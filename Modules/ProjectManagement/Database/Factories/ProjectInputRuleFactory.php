<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProjectManagement\app\Models\ProjectInputRule;

final class ProjectInputRuleFactory extends Factory
{
    protected $model = ProjectInputRule::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->unique()->word,
            'name' => $this->faker->unique()->word,
            'status' => 1,
        ];
    }
}
