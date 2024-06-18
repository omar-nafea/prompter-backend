<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Handler;

use App\Exceptions\Handler as BaseHandler;
use Illuminate\Http\Request;
use Modules\Exceptions\app\Factory\Contract\ExceptionMappingFactory;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;

final class Handler extends BaseHandler
{
    public static bool $shouldChangeJsonResponse = true;

    /**
     * @var string[]
     */
    protected array $dontMapping = [
        FatalError::class,
    ];

    public function render($request, Throwable $exception)
    {
        if ($this->isShouldBeHandled($request, $exception)) {
            self::$shouldChangeJsonResponse = false; //prevent map looping
            app(ExceptionMappingFactory::class)->handle($exception);
        }

        return parent::render($request, $exception);
    }

    protected function isShouldBeHandled(Request $request, Throwable $exception): bool
    {
        return $this->isRequestWantsJsonOrAjax($request) &&
            self::$shouldChangeJsonResponse &&
            ! $this->isExceptionShouldBeIgnored($exception);
    }

    protected function isRequestWantsJsonOrAjax(Request $request): bool
    {
        return $request->wantsJson() || $request->ajax();
    }

    protected function isExceptionShouldBeIgnored(Throwable $exception): bool
    {
        return in_array(get_class($exception), $this->dontMapping);
    }
}
