<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\ProjectModerator;

use App\Http\Requests\BaseApiRequest;

final class CheckProjectModeratorExistenceRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:strict,dns',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
            ],
        ];
    }
}
