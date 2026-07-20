<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectManagement\app\Enums\OutputLanguageStatus;
use Modules\ProjectManagement\app\Models\OutputLanguage;

final class OutputLanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            'English',
            'Arabic',
            'French',
            'Spanish',
            'German',
            'Italian',
            'Portuguese',
            'Russian',
            'Chinese',
            'Japanese',
            'Hindi',
            'Turkish',
        ];

        foreach ($languages as $name) {
            OutputLanguage::firstOrCreate(
                ['name' => $name],
                ['status' => OutputLanguageStatus::Enabled],
            );
        }
    }
}
