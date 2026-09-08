<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Actions\Project\UpdateProjectAction;
use Modules\ProjectManagement\app\Dtos\Project\UpdateProjectDto;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;

uses(RefreshDatabase::class);

test('inputs and outputs omitted from an update are deleted', function (): void {
    $user = User::factory()->create();
    $project = $user->projects()->create([
        'name' => 'Deletable fields project',
        'expected_outcome' => 'The original expected outcome.',
        'ai_call_type_id' => AiCallType::factory()->create()->id,
        'ai_response_type_id' => AiResponseType::factory()->create()->id,
        'max_output_length' => 200,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'deletable-fields-key',
    ]);

    $keptInput = $project->inputs()->create([
        'name' => 'kept input',
        'data_type' => DataType::String,
        'is_required' => true,
        'max_length' => 100,
        'description' => 'Test description',
    ]);
    $removedInput = $project->inputs()->create([
        'name' => 'removed input',
        'data_type' => DataType::Enum,
        'is_required' => false,
        'max_length' => 100,
        'description' => 'Test description',
    ]);
    $removedInput->enumValues()->createMany([
        ['value' => 'a'],
        ['value' => 'b'],
    ]);

    $keptOutput = $project->outputs()->create([
        'name' => 'kept output',
        'data_type' => DataType::String,
        'is_required' => true,
        'max_length' => 100,
        'description' => 'Test description',
    ]);
    $removedOutput = $project->outputs()->create([
        'name' => 'removed output',
        'data_type' => DataType::String,
        'is_required' => false,
        'max_length' => 100,
        'description' => 'Test description',
    ]);

    $dto = UpdateProjectDto::from([
        'project' => $project,
        'projectDto' => [
            'name' => 'Deletable fields project',
            'expected_outcome' => 'The original expected outcome.',
            'ai_call_type_id' => $project->ai_call_type_id,
            'ai_response_type_id' => $project->ai_response_type_id,
            'max_output_length' => 200,
            'output_format' => ProjectOutputFormat::Json,
        ],
        'projectDetailsDto' => ['ai_temperature' => 0.9],
        'projectAiModelDto' => null,
        'objective_questions' => [],
        'project_inputs' => [[
            'id' => $keptInput->id,
            'name' => 'kept input renamed',
            'data_type' => DataType::String,
            'is_required' => true,
            'max_length' => 100,
            'description' => 'Test description',
        ]],
        'project_outputs' => [[
            'id' => $keptOutput->id,
            'name' => 'kept output',
            'data_type' => DataType::String,
            'is_required' => true,
            'max_length' => 100,
            'description' => 'Test description',
        ]],
        'output_languages' => [],
        'authUser' => $user,
        'hasMetadata' => false,
        'metadata' => null,
    ]);

    app(UpdateProjectAction::class)->execute($dto);

    expect($project->inputs()->count())->toBe(1)
        ->and($project->outputs()->count())->toBe(1)
        ->and($keptInput->fresh()->name)->toBe('kept input renamed')
        ->and($removedInput->fresh()->trashed())->toBeTrue()
        ->and($removedOutput->fresh()->trashed())->toBeTrue()
        ->and($removedInput->enumValues()->count())->toBe(0);
});
