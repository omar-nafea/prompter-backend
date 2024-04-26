<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiCallType;
use Illuminate\Database\Eloquent\Factories\Factory;

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
