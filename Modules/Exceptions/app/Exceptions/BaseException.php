<?php

namespace Modules\Exceptions\app\Exceptions;

use Exception;
use ReflectionClass;
use Throwable;

abstract class BaseException extends Exception
{
    protected mixed $id;
    protected mixed $name;

    public function __construct(?string $message = "", int $code = 0, ?Throwable $previous = null, $id = '', $name = '')
    {
        $this->id = $id;
        $this->name = $name;
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        if (request()->acceptsJson()) {
            return $this->sendJsonResponse();
        }
    }

    protected function sendJsonResponse()
    {
        $response = apiResponse()->error()
            ->message($this->message)
            ->statusCode($this->getCode())
            ->withMeta([
                'exception' => [
                    'id' => $this->id,
                    'name' => $this->name
                ]
            ]);
        if (method_exists($this, 'withResponse'))
            $response = $this->withResponse($response);
        return $response->send();
    }

    protected static function getClassShortName()
    {
        return (new ReflectionClass(static::class))->getShortName();
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getName(): mixed
    {
        return $this->name;
    }

    public function report() : bool
    {
        return true; // will not be reported in logs
    }


}
