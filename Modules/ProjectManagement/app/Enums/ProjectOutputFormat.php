<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Enums;

use App\Enums\MetaProperties\Enabled;
use App\Enums\MetaProperties\Label;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;

/**
 * @method string label()
 * @method bool enabled()
 */
#[Meta(Label::class, Enabled::class)]
enum ProjectOutputFormat: int
{
    use Metadata;

    #[Label('JSON')]
    #[Enabled(true)]
    case Json = 1;

    #[Label('XML')]
    #[Enabled(false)]
    case Xml = 2;

    /**
     * @return self[]
     */
    public static function enabledCases(): array
    {
        return array_filter(
            array: self::cases(),
            callback: fn (self $case): bool => $case->enabled()
        );
    }
}
