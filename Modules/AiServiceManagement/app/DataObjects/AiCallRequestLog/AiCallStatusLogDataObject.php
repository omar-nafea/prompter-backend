<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\DataObjects\AiCallRequestLog;

use App\Dtos\BaseDto;
use Illuminate\Support\Carbon;
use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

final class AiCallStatusLogDataObject extends BaseDto
{
    public function __construct(
        public AiCallRequestStatus $status,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d H:i:s')]
        public Carbon $updatedAt,
    ) {}
}
