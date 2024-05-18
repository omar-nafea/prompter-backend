<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\Contracts;

interface AiCallTypeStrategy
{
    public function rules(): array;
}
