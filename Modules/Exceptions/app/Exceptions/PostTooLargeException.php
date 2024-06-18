<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use Illuminate\Http\Exceptions\PostTooLargeException as BasePostTooLargeException;
use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;
use Throwable;

final class PostTooLargeException extends BaseException
{
    public function __construct(
        public BasePostTooLargeException $basePostTooLargeException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        mixed$id = '',
        mixed$name = ''
    ) {
        parent::__construct(
            $message ?: $this->basePostTooLargeException->getMessage() ?: __('Post Request Body Data Too Large'),
            $code ?: ResponseStatusCode::HTTP_REQUEST_ENTITY_TOO_LARGE,
            $previous ?: $this->basePostTooLargeException->getPrevious(),
            $id ?: $code ?: $this->basePostTooLargeException->getCode(),
            $name ?: self::getClassShortName()
        );
    }

    //    protected function withResponse(ErrorApiResponseBuilder $response)
    //    {
    //        return $response->withMeta([]);
    //    }
}
