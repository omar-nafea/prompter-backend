<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Gateway\AiProviderResolver;
use Modules\AiServiceManagement\app\Http\Requests\AiModelRequest;
use Modules\AiServiceManagement\app\Http\Resources\AiModelResource;
use Modules\AiServiceManagement\app\Models\AiModel;

final class AiModelController
{
    public function show(): JsonResponse
    {
        $model = AiModel::query()->first();

        return apiResponse()->success()->data([
            'model' => $model ? AiModelResource::make($model) : null,
            'providers' => AiModelProvider::selectOptions(),
        ])->send();
    }

    /**
     * @throws ValidationException
     */
    public function update(AiModelRequest $request, AiProviderResolver $resolver): JsonResponse
    {
        $model = AiModel::query()->first();
        $attributes = $request->validated();
        $apiKey = $attributes['api_key'] ?? $model?->api_key;
        if ($request->integer('provider') !== AiModelProvider::OpenAiCompatible->value) {
            $attributes['connector_url'] = null;
        }
        $candidate = new AiModel([...$attributes, 'api_key' => $apiKey]);
        $connection = $resolver->for($candidate->provider)->test($candidate);

        if ( ! $connection['success']) {
            throw ValidationException::withMessages(['connection' => $connection['message']]);
        }

        if ( ! filled($attributes['api_key'] ?? null)) {
            unset($attributes['api_key']);
        }

        $model ??= new AiModel();
        $model->fill($attributes);
        $model->save();

        return apiResponse()
            ->success()
            ->message('AI model connected and saved successfully')
            ->data(AiModelResource::make($model))
            ->send();
    }
}
