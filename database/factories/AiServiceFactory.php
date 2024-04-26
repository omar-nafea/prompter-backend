<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiService;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiServiceFactory extends Factory
{
    protected $model = AiService::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
            'description' => $this->faker->text,
            'price' => $this->faker->numberBetween(),
            'status' => $this->faker->boolean,
        ];
    }
}
