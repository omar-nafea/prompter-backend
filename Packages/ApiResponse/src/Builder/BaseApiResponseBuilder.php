<?php

declare(strict_types=1);

namespace MohamedGaber\ApiResponse\Builder;

abstract class BaseApiResponseBuilder
{
    protected $message;

    protected $statusCode;

    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    public function statusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function send()
    {
        return response()->json($this->responseData(), $this->statusCode)->throwResponse();
    }

    protected function responseData()
    {
        return [
            'message' => $this->message,
        ];
    }
}
