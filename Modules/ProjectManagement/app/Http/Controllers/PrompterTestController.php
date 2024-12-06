<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;
use Modules\AiServiceManagement\app\Models\AiCallRequestLog;

final class PrompterTestController
{
    public function samples()
    {
        $projectId = 6;
        abort_if(
            ! auth()->user()->projects()->where('id', $projectId)->exists(),
            403,
            'You are not authorized to access this project'
        );
        $data = AiCallRequestLog::where('project_id', $projectId)
            ->whereBetween('created_at', ['2024-12-01', '2024-12-05'])
            ->where('status', AiCallRequestStatus::Sent)
            ->limit(30)
            ->pluck('request_body');

        return apiResponse()->success()->data($data->toArray())->send();
    }
}
