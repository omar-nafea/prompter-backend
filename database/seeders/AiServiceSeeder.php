<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiService;
use Illuminate\Database\Seeder;

class AiServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiService::factory()->count(5)->create();
    }
}
