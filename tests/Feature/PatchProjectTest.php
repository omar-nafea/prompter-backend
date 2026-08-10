<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;

uses(RefreshDatabase::class);

test('a project output description can be patched without the full project payload', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = $user->projects()->create([
        'name' => 'Partial update project',
        'expected_outcome' => 'The original expected outcome.',
        'ai_call_type_id' => AiCallType::factory()->create()->id,
        'ai_response_type_id' => AiResponseType::factory()->create()->id,
        'max_output_length' => 200,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'partial-update-key',
    ]);
    $output = $project->outputs()->create([
        'name' => 'summary',
        'data_type' => DataType::String,
        'is_required' => true,
        'max_length' => 100,
        'description' => 'Old description',
    ]);

    $this->patchJson("/api/projects/{$project->key}", [
        'project_outputs' => [[
            'id' => $output->id,
            'description' => 'Updated description',
        ]],
    ])->assertOk();

    expect($output->fresh()->description)->toBe('Updated description')
        ->and($project->fresh()->name)->toBe('Partial update project');
});
