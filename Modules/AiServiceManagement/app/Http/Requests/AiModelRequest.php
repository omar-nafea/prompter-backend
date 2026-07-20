<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Validation\Rule;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Models\AiModel;

final class AiModelRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alias' => ['required', 'string', 'max:255'],
            'provider' => ['required', Rule::enum(AiModelProvider::class)],
            'api_key' => [Rule::requiredIf(AiModel::query()->doesntExist()), 'nullable', 'string', 'max:1000'],
            'connector_url' => [
                Rule::requiredIf(fn (): bool => $this->integer('provider') === AiModelProvider::OpenAiCompatible->value),
                'nullable',
                'string',
                'url:https',
                'max:2048',
            ],
        ];
    }
}
