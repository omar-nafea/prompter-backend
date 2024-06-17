<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DateTimeResource extends JsonResource
{
    public function toArray(Request $request)
    {
        /** @var Carbon $this */
        return [
            'timestamp' => $this->timestamp,
            'datetime' => $this->toDateTimeString(),
            'diff_for_humans' => $this->diffForHumans(),
        ];
    }
}
