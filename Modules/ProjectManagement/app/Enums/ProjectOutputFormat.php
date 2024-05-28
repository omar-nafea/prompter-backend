<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Enums;

use App\Enums\MetaProperties\Enabled;
use App\Enums\MetaProperties\Label;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;

/**
 * @method string label()
 * @method string enabled()
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

    public static function enabledCases(): array
    {
        return array_filter(self::cases(), fn (ProjectOutputFormat $case) => $case->enabled());
    }
}
