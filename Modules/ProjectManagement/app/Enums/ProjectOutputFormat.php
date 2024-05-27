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
enum ProjectOutputFormat: int
{
    use Metadata;

    #[Label('JSON')]
    case Json = 1;

    //    #[Label('XML')]
    //    case Xml = 2;
}
