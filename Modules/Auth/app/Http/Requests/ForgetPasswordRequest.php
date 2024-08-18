<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use App\x;
use Illuminate\Support\Facades\Gate;

final class ForgetPasswordRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        //        Gate::authorize('',[x::class,$this->route('')]);
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,strict',
                'max:' . config('global.max_string_length'),
            ],
        ];
    }
}
