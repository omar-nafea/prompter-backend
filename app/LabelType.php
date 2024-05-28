<?php

declare(strict_types=1);

namespace App;

use App\Enums\MetaProperties\Label;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;

/**
 * @method string label()
 */
#[Meta(Label::class)]
enum LabelType: string
{
    use Metadata;

    #[Label('Label')]
    case test = 'a';
}
