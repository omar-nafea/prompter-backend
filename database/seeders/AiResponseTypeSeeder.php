<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiResponseType;
use Illuminate\Database\Seeder;

class AiResponseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiResponseType::factory()->count(3)->create();
    }
}
