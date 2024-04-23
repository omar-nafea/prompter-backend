<?php

declare(strict_types=1);

namespace Modules\Auth\app\Enums;

use App\Enums\MetaProperties\Label;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;

/**
 * @method string label()
 */
#[Meta(Label::class)]
enum UserStatus: int
{
    use Metadata;

    #[Label('Active')]
    case Active = 1;

    #[Label('Blocked')]
    case Blocked = 0;

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
