<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiResponseType;

use Illuminate\Support\Collection;
use Modules\AiServiceManagement\app\Models\AiResponseType;

final class FetchAiResponseTypeListAction
{
    /** @return Collection<int, AiResponseType> */
    public function execute(): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, AiResponseType> */
        return AiResponseType::query()->get();
    }
}
