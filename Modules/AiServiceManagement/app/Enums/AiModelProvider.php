<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Enums;

use App\Enums\MetaProperties\Label;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;

/**
 * @method string label()
 */
#[Meta(Label::class)]
enum AiModelProvider: int
{
    use Metadata;

    #[Label('OpenAI')]
    case OpenAi = 1;

    #[Label('Google Gemini')]
    case Gemini = 2;

    #[Label('Anthropic')]
    case Anthropic = 3;

    #[Label('Custom (OpenAI-compatible)')]
    case OpenAiCompatible = 4;

    #[Label('OpenRouter')]
    case OpenRouter = 5;

    /**
     * @return array<int, array{name: string, value: int, requires_connector_url: bool}>
     */
    public static function selectOptions(): array
    {
        return array_map(
            static fn (self $case) => [
                'name' => $case->label(),
                'value' => $case->value,
                'requires_connector_url' => $case === self::OpenAiCompatible,
            ],
            self::cases(),
        );
    }
}
