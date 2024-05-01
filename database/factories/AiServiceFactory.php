<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\AiServiceManagement\app\Models\AiService;

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
