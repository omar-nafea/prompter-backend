<?php

declare(strict_types=1);

namespace App\Modules\Requests\Infrastructure\FlightSearch;

use App\Modules\Requests\Domain\Contracts\FlightRankingProvider;
use App\Modules\Requests\Domain\Exceptions\FlightRankingProjectionException;
use App\Modules\Requests\Domain\Exceptions\FlightRankingUnavailable;
use App\Modules\Requests\Domain\ValueObjects\FlightSearchCriteria;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JsonException;

final class OpenRouterFlightRankingProvider implements FlightRankingProvider
{
    private const DEFAULT_BASE_URL = 'https://openrouter.ai/api/v1';

    private const DEFAULT_MODEL = 'google/gemini-3.5-flash-lite';

    private const TOP_N = 5;

    public function rank(FlightSearchCriteria $criteria, array $candidates): array
    {
        return $this->rankWithLimit($criteria, $candidates, min(self::TOP_N, count($candidates)), null, true)['ranked'];
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     * @return array{ranked: list<array{id: string, reason: string}>, usage: array{prompt_tokens: int, completion_tokens: int, total_tokens: int}, attempts: int}
     */
    public function rankChunk(FlightSearchCriteria $criteria, array $candidates, ?Closure $beforeAttempt = null): array
    {
        return $this->rankWithLimit($criteria, $candidates, min(3, count($candidates)), $beforeAttempt);
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     * @return array{ranked: list<array{id: string, reason: string}>, usage: array{prompt_tokens: int, completion_tokens: int, total_tokens: int}, attempts: int}
     */
    private function rankWithLimit(FlightSearchCriteria $criteria, array $candidates, int $limit, ?Closure $beforeAttempt = null, bool $legacy = false): array
    {
        if ($candidates === [] || $limit === 0) {
            return ['ranked' => [], 'usage' => ['prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0], 'attempts' => 0];
        }

        $apiKey = $this->apiKey();
        if ($apiKey === null) {
            throw new FlightRankingUnavailable('OpenRouter ranking is not configured.');
        }

        $payload = $this->requestPayload($criteria, $candidates, $limit, $legacy);
        [$response, $attempts] = $this->send($apiKey, $payload, $beforeAttempt);

        $body = $response->json();
        if (! is_array($body)) {
            throw new FlightRankingUnavailable('OpenRouter ranking returned an invalid response.');
        }

        $content = data_get($body, 'choices.0.message.content');
        if (! is_string($content) || $content === '') {
            throw new FlightRankingUnavailable('OpenRouter ranking returned an empty response.');
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new FlightRankingUnavailable('OpenRouter ranking returned invalid JSON.', 0, $exception);
        }

        return [
            'ranked' => $this->validatedRanking($decoded, $candidates, $limit),
            'usage' => $this->usage($body),
            'attempts' => $attempts,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     * @return array<string, mixed>
     */
    private function requestPayload(FlightSearchCriteria $criteria, array $candidates, int $limit, bool $legacy = false): array
    {
        $search = $criteria->toArray();
        $allowedIds = array_values(array_filter(array_map(
            static fn (array $candidate): ?string => is_string($candidate['id'] ?? null) ? $candidate['id'] : null,
            $candidates,
        )));
        $goal = 'Rank exactly '.$limit.' supplied flights using only the supplied facts and preferences.';

        if ($legacy) {
            return $this->legacyRequestPayload($search, $candidates, $limit, $allowedIds);
        }

        $preferredAirlines = array_values(array_filter(
            is_array(data_get($search, 'soft_preferences.preferred_airlines')) ? data_get($search, 'soft_preferences.preferred_airlines') : [],
            'is_string',
        ));
        $preferredAirlines = array_map(static fn (string $airline): string => strtoupper(trim($airline)), $preferredAirlines);
        $direction = (string) ($candidates[0]['_ranking_direction'] ?? 'outbound');
        $projection = array_map(
            fn (array $candidate): array => $this->rankingCandidate($candidate, $search, $preferredAirlines, $direction),
            $candidates,
        );
        $request = [
            'goal' => $goal,
            'direction' => $direction,
            'requested_cabin_class' => $search['ticket_type'],
            'soft_preferences' => $search['soft_preferences'],
            'policy_version' => (string) config('flight_selection.policy_version'),
            'prompt_version' => (string) config('flight_selection.prompt_version'),
            'schema_version' => (int) config('flight_selection.schema_version'),
            'candidates' => $projection,
            'allowed_ids' => $allowedIds,
        ];
        $fixedRequest = $request;
        unset($fixedRequest['candidates']);
        if (strlen(json_encode($fixedRequest, JSON_THROW_ON_ERROR)) > (int) config('flight_selection.projection.max_fixed_bytes', 8000)) {
            throw new FlightRankingProjectionException('The fixed ranking projection exceeds its configured size.');
        }
        $encodedRequest = json_encode($request, JSON_THROW_ON_ERROR);
        if (strlen($encodedRequest) > (int) config('flight_selection.projection.max_request_bytes', 49152)) {
            throw new FlightRankingProjectionException('The ranking projection exceeds its configured request size.');
        }
        foreach ($projection as $candidate) {
            $this->assertProjectionStrings($candidate);
            if (strlen(json_encode($candidate, JSON_THROW_ON_ERROR)) > (int) config('flight_selection.projection.max_candidate_bytes', 900)) {
                throw new FlightRankingProjectionException('A ranking candidate exceeds its configured projection size.');
            }
        }

        $payload = [
            'model' => $this->model(),
            'temperature' => 0,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Actively sort the already route-validated SerpApi flight candidates and return only the top '.$limit.' ids with a short reason each. Use only provided facts. Never invent prices, times, airports, airlines, or links. Do not analyze or return the rest of the list.',
                ],
                [
                    'role' => 'user',
                    'content' => $encodedRequest,
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'flight_ranking',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['ranked'],
                        'properties' => [
                            'ranked' => [
                                'type' => 'array',
                                'minItems' => $limit,
                                'maxItems' => $limit,
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'required' => ['id', 'reason'],
                                    'properties' => [
                                        'id' => ['type' => 'string'],
                                        'reason' => ['type' => 'string', 'maxLength' => (int) config('flight_selection.projection.max_reason_bytes', 180)],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'max_completion_tokens' => (int) config('flight_selection.projection.max_completion_tokens', 800),
            'provider' => [
                'require_parameters' => true,
            ],
        ];

        if (strlen(json_encode($payload, JSON_THROW_ON_ERROR)) > (int) config('flight_selection.projection.max_request_bytes', 49152)) {
            throw new FlightRankingProjectionException('The complete ranking request exceeds its configured request size.');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $search
     * @param  list<array<string, mixed>>  $candidates
     * @param  list<string>  $allowedIds
     * @return array<string, mixed>
     */
    private function legacyRequestPayload(array $search, array $candidates, int $limit, array $allowedIds): array
    {
        $goal = 'Rank exactly '.$limit.' preferred flights by the supplied ranking policy and saved request preferences. Do not consider carbon emissions.';
        $content = [
            'goal' => $goal,
            'mode' => 'preferred',
            'limit' => $limit,
            'preferences' => [
                'trip_type' => $search['trip_type'],
                'ticket_type' => $search['ticket_type'],
                'baggage_type' => $search['baggage_type'],
                'seat_preference' => $search['seat_preference'],
                'passengers' => $search['passengers'],
                'preferred_airline' => $search['preferred_airline'],
                'segments' => array_map(static fn (array $segment): array => [
                    'departure_airport' => $segment['departure_airport'],
                    'arrival_airport' => $segment['arrival_airport'],
                    'departure_date' => $segment['departure_date'],
                    'departure_location' => $segment['departure_location'],
                    'arrival_location' => $segment['arrival_location'],
                ], $search['segments']),
            ],
            'ranking_policy' => [
                'priority_order' => [
                    'Match every requested airport or selected city and departure date.',
                    'Place the preferred airline first only when its price is no more than the configured EGP maximum premium above the cheapest matching candidate.',
                    'Otherwise place a cheapest candidate first, then prefer fewer stops and shorter total duration.',
                    'Match ticket, baggage, and seat preferences when the candidate facts provide that information.',
                ],
                'preferred_airline' => $search['preferred_airline'],
                'preferred_airline_max_premium_egp' => $search['preferred_airline_max_premium_egp'],
            ],
            'candidates' => array_map($this->legacyRankingCandidate(...), $candidates),
            'allowed_ids' => $allowedIds,
        ];

        return [
            'model' => $this->model(),
            'temperature' => 0,
            'messages' => [
                ['role' => 'system', 'content' => 'Actively sort the already route-validated SerpApi flight candidates and return only the top '.$limit.' ids with a short reason each. Use only provided facts. Never invent prices, times, airports, airlines, or links. Do not analyze or return the rest of the list.'],
                ['role' => 'user', 'content' => json_encode($content, JSON_THROW_ON_ERROR)],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'flight_ranking',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['ranked'],
                        'properties' => ['ranked' => [
                            'type' => 'array',
                            'minItems' => $limit,
                            'maxItems' => $limit,
                            'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['id', 'reason'], 'properties' => [
                                'id' => ['type' => 'string'],
                                'reason' => ['type' => 'string'],
                            ]],
                        ]],
                    ],
                ],
            ],
            'provider' => ['require_parameters' => true],
        ];
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @return array<string, mixed>
     */
    private function legacyRankingCandidate(array $candidate): array
    {
        return [
            'id' => $candidate['id'] ?? '',
            'price' => $candidate['price'] ?? null,
            'total_duration' => $candidate['total_duration'] ?? null,
            'stops' => $candidate['stops'] ?? null,
            'type' => $candidate['type'] ?? '',
            'airlines' => $candidate['airlines'] ?? [],
            'departure' => $candidate['departure'] ?? [],
            'arrival' => $candidate['arrival'] ?? [],
            'extensions' => $candidate['extensions'] ?? [],
            'source_lists' => $candidate['source_lists'] ?? [],
            'source_ranks' => $candidate['source_ranks'] ?? [],
            'flights' => array_map(static fn (array $leg): array => [
                'airline' => $leg['airline'] ?? '',
                'flight_number' => $leg['flight_number'] ?? '',
                'travel_class' => $leg['travel_class'] ?? '',
                'airplane' => $leg['airplane'] ?? '',
                'duration' => $leg['duration'] ?? null,
                'departure' => $leg['departure'] ?? [],
                'arrival' => $leg['arrival'] ?? [],
            ], is_array($candidate['flights'] ?? null) ? $candidate['flights'] : []),
        ];
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @param  array<string, mixed>  $search
     * @param  list<string>  $preferredAirlines
     * @return array<string, mixed>
     */
    private function rankingCandidate(array $candidate, array $search, array $preferredAirlines, string $direction): array
    {
        $legs = is_array($candidate['flights'] ?? null) ? array_values(array_filter($candidate['flights'], 'is_array')) : [];
        $airlines = is_array($candidate['airlines'] ?? null) ? array_values(array_slice($candidate['airlines'], 0, 8)) : [];
        $operatingAirlines = array_slice(array_unique(array_filter(array_map(
            static fn (array $leg): ?string => is_string($leg['operating_carrier'] ?? null) ? $leg['operating_carrier'] : null,
            $legs,
        ))), 0, 8);
        $preferences = is_array($search['soft_preferences'] ?? null) ? $search['soft_preferences'] : [];
        $preferredAirports = is_array($preferences['preferred_airports'] ?? null) ? array_map('strtoupper', $preferences['preferred_airports']) : [];
        $departure = is_array($candidate['departure'] ?? null) ? $candidate['departure'] : [];
        $arrival = is_array($candidate['arrival'] ?? null) ? $candidate['arrival'] : [];
        $departureWindow = $direction === 'return'
            ? $this->windowDistance($departure, $preferences['preferred_departure_window_start'] ?? null, $preferences['preferred_departure_window_end'] ?? null)
            : null;
        $arrivalWindow = $this->windowDistance($arrival, $preferences['preferred_arrival_window_start'] ?? null, $preferences['preferred_arrival_window_end'] ?? null);

        return [
            // _ranking_direction is an internal host hint and never enters the projection.
            'id' => $candidate['id'] ?? '',
            'origin_iata' => $departure['airport'] ?? null,
            'destination_iata' => $arrival['airport'] ?? null,
            'departure_local' => $departure['time'] ?? null,
            'arrival_local' => $arrival['time'] ?? null,
            'price_amount' => $candidate['price'] ?? null,
            'currency' => $candidate['currency'] ?? 'EGP',
            'price_semantics' => $candidate['price_semantics'] ?? 'indicative_round_trip_from',
            'total_duration_minutes' => $candidate['total_duration'] ?? null,
            'stop_count' => $candidate['stops'] ?? null,
            'connection_iatas' => array_slice(array_filter(array_map(
                static fn (array $leg): ?string => data_get($leg, 'arrival.airport'),
                array_slice($legs, 0, -1),
            )), 0, 8),
            'marketing_airlines' => $airlines,
            'operating_airlines' => $operatingAirlines,
            'observed_cabin_classes' => array_slice(array_unique(array_filter(array_map(static fn (array $leg): string => (string) ($leg['travel_class'] ?? ''), $legs))), 0, 4),
            'mixed_cabin' => count(array_unique(array_filter(array_map(static fn (array $leg): string => (string) ($leg['travel_class'] ?? ''), $legs)))) > 1,
            'minimum_legroom_inches' => $this->minimumLegroom($legs),
            'overnight' => $this->nullableBool($candidate, 'overnight'),
            'often_delayed_by_over_30_min' => $this->nullableBool($candidate, 'often_delayed_by_over_30_min'),
            'airport_change' => $this->airportChange($legs),
            'preferred_airline_match' => $preferredAirlines === [] ? null : array_intersect(array_map('strtoupper', $airlines), $preferredAirlines) !== [],
            'preferred_airport_match' => $preferredAirports === [] ? null : in_array($departure['airport'] ?? null, $preferredAirports, true) || in_array($arrival['airport'] ?? null, $preferredAirports, true),
            'stops_over_preference' => is_int($preferences['maximum_preferred_stops'] ?? null) && is_numeric($candidate['stops'] ?? null)
                ? (int) $candidate['stops'] > (int) $preferences['maximum_preferred_stops']
                : null,
            'departure_window_distance_minutes' => $departureWindow,
            'arrival_window_distance_minutes' => $arrivalWindow,
            'late_night_departure' => $this->dayPeriod($departure, 22, 5),
            'early_morning_arrival' => $this->dayPeriod($arrival, 0, 8),
            'warning_codes' => is_array($candidate['warning_codes'] ?? null)
                ? array_values(array_slice($candidate['warning_codes'], 0, 12))
                : [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     * @return list<array{id: string, reason: string}>
     */
    private function validatedRanking(mixed $decoded, array $candidates, int $limit): array
    {
        if (! is_array($decoded) || ! isset($decoded['ranked']) || ! is_array($decoded['ranked'])) {
            throw new FlightRankingUnavailable('OpenRouter ranking schema mismatch.');
        }

        $allowed = [];
        foreach ($candidates as $candidate) {
            if (is_string($candidate['id'] ?? null)) {
                $allowed[$candidate['id']] = true;
            }
        }

        $ranked = [];
        $seen = [];

        foreach ($decoded['ranked'] as $item) {
            if (count($ranked) >= $limit) {
                break;
            }

            if (! is_array($item)) {
                continue;
            }

            $id = $item['id'] ?? null;
            $reason = $item['reason'] ?? null;

            if (! is_string($id) || ! isset($allowed[$id]) || isset($seen[$id])) {
                continue;
            }

            if (! is_string($reason)) {
                continue;
            }

            $reason = trim(mb_strcut($reason, 0, (int) config('flight_selection.projection.max_reason_bytes', 180), 'UTF-8'));
            if ($reason === '') {
                continue;
            }

            $ranked[] = ['id' => $id, 'reason' => $reason];
            $seen[$id] = true;
        }

        $expectedCount = min($limit, count($allowed));
        if (count($ranked) !== $expectedCount) {
            throw new FlightRankingUnavailable('OpenRouter ranking did not return a complete valid order.');
        }

        return $ranked;
    }

    private function apiKey(): ?string
    {
        $apiKey = trim((string) config('services.openrouter.api_key'));

        return $apiKey === '' ? null : $apiKey;
    }

    private function baseUrl(): string
    {
        $baseUrl = trim((string) config('services.openrouter.base_url', self::DEFAULT_BASE_URL));

        return rtrim($baseUrl !== '' ? $baseUrl : self::DEFAULT_BASE_URL, '/');
    }

    private function model(): string
    {
        $model = trim((string) config('services.openrouter.model', self::DEFAULT_MODEL));

        return $model !== '' ? $model : self::DEFAULT_MODEL;
    }

    private function timeout(): int
    {
        $timeout = (int) config('services.openrouter.timeout', 20);

        return max(5, min($timeout, 60));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: Response, 1: int}
     */
    private function send(string $apiKey, array $payload, ?Closure $beforeAttempt = null): array
    {
        $attempts = 0;
        /** @var Response|null $response */
        $response = null;
        $attempt = 0;
        while (true) {
            $attempts++;
            $attempt++;
            $beforeAttempt?->__invoke();
            try {
                $response = Http::acceptJson()
                    ->withToken($apiKey)
                    ->connectTimeout(5)
                    ->timeout($this->timeout())
                    ->post($this->baseUrl().'/chat/completions', $payload);
            } catch (ConnectionException $exception) {
                if ($attempt >= 2) {
                    throw new FlightRankingUnavailable('OpenRouter ranking is temporarily unavailable.', 0, $exception);
                }
                usleep(250000);

                continue;
            }

            if ($response->successful() || ! in_array($response->status(), [429, 500, 502, 503, 504], true) || $attempt >= 2) {
                break;
            }

            usleep(250000);
        }

        if (! $response->successful()) {
            throw new FlightRankingUnavailable('OpenRouter ranking failed with HTTP '.$response->status().'.');
        }

        return [$response, $attempts];
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{prompt_tokens: int, completion_tokens: int, total_tokens: int}
     */
    private function usage(array $body): array
    {
        $usage = is_array($body['usage'] ?? null) ? $body['usage'] : [];

        return [
            'prompt_tokens' => max(0, (int) ($usage['prompt_tokens'] ?? 0)),
            'completion_tokens' => max(0, (int) ($usage['completion_tokens'] ?? 0)),
            'total_tokens' => max(0, (int) ($usage['total_tokens'] ?? 0)),
        ];
    }

    /** @param list<array<string, mixed>> $legs */
    private function minimumLegroom(array $legs): ?int
    {
        $values = [];
        foreach ($legs as $leg) {
            if (preg_match('/(\d{2})\s*in/i', (string) ($leg['legroom'] ?? ''), $matches) === 1) {
                $values[] = (int) $matches[1];
            }
        }

        return $values === [] ? null : min($values);
    }

    /** @param array<string, mixed> $candidate */
    private function nullableBool(array $candidate, string $key): ?bool
    {
        return array_key_exists($key, $candidate) && is_bool($candidate[$key]) ? $candidate[$key] : null;
    }

    /** @param list<array<string, mixed>> $legs */
    private function airportChange(array $legs): bool
    {
        for ($index = 0; $index < count($legs) - 1; $index++) {
            if (data_get($legs[$index], 'arrival.airport') !== data_get($legs[$index + 1], 'departure.airport')) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $airport */
    private function windowDistance(array $airport, mixed $start, mixed $end): ?int
    {
        if (! is_string($airport['time_zone'] ?? null) || ! is_string($airport['time'] ?? null)) {
            return null;
        }

        $time = $this->minutes($airport['time']);
        $windowStart = $this->minutes($start);
        $windowEnd = $this->minutes($end);
        if ($time === null || $windowStart === null || $windowEnd === null) {
            return null;
        }

        if (($windowStart <= $windowEnd && $time >= $windowStart && $time <= $windowEnd)
            || ($windowStart > $windowEnd && ($time >= $windowStart || $time <= $windowEnd))) {
            return 0;
        }

        return min(abs($time - $windowStart), abs($time - $windowEnd));
    }

    /** @param array<string, mixed> $airport */
    private function dayPeriod(array $airport, int $startHour, int $endHour): ?bool
    {
        if (! is_string($airport['time_zone'] ?? null) || ! is_string($airport['time'] ?? null)) {
            return null;
        }

        $minutes = $this->minutes($airport['time']);
        if ($minutes === null) {
            return null;
        }

        $start = $startHour * 60;
        $end = $endHour * 60;

        return $startHour > $endHour
            ? $minutes >= $start || $minutes < $end
            : $minutes >= $start && $minutes < $end;
    }

    private function minutes(mixed $time): ?int
    {
        if (! is_string($time) || preg_match('/(?:\d{4}-\d{2}-\d{2}\s+)?(\d{2}):(\d{2})/', $time, $matches) !== 1) {
            return null;
        }

        return ((int) $matches[1] * 60) + (int) $matches[2];
    }

    /** @param array<string, mixed> $value */
    private function assertProjectionStrings(array $value, string $path = ''): void
    {
        foreach ($value as $key => $item) {
            $itemPath = $path === '' ? (string) $key : $path.'.'.$key;
            if (is_string($item)) {
                $limit = $key === 'id' ? 96 : 64;
                if (strlen($item) > $limit) {
                    throw new FlightRankingProjectionException('A ranking projection string exceeds its configured size.');
                }
            } elseif (is_array($item)) {
                $this->assertProjectionStrings($item, $itemPath);
            }
        }
    }
}
