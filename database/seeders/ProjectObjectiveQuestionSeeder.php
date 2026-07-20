<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;

final class ProjectObjectiveQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            'Who is the target audience for this project?',
            'What tone should the generated output use?',
        ];

        foreach ($questions as $question) {
            ProjectObjectiveQuestion::firstOrCreate(
                ['question' => $question],
                ['status' => 1],
            );
        }
    }
}
