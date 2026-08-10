<?php

declare(strict_types=1);

namespace App\Modules\Requests\Infrastructure\HotelOffers;

use App\Modules\Requests\Domain\Contracts\HotelOfferRankingProvider;
use App\Modules\Requests\Domain\Exceptions\HotelOfferRankingUnavailable;
use App\Modules\Requests\Domain\ValueObjects\HotelOfferCriteria;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JsonException;
use Throwable;

final class OpenRouterHotelOfferRankingProvider implements HotelOfferRankingProvider
{
    private const DEFAULT_BASE_URL = 'https://openrouter.ai/api/v1';

    private const DEFAULT_MODEL = 'google/gemini-3.5-flash-lite';

    private const TOP_N = 5;

    public function rank(HotelOfferCriteria $criteria, array $offers): array
    {
        if ($offers === []) {
            return [];
        }

        $apiKey = $this->apiKey();
        if ($apiKey === null) {
            throw new HotelOfferRankingUnavailable('OpenRouter ranking is not configured.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($apiKey)
                ->connectTimeout(5)
                ->timeout($this->timeout())
                ->retry(
                    2,
                    250,
                    static fn (Throwable $exception): bool => $exception instanceof ConnectionException
                        || ($exception instanceof RequestException
                            && ($exception->response->status() === 429 || $exception->response->serverError())),
                    throw: false,
                )
                ->post($this->baseUrl().'/chat/completions', $this->requestPayload($criteria, $offers));
        } catch (ConnectionException $exception) {
            throw new HotelOfferRankingUnavailable('OpenRouter ranking is temporarily unavailable.', 0, $exception);
        }

        if (! $response->successful()) {
            throw new HotelOfferRankingUnavailable('OpenRouter ranking failed with HTTP '.$response->status().'.');
        }

        $body = $response->json();
        if (! is_array($body)) {
            throw new HotelOfferRankingUnavailable('OpenRouter ranking returned an invalid response.');
        }

        $content = data_get($body, 'choices.0.message.content');
        if (! is_string($content) || $content === '') {
            throw new HotelOfferRankingUnavailable('OpenRouter ranking returned an empty response.');
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new HotelOfferRankingUnavailable('OpenRouter ranking returned invalid JSON.', 0, $exception);
        }

        return $this->validatedRanking($decoded, $offers);
    }

    /**
     * @param  list<array<string, mixed>>  $offers
     * @return array<string, mixed>
     */
    private function requestPayload(HotelOfferCriteria $criteria, array $offers): array
    {
        $stay = $criteria->toArray();
        $allowedIds = array_values(array_filter(array_map(
            static fn (array $offer): ?string => is_string($offer['id'] ?? null) ? $offer['id'] : null,
            $offers,
        )));
        $limit = min(self::TOP_N, count($allowedIds));

        return [
            'model' => $this->model(),
            'temperature' => 0,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Sort the supplied booking offers for one already-chosen hotel and return only the top '
                        .$limit.' ids with a short reason each. All offers are for the same hotel and the same stay dates, '
                        .'so compare on total price and booking conditions only. Use only provided facts. Never invent '
                        .'prices, vendors, or links. Do not analyze or return the rest of the list.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'goal' => 'Rank exactly '.$limit.' preferred booking offers by the supplied ranking policy and saved request preferences.',
                        'limit' => $limit,
                        'stay' => [
                            'hotel_name' => $stay['hotel_name'],
                            'city' => $stay['city'],
                            'country' => $stay['country'],
                            'check_in' => $stay['check_in'],
                            'check_out' => $stay['check_out'],
                            'nights' => $stay['nights'],
                            'rooms' => $stay['rooms'],
                            'adults' => $stay['adults'],
                            'room_type' => $stay['room_type'],
                            'meal_plan' => $stay['meal_plan'],
                        ],
                        'ranking_policy' => [
                            'currency' => 'EGP',
                            'priority_order' => [
                                'Place Booking.com first and Expedia.com second when those sources are available.',
                                'For the remaining positions, prefer the lowest total_rate.',
                                'Prefer free cancellation when the price difference is reasonable.',
                                'Then prefer offers whose room names match the requested room type and meal plan.',
                                'Then prefer official hotel sites over resellers when totals are equal.',
                            ],
                        ],
                        'offers' => array_map($this->rankingOffer(...), $offers),
                        'allowed_ids' => $allowedIds,
                    ], JSON_THROW_ON_ERROR),
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'hotel_offer_ranking',
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
                                        'reason' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'provider' => [
                'require_parameters' => true,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $offer
     * @return array<string, mixed>
     */
    private function rankingOffer(array $offer): array
    {
        return [
            'id' => $offer['id'] ?? '',
            'source' => $offer['source'] ?? '',
            'official' => $offer['official'] ?? false,
            'rate_per_night' => $offer['rate_per_night'] ?? null,
            'total_rate' => $offer['total_rate'] ?? null,
            'num_guests' => $offer['num_guests'] ?? null,
            'free_cancellation' => $offer['free_cancellation'] ?? false,
            'rooms' => $offer['rooms'] ?? [],
            'remarks' => $offer['remarks'] ?? [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $offers
     * @return list<array{id: string, reason: string}>
     */
    private function validatedRanking(mixed $decoded, array $offers): array
    {
        if (! is_array($decoded) || ! isset($decoded['ranked']) || ! is_array($decoded['ranked'])) {
            throw new HotelOfferRankingUnavailable('OpenRouter ranking schema mismatch.');
        }

        $allowed = [];
        foreach ($offers as $offer) {
            if (is_string($offer['id'] ?? null)) {
                $allowed[$offer['id']] = true;
            }
        }

        $ranked = [];
        $seen = [];

        foreach ($decoded['ranked'] as $item) {
            if (count($ranked) >= self::TOP_N) {
                break;
            }

            if (! is_array($item)) {
                continue;
            }

            $id = $item['id'] ?? null;
            $reason = $item['reason'] ?? null;

            if (! is_string($id) || ! isset($allowed[$id]) || isset($seen[$id]) || ! is_string($reason)) {
                continue;
            }

            $reason = trim(mb_substr($reason, 0, 180));
            if ($reason === '') {
                continue;
            }

            $ranked[] = ['id' => $id, 'reason' => $reason];
            $seen[$id] = true;
        }

        $expectedCount = min(self::TOP_N, count($allowed));
        if (count($ranked) !== $expectedCount) {
            throw new HotelOfferRankingUnavailable('OpenRouter ranking did not return a complete valid order.');
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
}
