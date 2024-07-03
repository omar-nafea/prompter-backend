<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectManagement\app\Models\ProjectInputRule;

final class ProjectInputRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProjectInputRule::factory()->count(10)->create();
    }
}
