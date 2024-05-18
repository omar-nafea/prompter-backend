<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Enums;

enum AiCallType: int
{
    case OneByOne = 1;
    case Bulk = 2;
}
