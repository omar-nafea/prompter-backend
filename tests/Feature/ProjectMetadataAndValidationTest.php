<?php

declare(strict_types=1);

use Database\Seeders\AiCallTypeSeeder;
use Database\Seeders\AiResponseTypeSeeder;
use Database\Seeders\OutputLanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\OneByOneCall;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        AiCallTypeSeeder::class,
        AiResponseTypeSeeder::class,
        OutputLanguageSeeder::class,
    ]);
});

test('project metadata can be saved and patched with explicit update semantics', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = $user->projects()->create([
        'name' => 'Metadata test project',
        'expected_outcome' => 'Testing metadata behavior.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 500,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'meta-key-test',
        'metadata' => [
            'code' => 'P01',
            'intent' => 'Clarify research idea',
        ],
    ]);

    expect($project->fresh()->metadata)->toBe([
        'code' => 'P01',
        'intent' => 'Clarify research idea',
    ]);

    // Test: Omitted metadata preserves existing metadata
    $this->patchJson("/api/projects/{$project->key}", [
        'name' => 'Renamed project without touching metadata',
    ])->assertOk();

    expect($project->fresh()->metadata)->toBe([
        'code' => 'P01',
        'intent' => 'Clarify research idea',
    ]);

    // Test: Supplying new metadata replaces it
    $this->patchJson("/api/projects/{$project->key}", [
        'metadata' => [
            'code' => 'P02',
            'intent' => 'Updated intent',
        ],
    ])->assertOk();

    expect($project->fresh()->metadata)->toBe([
        'code' => 'P02',
        'intent' => 'Updated intent',
    ]);

    // Test: Supplying null clears it
    $this->patchJson("/api/projects/{$project->key}", [
        'metadata' => null,
    ])->assertOk();

    expect($project->fresh()->metadata)->toBeNull();
});

test('OneByOneCall categorical validation accepts single scalar value and validates against enumValues', function (): void {
    $user = User::factory()->create();
    $project = $user->projects()->create([
        'name' => 'Categorical test project',
        'expected_outcome' => 'Testing categorical input validation.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 500,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'cat-key-test',
    ]);

    $frameworkInput = $project->inputs()->create([
        'name' => 'framework_suggestion',
        'data_type' => DataType::Enum,
        'is_required' => true,
        'description' => 'Suggested framework',
        'max_length' => 1000,
    ]);
    $frameworkInput->enumValues()->createMany([
        ['value' => 'PICO'],
        ['value' => 'SPIDER'],
    ]);

    $jsonGapsInput = $project->inputs()->create([
        'name' => 'identified_gaps_json',
        'data_type' => DataType::Json,
        'is_required' => true,
        'description' => 'Array of gaps',
        'max_length' => 1000,
    ]);

    $strategy = new OneByOneCall($project->fresh(['inputs.enumValues']));
    $rules = $strategy->rules();

    // 1. Single valid scalar "PICO" passes
    $validator1 = validator([
        'framework_suggestion' => 'PICO',
        'identified_gaps_json' => [],
    ], $rules);
    expect($validator1->passes())->toBeTrue();

    // 2. Valid array ["SPIDER"] passes
    $validator2 = validator([
        'framework_suggestion' => ['SPIDER'],
        'identified_gaps_json' => ['gap 1', 'gap 2'],
    ], $rules);
    expect($validator2->passes())->toBeTrue();

    // 3. Invalid enum value fails
    $validator3 = validator([
        'framework_suggestion' => 'INVALID_FRAMEWORK',
        'identified_gaps_json' => [],
    ], $rules);
    expect($validator3->fails())->toBeTrue();

    // 4. Empty array [] for required JSON collection passes
    $validator4 = validator([
        'framework_suggestion' => 'PICO',
        'identified_gaps_json' => [],
    ], $rules);
    expect($validator4->passes())->toBeTrue();

    // 5. Missing required JSON collection fails
    $validator5 = validator([
        'framework_suggestion' => 'PICO',
    ], $rules);
    expect($validator5->fails())->toBeTrue();
});

test('OneByOneCall maps decimal inputs to numeric with percentage bounds and never caps numerics by length', function (): void {
    $user = User::factory()->create();
    $project = $user->projects()->create([
        'name' => 'Decimal test project',
        'expected_outcome' => 'Testing decimal input validation.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 500,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'decimal-key-test',
    ]);

    $project->inputs()->create([
        'name' => 'coverage_percentage',
        'data_type' => DataType::Float,
        'is_required' => true,
        'description' => 'Recorded coverage from 0 to 100',
        'max_length' => 10000,
    ]);
    $project->inputs()->create([
        'name' => 'identified_gaps_json',
        'data_type' => DataType::Json,
        'is_required' => true,
        'description' => 'Array of gaps',
        'max_length' => 1000,
    ]);

    $strategy = new OneByOneCall($project->fresh(['inputs.enumValues']));
    $rules = $strategy->rules();

    // Laravel has no 'float' rule: decimals validate as numeric.
    expect($rules['coverage_percentage'])->toContain('numeric')
        ->and($rules['coverage_percentage'])->not->toContain('float')
        ->and($rules['coverage_percentage'])->toContain('min:0')
        ->and($rules['coverage_percentage'])->toContain('max:100')
        ->and($rules['coverage_percentage'])->not->toContain('max:10000');

    expect(validator([
        'coverage_percentage' => 42.5,
        'identified_gaps_json' => [],
    ], $rules)->passes())->toBeTrue();

    expect(validator([
        'coverage_percentage' => 150,
        'identified_gaps_json' => [],
    ], $rules)->fails())->toBeTrue();

    // Required JSON collections reject explicit null but accept empty arrays.
    expect(validator([
        'coverage_percentage' => 10,
        'identified_gaps_json' => null,
    ], $rules)->fails())->toBeTrue();

    expect(validator([
        'coverage_percentage' => 10,
        'identified_gaps_json' => [],
    ], $rules)->passes())->toBeTrue();
});

test('OneByOneCall enum validation rejects nested arrays', function (): void {
    $user = User::factory()->create();
    $project = $user->projects()->create([
        'name' => 'Nested enum test project',
        'expected_outcome' => 'Testing nested enum input validation.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 500,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'nested-enum-key-test',
    ]);

    $frameworkInput = $project->inputs()->create([
        'name' => 'framework_suggestion',
        'data_type' => DataType::Enum,
        'is_required' => true,
        'description' => 'Suggested framework',
        'max_length' => 1000,
    ]);
    $frameworkInput->enumValues()->createMany([
        ['value' => 'PICO'],
        ['value' => 'SPIDER'],
    ]);

    $strategy = new OneByOneCall($project->fresh(['inputs.enumValues']));
    $rules = $strategy->rules();

    expect(validator([
        'framework_suggestion' => [['PICO']],
    ], $rules)->fails())->toBeTrue();

    expect(validator([
        'framework_suggestion' => 'PICO',
    ], $rules)->passes())->toBeTrue();
});
