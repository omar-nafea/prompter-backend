<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\InputDataType;

use Modules\ProjectManagement\app\Enums\DataType;

final class FetchInputDataTypeListAction
{
    /**
     * @return DataType[]
     */
    public function execute(): array
    {
        return DataType::cases();
    }
}
