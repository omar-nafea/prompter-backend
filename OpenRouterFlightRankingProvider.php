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

final class OpenRouterFlightRankingProvider implements FlightRankingProvider
{
    private const TOP_N = 5;

    public function rank(FlightSearchCriteria $criteria, array $candidates): array
    {
        return $this->rankWithLimit($criteria, $candidates, min(self::TOP_N, count($candidates)))['ranked'];
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
    private function rankWithLimit(FlightSearchCriteria $criteria, array $candidates, int $limit, ?Closure $beforeAttempt = null): array
    {
        if ($candidates === [] || $limit === 0) {
            return ['ranked' => [], 'usage' => ['prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0], 'attempts' => 0];
        }

        if ($this->apiKey() === null || $this->publicKey() === null) {
            throw new FlightRankingUnavailable('Prompter flight ranking is not configured.');
        }

        [$response, $attempts] = $this->send(
            $this->requestPayload($criteria, $candidates, $limit),
            $beforeAttempt,
        );

        $body = $response->json();
        if ( ! is_array($body)) {
            throw new FlightRankingUnavailable('Prompter ranking returned an invalid response.');
        }

        $decoded = data_get($body, 'data');
        if ( ! is_array($decoded)) {
            throw new FlightRankingUnavailable('Prompter ranking returned no structured data.');
        }

        return [
            'ranked' => $this->validatedRanking($decoded, $candidates, $limit),
            'usage' => $this->usage($decoded),
            'attempts' => $attempts,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     * @return array<string, mixed>
     */
    private function requestPayload(FlightSearchCriteria $criteria, array $candidates, int $limit): array
    {
        $search = $criteria->toArray();
        $allowedIds = array_values(array_filter(array_map(
            static fn (array $candidate): ?string => is_string($candidate['id'] ?? null) ? $candidate['id'] : null,
            $candidates,
        )));
        $goal = 'Rank exactly ' . $limit . ' supplied flights using only the supplied facts and preferences.';

        $preferredAirlines = array_values(array_filter(
            is_array(data_get($search, 'soft_preferences.preferred_airlines')) ? data_get($search, 'soft_preferences.preferred_airlines') : [],
            'is_string',
        ));
        $preferredAirlines = array_map(static fn (string $airline): string => mb_strtoupper(trim($airline)), $preferredAirlines);
        $direction = (string) ($candidates[0]['_ranking_direction'] ?? 'outbound');
        $projection = array_map(
            fn (array $candidate): array => $this->rankingCandidate($candidate, $search, $preferredAirlines, $direction),
            $candidates,
        );
        $request = [
            'goal' => $goal,
            'limit' => $limit,
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
        if (mb_strlen(json_encode($fixedRequest, JSON_THROW_ON_ERROR)) > (int) config('flight_selection.projection.max_fixed_bytes', 8000)) {
            throw new FlightRankingProjectionException('The fixed ranking projection exceeds its configured size.');
        }
        $encodedRequest = json_encode($request, JSON_THROW_ON_ERROR);
        if (mb_strlen($encodedRequest) > (int) config('flight_selection.projection.max_request_bytes', 49152)) {
            throw new FlightRankingProjectionException('The ranking projection exceeds its configured request size.');
        }
        foreach ($projection as $candidate) {
            $this->assertProjectionStrings($candidate);
            if (mb_strlen(json_encode($candidate, JSON_THROW_ON_ERROR)) > (int) config('flight_selection.projection.max_candidate_bytes', 900)) {
                throw new FlightRankingProjectionException('A ranking candidate exceeds its configured projection size.');
            }
        }

        return [
            // Keep the Prompter test form usable while the backend also accepts native JSON inputs.
            'ranking_request' => $encodedRequest,
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
        if ( ! is_array($decoded) || ! isset($decoded['ranked']) || ! is_array($decoded['ranked'])) {
            throw new FlightRankingUnavailable('Prompter ranking schema mismatch.');
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

            if ( ! is_array($item)) {
                continue;
            }

            $id = $item['id'] ?? null;
            $reason = $item['reason'] ?? null;

            if ( ! is_string($id) || ! isset($allowed[$id]) || isset($seen[$id])) {
                continue;
            }

            if ( ! is_string($reason)) {
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
            throw new FlightRankingUnavailable('Prompter ranking did not return a complete valid order.');
        }

        return $ranked;
    }

    private function apiKey(): ?string
    {
        $apiKey = trim((string) config('services.prompter.flight_api_key'));

        return $apiKey === '' ? null : $apiKey;
    }

    private function publicKey(): ?string
    {
        $publicKey = trim((string) config('services.prompter.flight_public_key'));

        return $publicKey === '' ? null : $publicKey;
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.prompter.url'), '/');
    }

    private function timeout(): int
    {
        $timeout = (int) config('services.prompter.timeout', 60);

        return max(5, min($timeout, 120));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: Response, 1: int}
     */
    private function send(array $payload, ?Closure $beforeAttempt = null): array
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
                    ->withHeaders([
                        'X-Api-Key' => $this->apiKey(),
                        'X-Public-Key' => $this->publicKey(),
                    ])
                    ->connectTimeout(5)
                    ->timeout($this->timeout())
                    ->post($this->baseUrl() . '/api/call-ai-service', $payload);
            } catch (ConnectionException $exception) {
                if ($attempt >= 2) {
                    throw new FlightRankingUnavailable('Prompter ranking is temporarily unavailable.', 0, $exception);
                }
                usleep(250000);

                continue;
            }

            if ($response->successful() || ! in_array($response->status(), [429, 500, 502, 503, 504], true) || $attempt >= 2) {
                break;
            }

            usleep(250000);
        }

        if ( ! $response->successful()) {
            throw new FlightRankingUnavailable('Prompter ranking failed with HTTP ' . $response->status() . '.');
        }

        return [$response, $attempts];
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{prompt_tokens: int, completion_tokens: int, total_tokens: int}
     */
    private function usage(array $body): array
    {
        $usage = is_array(data_get($body, '_meta.usage')) ? data_get($body, '_meta.usage') : [];

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
        if ( ! is_string($airport['time_zone'] ?? null) || ! is_string($airport['time'] ?? null)) {
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
        if ( ! is_string($airport['time_zone'] ?? null) || ! is_string($airport['time'] ?? null)) {
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
        if ( ! is_string($time) || preg_match('/(?:\d{4}-\d{2}-\d{2}\s+)?(\d{2}):(\d{2})/', $time, $matches) !== 1) {
            return null;
        }

        return ((int) $matches[1] * 60) + (int) $matches[2];
    }

    /** @param array<string, mixed> $value */
    private function assertProjectionStrings(array $value, string $path = ''): void
    {
        foreach ($value as $key => $item) {
            $itemPath = $path === '' ? (string) $key : $path . '.' . $key;
            if (is_string($item)) {
                $limit = $key === 'id' ? 96 : 64;
                if (mb_strlen($item) > $limit) {
                    throw new FlightRankingProjectionException('A ranking projection string exceeds its configured size.');
                }
            } elseif (is_array($item)) {
                $this->assertProjectionStrings($item, $itemPath);
            }
        }
    }
}
