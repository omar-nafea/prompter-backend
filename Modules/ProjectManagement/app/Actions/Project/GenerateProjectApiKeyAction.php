<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Str;

class GenerateProjectApiKeyAction
{
    public function execute()
    {
        //todo implement logic like sanctum tokens generation
        return Str::random(32);
    }
}
