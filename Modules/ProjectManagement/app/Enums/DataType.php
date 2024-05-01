<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Enums;

use App\Enums\MetaProperties\Label;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;

/**
 * @method string label()
 */
#[Meta(Label::class)]
enum DataType: int
{
    use Metadata;

    #[Label('String')]
    case String = 1;

    #[Label('Number')]
    case Integer = 2;

    #[Label('Decimal')]
    case Float = 3;

    #[Label('Binary')]
    case Boolean = 4;

    #[Label('Categorical')]
    case Enum = 5;
}
