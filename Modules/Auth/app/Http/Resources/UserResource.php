<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\app\Models\User;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        /* @var User|self $this */

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email->toNative(),
            'phone' => $this->phone->toNative(),
            'status' => [
                'name' => $this->status->label(),
                'value' => $this->status->value,
            ],
        ];

    }
}
