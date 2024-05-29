<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\RateLimiter;
use Modules\AiServiceManagement\app\Exceptions\ProjectException;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\AiServiceInputsValidationFactory;
use Modules\ProjectManagement\app\Models\Project;
use Str;
use Symfony\Component\HttpFoundation\Response;

class AskAiServiceRequest extends BaseApiRequest
{
    public ?Project $project;

    public function authorize(): bool
    {
        $this->ensureIsNotRateLimited();

        return true;
    }

    protected function prepareForValidation(): void
    {
        $apiKey = request()->header('X-Api-Key');
        $publicKey = request()->header('X-Public-Key');
        if (! $publicKey || ! $apiKey) {
            throw ProjectException::invalidPublicOrApiKey();
        }
        $project = Project::where('key', $publicKey)->with('answers')->firstOrFail();
        if ($apiKey !== $project->api_key) {
            throw ProjectException::invalidPublicOrApiKey();
        }
        $this->project = $project;
    }

    public function rules(): array
    {
        return AiServiceInputsValidationFactory::make($this->project)->rules();
    }

    protected function ensureIsNotRateLimited(): void
    {
        $rateLimiterKey = 'ai-call:' . request()->header('X-Public-Key');
        if (RateLimiter::tooManyAttempts($rateLimiterKey, config('ai-service-management.throttle.max_attempts'))) {
            apiResponse()
                ->error()
                ->statusCode(Response::HTTP_TOO_MANY_REQUESTS)
                ->message(sprintf(
                    'Too Many Requests. Please try again in (%s) %s.',
                    $minutes = ceil(RateLimiter::availableIn($rateLimiterKey) / 60),
                    Str::plural('minute', $minutes)
                ))
                ->send();
        }
        RateLimiter::increment($rateLimiterKey, config('ai-service-management.throttle.seconds'));
    }
}
