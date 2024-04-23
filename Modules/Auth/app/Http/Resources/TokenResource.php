<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\NewAccessToken;

class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /* @var NewAccessToken|self $this */
        return [
            'token' => $this->plainTextToken,
            'expires_at' => $this->accessToken->expires_at->timestamp,
        ];
    }
}
