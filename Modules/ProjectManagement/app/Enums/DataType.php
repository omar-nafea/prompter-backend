<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Enums;

enum DataType: int
{
    case String = 1;
    case Integer = 2;
    case Float = 3;
    case Boolean = 4;
    case Enum = 5;
}
