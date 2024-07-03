<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiCallType;

use Illuminate\Support\Collection;
use Modules\AiServiceManagement\app\Models\AiCallType;

final class FetchAiCallTypeListAction
{
    /** @return Collection<int, AiCallType> */
    public function execute(): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, AiCallType> */
        return AiCallType::query()->get();
    }
}
