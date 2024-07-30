<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Enums;

enum AiCallRequestStatus: int
{
    case Started = 1;
    case Prepared = 2;
    case Sent = 3;
    case Failed = 4;
}
