<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Enums;

use App\Enums\MetaProperties\Example;
use App\Enums\MetaProperties\Label;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;

/**
 * @method string label()
 * @method string example()
 */
#[Meta(Label::class, Example::class)]
enum DataType: int
{
    use Metadata;

    #[Label('String')]
    #[Example('some random string')]
    case String = 1;

    #[Label('Number')]
    #[Example('random integer number')]
    case Integer = 2;

    #[Label('Decimal')]
    #[Example('random float number')]
    case Float = 3;

    #[Label('Binary')]
    #[Example('true/false')]
    case Boolean = 4;

    #[Label('Categorical')]
    case Enum = 5;
}
