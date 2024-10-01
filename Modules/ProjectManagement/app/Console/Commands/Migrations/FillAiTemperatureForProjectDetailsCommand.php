<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Modules\ProjectManagement\app\Models\Project;

final class FillAiTemperatureForProjectDetailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-ai-temperature-for-project-details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Filling ai temperature for project details...');
        Project::all()->each(function (Project $project) {
            return $project->details()->updateOrCreate([], [
                'ai_temperature' => 0.9,
            ]);
        });

        return self::SUCCESS;
    }
}
