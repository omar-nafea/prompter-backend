<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Enums;

enum AiServiceModelsType: int
{
    case GPT_3_5 = 1;
    case GPT_3_5_turbo = 2;
    case GPT_4_0 = 3;
    case GPT_4o = 4;
    case Gemini = 5;

    public static function supportsTemperatureCases(): array
    {
        return [
            self::GPT_4_0,
            self::Gemini,
        ];
    }

    public static function supportsTemperatureIds(): array
    {
        return collect(self::supportsTemperatureCases())
            ->map(static fn (self $type) => $type->value)
            ->toArray();
    }
}
