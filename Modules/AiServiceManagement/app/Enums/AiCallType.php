<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Enums;

use App\Enums\MetaProperties\Enabled;
use App\Enums\MetaProperties\Label;
use ArchTech\Enums\Meta\Meta;

/**
 * @method string label()
 * @method string enabled()
 */
#[Meta(Label::class)]
enum AiCallType: int
{
    #[Label('One By One')]
    #[Enabled(true)]
    case OneByOne = 1;

    #[Label('Bulk')]
    #[Enabled(false)]
    case Bulk = 2;

    #[Label('Bulkx')]
    #[Enabled(false)]
    case Bulkx = 3;
}
