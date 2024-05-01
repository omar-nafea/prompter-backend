<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\AiServiceManagement\app\Models\AiResponseType;

class AiResponseTypeFactory extends Factory
{
    protected $model = AiResponseType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
            'description' => $this->faker->text,
            'type' => $this->faker->numberBetween(1, 3),
            'status' => $this->faker->boolean,
        ];
    }
}
