<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;

class ProjectObjectiveQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProjectObjectiveQuestion::factory()->count(5)->create();
    }
}
