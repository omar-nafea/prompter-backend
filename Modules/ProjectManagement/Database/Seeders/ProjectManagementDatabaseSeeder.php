<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\Database\Seeders;

use Illuminate\Database\Seeder;

final class ProjectManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ProjectInputRuleSeeder::class,
            ProjectObjectiveQuestionSeeder::class,
        ]);
    }
}
