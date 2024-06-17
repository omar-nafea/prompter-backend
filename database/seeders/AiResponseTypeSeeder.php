<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AiServiceManagement\app\Models\AiResponseType;

final class AiResponseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiResponseType::factory()->count(3)->create();
    }
}
