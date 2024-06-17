<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Illuminate\Database\Eloquent\Collection;
use Modules\AiServiceManagement\app\Models\AiService;

final class FetchAiServiceListAction
{
    /**
     * @return Collection<int,AiService>
     */
    public function execute(): Collection
    {
        return AiService::all();
    }
}
