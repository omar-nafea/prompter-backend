<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Modules\AiServiceManagement\app\Actions\AiService\AskAiServiceAction;
use Modules\AiServiceManagement\app\Dtos\AskAiServiceDto;
use Modules\AiServiceManagement\app\Http\Requests\AskAiServiceRequest;

final class AiServiceCallingController
{
    /**
     * @throws BindingResolutionException
     */
    public function ask(AskAiServiceRequest $request, AskAiServiceAction $action): JsonResponse
    {
        return apiResponse()
            ->success()
            ->data(
                data: $action->execute(
                    dto: AskAiServiceDto::fromAskAiServiceRequest($request)
                )->data()
            )->send();
    }
}
