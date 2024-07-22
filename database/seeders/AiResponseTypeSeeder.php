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
//        AiResponseType::factory()->count(3)->create();
        AiResponseType::create([
            'name' => 'instant response',
            'description' => 'Get The request response instantly',
            'type' =>3,
            'status' => 1
        ]);
        AiResponseType::create([
            'name' => 'webhook',
            'description' => 'Get The request response later by webhook',
            'type' =>2,
            'status' => 0
        ]);
        AiResponseType::create([
            'name' => 'request id to call later',
            'description' => 'Request respond with unique id to call it later to get your response',
            'type' =>3,
            'status' => 0
        ]);
    }
}
