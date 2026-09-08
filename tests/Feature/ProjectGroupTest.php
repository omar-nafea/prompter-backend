<?php

declare(strict_types=1);

use Database\Seeders\AiCallTypeSeeder;
use Database\Seeders\AiResponseTypeSeeder;
use Database\Seeders\OutputLanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiModel;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectGroup;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        AiCallTypeSeeder::class,
        AiResponseTypeSeeder::class,
        OutputLanguageSeeder::class,
    ]);
});

test('groups CRUD works with project assignment', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project1 = $user->projects()->create([
        'name' => 'Project 1',
        'expected_outcome' => 'Outcome 1',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 1000,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'p1-key',
    ]);

    $project2 = $user->projects()->create([
        'name' => 'Project 2',
        'expected_outcome' => 'Outcome 2',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 1000,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'p2-key',
    ]);

    // Create group
    $createResponse = $this->postJson('/api/project-groups', [
        'name' => 'SLR Review Group',
        'description' => 'Systematic literature review steps',
        'instructions' => 'Treat all evidence strictly and factually. Do not invent citations.',
        'project_keys' => [$project1->key, $project2->key],
    ]);

    $createResponse->assertOk()
        ->assertJsonPath('data.name', 'SLR Review Group')
        ->assertJsonPath('data.instructions', 'Treat all evidence strictly and factually. Do not invent citations.')
        ->assertJsonPath('data.projects_count', 2);

    $groupKey = $createResponse->json('data.id');

    expect($project1->fresh()->project_group_id)->not->toBeNull()
        ->and($project2->fresh()->project_group_id)->not->toBeNull();

    // List groups
    $indexResponse = $this->getJson('/api/project-groups');
    $indexResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'SLR Review Group');

    // Show single group
    $showResponse = $this->getJson("/api/project-groups/{$groupKey}");
    $showResponse->assertOk()
        ->assertJsonPath('data.id', $groupKey)
        ->assertJsonCount(2, 'data.projects');

    // Update group (remove project2)
    $updateResponse = $this->putJson("/api/project-groups/{$groupKey}", [
        'name' => 'SLR Group Updated',
        'description' => 'Updated desc',
        'instructions' => 'Updated group instructions.',
        'project_keys' => [$project1->key],
    ]);

    $updateResponse->assertOk()
        ->assertJsonPath('data.name', 'SLR Group Updated')
        ->assertJsonPath('data.projects_count', 1);

    expect($project1->fresh()->project_group_id)->not->toBeNull()
        ->and($project2->fresh()->project_group_id)->toBeNull();

    // Delete group
    $deleteResponse = $this->deleteJson("/api/project-groups/{$groupKey}");
    $deleteResponse->assertOk();

    expect(ProjectGroup::where('key', $groupKey)->exists())->toBeFalse()
        ->and($project1->fresh()->project_group_id)->toBeNull();
});

test('group instructions are prepended to system prompt and metadata is injected into prompt and model is returned in output', function (): void {
    $user = User::factory()->create();

    $group = ProjectGroup::create([
        'name' => 'Review Group',
        'instructions' => 'GROUP INSTRUCTIONS: Be rigorous and objective.',
        'user_id' => $user->id,
    ]);

    $project = $user->projects()->create([
        'name' => 'SLR 01 Clarification',
        'expected_outcome' => 'Clarify research topic.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 2000,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'project-secret-key',
        'project_group_id' => $group->id,
        'metadata' => [
            'topic' => 'Cardiology Review',
            'disease_area' => 'Heart Failure',
        ],
    ]);

    $project->details()->create([
        'ai_temperature' => 0.2,
        'system_prompt' => 'PROJECT SYSTEM PROMPT: Extract key variables.',
        'max_output_tokens' => 800,
    ]);

    $project->inputs()->create([
        'name' => 'research_notes',
        'description' => 'Researcher notes for study',
        'data_type' => DataType::String,
        'is_required' => true,
        'max_length' => 1000,
    ]);

    $project->outputs()->create([
        'name' => 'clarification',
        'description' => 'Clarification response',
        'data_type' => DataType::String,
        'is_required' => true,
        'max_length' => 1000,
    ]);

    $project->aiModel()->create([
        'name' => 'openai/gpt-4o-mini',
        'alias' => 'GPT-4o Mini',
        'provider' => AiModelProvider::OpenRouter,
        'api_key' => 'test-openrouter-key',
    ]);

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'clarification' => 'Study clarified successfully.',
                    ], JSON_THROW_ON_ERROR),
                ],
            ]],
            'model' => 'openai/gpt-4o-mini',
            'usage' => [
                'prompt_tokens' => 80,
                'completion_tokens' => 20,
                'total_tokens' => 100,
            ],
        ]),
    ]);

    $response = $this->postJson('/api/call-ai-service', [
        'research_notes' => 'Patient cohort 2020-2024.',
    ], [
        'X-Public-Key' => $project->key,
        'X-Api-Key' => 'project-secret-key',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.clarification', 'Study clarified successfully.')
        ->assertJsonPath('data.model', 'openai/gpt-4o-mini')
        ->assertJsonPath('data.model_name', 'openai/gpt-4o-mini');

    Http::assertSent(function (Request $request): bool {
        $payload = $request->data();

        // Check that group instructions were prepended to project system prompt
        $systemMsg = data_get($payload, 'messages.0.content');
        $hasGroupInstructions = str_contains((string) $systemMsg, 'GROUP INSTRUCTIONS: Be rigorous and objective.');
        $hasProjectPrompt = str_contains((string) $systemMsg, 'PROJECT SYSTEM PROMPT: Extract key variables.');
        $groupComesFirst = strpos((string) $systemMsg, 'GROUP INSTRUCTIONS') < strpos((string) $systemMsg, 'PROJECT SYSTEM PROMPT');

        // Check user prompt has metadata section
        $userMsg = data_get($payload, 'messages.1.content');
        $hasMetadataSection = str_contains((string) $userMsg, 'Project Context / Metadata:');
        $hasMetadataValues = str_contains((string) $userMsg, '[topic]: Cardiology Review')
            && str_contains((string) $userMsg, '[disease_area]: Heart Failure');

        return $hasGroupInstructions && $hasProjectPrompt && $groupComesFirst && $hasMetadataSection && $hasMetadataValues;
    });
});
