<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Modules\AiServiceManagement\app\Exceptions\ProjectException;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\AiServiceInputsValidationFactory;
use Modules\ProjectManagement\app\Models\Project;
use Override;
use Symfony\Component\HttpFoundation\Response;

final class AskAiServiceRequest extends BaseApiRequest
{
    public Project $project;

    public function authorize(): bool
    {
        $this->ensureIsNotRateLimited();

        return true;
    }

    /**
     * @throws ProjectException
     */
    #[Override]
    protected function prepareForValidation(): void
    {
        $apiKey = $this->header('X-Api-Key');
        $publicKey = $this->header('X-Public-Key');
        if ( ! $publicKey || ! $apiKey) {
            throw ProjectException::invalidPublicOrApiKey();
        }
        /** @var Project $project */
        $project = Project::query()
            ->where('key', $publicKey)
            ->with('answers')
            ->firstOrFail();
        if ($apiKey !== $project->api_key) {
            throw ProjectException::invalidPublicOrApiKey();
        }
        $this->project = $project;
    }

    /**
     * @throws BindingResolutionException
     */
    #[Override]
    public function rules(): array
    {
        return AiServiceInputsValidationFactory::make($this->project)->handle()->rules();
    }

    protected function ensureIsNotRateLimited(): void
    {
        /** @var string|null $projectPublicKey */
        $projectPublicKey = $this->header('X-Public-Key');
        $rateLimiterKey = md5('ai-call:' . $projectPublicKey);
        /** @var int $maxAttempts */
        $maxAttempts = config('ai-service-management.throttle.max_attempts');
        if (RateLimiter::tooManyAttempts(
            key: $rateLimiterKey,
            maxAttempts: $maxAttempts
        )) {
            apiResponse()
                ->error()
                ->statusCode(Response::HTTP_TOO_MANY_REQUESTS)
                ->message(sprintf(
                    'Too Many Requests. Please try again in (%s) %s.',
                    $minutes = (int) ceil(RateLimiter::availableIn($rateLimiterKey) / 60),
                    Str::plural('minute', $minutes)
                ))
                ->send();
        }
        /** @var int $throttleSeconds */
        $throttleSeconds = config('ai-service-management.throttle.seconds');
        RateLimiter::increment(
            $rateLimiterKey,
            $throttleSeconds
        );
    }
}
