<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Contracts;

use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;

interface AiProviderConnector
{
    /**
     * Run a completion against the provider for the given project prompt.
     */
    public function complete(AiModel $model, string $prompt): AskResponseDto;

    /**
     * Run a tiny completion to verify the model credentials/configuration.
     *
     * @return array{success: bool, message: string, response?: string}
     */
    public function test(AiModel $model): array;
}
