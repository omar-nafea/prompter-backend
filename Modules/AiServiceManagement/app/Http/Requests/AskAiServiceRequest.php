<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use Modules\AiServiceManagement\app\Exceptions\ProjectException;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\AiServiceInputsValidationFactory;
use Modules\ProjectManagement\app\Models\Project;

class AskAiServiceRequest extends BaseApiRequest
{
    public ?Project $project;

    protected function prepareForValidation(): void
    {
        $apiKey = request()->header('X-Api-Key');
        $publicKey = request()->header('X-Public-Key');
        if (! $publicKey || ! $apiKey) {
            throw ProjectException::invalidPublicOrApiKey();
        }
        $project = Project::where('key', $publicKey)->firstOrFail();
        if ($apiKey !== $project->api_key) {
            throw ProjectException::invalidPublicOrApiKey();
        }
        $this->project = $project;
    }

    public function rules(): array
    {
        return AiServiceInputsValidationFactory::make($this->project)->rules();
    }
}
