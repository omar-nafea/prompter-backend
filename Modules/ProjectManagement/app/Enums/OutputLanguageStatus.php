<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\App\Enums;

enum OutputLanguageStatus: int
{
    case Enabled = 1;
    case Disabled = 0;
}
