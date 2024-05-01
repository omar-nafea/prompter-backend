<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\AiServiceManagement\app\Models\AiCallType;

class AiCallTypeFactory extends Factory
{
    protected $model = AiCallType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->text,
            'status' => $this->faker->boolean,
            'type' => $this->faker->numberBetween(1, 3),

        ];
    }
}
