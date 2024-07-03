<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Dtos;

use App\Dtos\BaseDto;
use Modules\AiServiceManagement\app\Http\Requests\AskAiServiceRequest;
use Modules\ProjectManagement\app\Models\Project;

final class AskAiServiceDto extends BaseDto
{
    public function __construct(
        public Project $project,
        /** @var array<string,mixed> */
        public array $data
    ) {}

    public static function fromAskAiServiceRequest(AskAiServiceRequest $request): self
    {
        /** @var array<string,mixed> $data */
        $data = $request->validated();

        return new self(
            project: $request->project,
            data: $data
        );
    }
}
