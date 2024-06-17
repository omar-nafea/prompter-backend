<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\Contracts;

interface AiCallTypeStrategy
{

    /**
     * @return array<string,string[]>
     */
    public function rules(): array;
}
