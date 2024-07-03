<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\NewAccessToken;
use Override;

/**
 * @property-read  NewAccessToken $resource
 */
final class TokenResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource->plainTextToken,
            'expires_at' => $this->resource->accessToken->expires_at->timestamp, //@phpstan-ignore-line
        ];
    }
}
