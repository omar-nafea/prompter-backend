<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Modules\ProjectManagement\app\Actions\Project\CheckProjectPromptHasExceededMaxTokensAction;
use Modules\ProjectManagement\app\Models\Project;

final class FillHasExceededMaxTokensForProjectDetailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-has-exceeded-max-tokens-for-project-details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Project::query()->get()->each(function (Project $project): void {
            $project->details()->updateOrCreate([], [
                'has_exceeded_max_tokens' => app(CheckProjectPromptHasExceededMaxTokensAction::class)->execute(
                    project : $project
                ),
            ]);
        });

        return self::SUCCESS;
    }
}
