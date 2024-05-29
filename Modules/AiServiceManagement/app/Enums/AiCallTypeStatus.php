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
enum AiCallTypeStatus: int
{
    use Metadata;

    #[Label('Enabled')]
    case Enabled = 1;

    #[Label('Disabled')]
    case Disabled = 0;
}
