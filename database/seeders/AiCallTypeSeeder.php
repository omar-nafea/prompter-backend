<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AiServiceManagement\app\Models\AiCallType;

final class AiCallTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        AiCallType::factory(10)->create();
        AiCallType::create([
            'name' => 'one by one',
            'description' => 'One Query per request',
            'type' => 1,
            'status' => 1
        ]);
        AiCallType::create([
            'name' => 'bulk',
            'description' => 'Bulk Queries per request',
            'status' => 0,
            'type' => 1
        ]);
    }
}
