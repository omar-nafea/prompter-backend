<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use Illuminate\Validation\ValidationException as BaseValidationException;
use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;
use Throwable;

final class ValidationException extends BaseException
{
    public function __construct(
        public BaseValidationException $baseValidationException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        $id = '',
        $name = ''
    ) {
        parent::__construct(
            $this->baseValidationException->getMessage(),
            $code ?: ResponseStatusCode::HTTP_UNPROCESSABLE_ENTITY,
            $previous,
            $id,
            $name
        );
    }

    protected function withResponse(ErrorApiResponseBuilder $response)
    {
        $errors = $this->baseValidationException->errors();
        if ( ! count($errors)) {
            return null;
        }

        $transformedErrors = [];
        foreach ($errors as $field => $messages) {
            $transformedErrors[$field] = $messages[0];
        }

        return $response->withMeta([])
            ->withErrors($transformedErrors)
            ->statusCode($this->baseValidationException->status);
    }
}
