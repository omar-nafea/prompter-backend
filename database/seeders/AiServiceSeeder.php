<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AiServiceManagement\app\Models\AiService;

final class AiServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //        AiService::factory()->count(5)->create();
        $data = [
            'GPT 3.5',
            'GPT 3.5 turbo',
            'GPT 4.0',
            'GPT 4.0 turbo',
            'Gemini',
        ];
        foreach ($data as $idx => $name) {
            AiService::create([
                'name' => $name,
                'description' => $name,
                'price' => 0,
                'status' => $idx === 0 ? 1 : 0,
            ]);
        }
    }
}
