<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiCallType;
use Illuminate\Database\Seeder;

class AiCallTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiCallType::factory(10)->create();
    }
}
