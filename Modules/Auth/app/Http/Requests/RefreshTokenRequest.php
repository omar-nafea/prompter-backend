<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use App\x;
use Illuminate\Support\Facades\Gate;

final class RefreshTokenRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        //        Gate::authorize('',[x::class,$this->route('')]);
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
