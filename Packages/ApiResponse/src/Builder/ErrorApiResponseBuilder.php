<?php

declare(strict_types=1);

namespace MohamedGaber\ApiResponse\Builder;

use Symfony\Component\HttpFoundation\Response;

final class ErrorApiResponseBuilder extends BaseApiResponseBuilder
{
    protected $errors;

    protected $meta;

    public function __construct()
    {
        $this->errors = [];
        $this->message = __('global.response.error_message');
        $this->statusCode = Response::HTTP_BAD_REQUEST;
    }

    public function withErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function withMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    public function appendMeta($meta): static
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    protected function responseData()
    {
        return array_merge(parent::responseData(), [
            'errors' => $this->errors,
            'meta' => $this->meta,
        ]);
    }
}
