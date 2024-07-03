<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property-read Carbon $resource
 * */
final class DateTimeResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'timestamp' => $this->resource->timestamp,
            'datetime' => $this->resource->toDateTimeString(),
            'diff_for_humans' => $this->resource->diffForHumans(),
        ];
    }
}
