<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;

class ProjectObjectiveQuestionFactory extends Factory
{
    protected $model = ProjectObjectiveQuestion::class;

    public function definition(): array
    {
        return [
            'question' => $this->faker->unique()->sentence(5),
            'status' => 1,
        ];
    }
}
