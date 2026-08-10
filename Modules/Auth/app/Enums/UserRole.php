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
enum UserRole: int
{
    use Metadata;

    #[Label('Super Admin')]
    case SuperAdmin = 1;

    #[Label('Service')]
    case Service = 2;

    #[Label('Tester')]
    case Tester = 3;

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canManageProjects(): bool
    {
        return $this === self::SuperAdmin || $this === self::Service;
    }

    /**
     * @return array<int, array{name: string, value: int}>
     */
    public static function selectOptions(): array
    {
        return array_map(
            static fn (self $case) => [
                'name' => $case->label(),
                'value' => $case->value,
            ],
            self::cases(),
        );
    }
}
