<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\ProjectModerator;

use App\Http\Requests\BaseApiRequest;
use App\x;
use Illuminate\Support\Facades\Gate;

final class DestroyProjectModeratorRequest extends BaseApiRequest
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
