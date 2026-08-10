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
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Models\OutputLanguage;
use Modules\ProjectManagement\app\Models\Project;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        AiCallTypeSeeder::class,
        AiResponseTypeSeeder::class,
        OutputLanguageSeeder::class,
    ]);
});

function structuredAiProject(User $user): Project
{
    $project = $user->projects()->create([
        'name' => 'Structured flight ranking',
        'expected_outcome' => 'Return one object containing ranked flight IDs and reasons.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 2000,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'project-secret',
    ]);
    $project->details()->create([
        'ai_temperature' => 0.0,
        'system_prompt' => 'Rank only the supplied candidates.',
        'max_output_tokens' => 800,
        'response_schema' => [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['ranked'],
            'properties' => [
                'ranked' => [
                    'type' => 'array',
                    'minItems' => '{{input.ranking_request.limit}}',
                    'maxItems' => '{{input.ranking_request.limit}}',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['id', 'reason'],
                        'properties' => [
                            'id' => ['type' => 'string'],
                            'reason' => ['type' => 'string', 'maxLength' => 180],
                        ],
                    ],
                ],
            ],
        ],
    ]);
    $project->aiModel()->create([
        'name' => 'google/gemini-3.5-flash-lite',
        'alias' => 'Flight ranker',
        'provider' => AiModelProvider::OpenRouter,
        'api_key' => 'openrouter-secret',
    ]);
    $project->inputs()->create([
        'name' => 'ranking_request',
        'data_type' => DataType::String,
        'is_required' => true,
        'max_length' => 49152,
        'description' => str_repeat('Input contract. ', 30),
    ]);
    $project->outputs()->create([
        'name' => 'ranked',
        'data_type' => DataType::Json,
        'is_required' => true,
        'max_length' => 2000,
        'description' => str_repeat('Output contract. ', 30),
    ]);
    $project->outputLanguages()->attach(OutputLanguage::query()->where('name', 'English')->value('id'));

    return $project->fresh();
}

test('structured inputs and dynamic response schema are sent to OpenRouter', function (): void {
    $project = structuredAiProject(User::factory()->create());

    Http::preventStrayRequests();
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'ranked' => [['id' => 'flight-1', 'reason' => 'Lowest price.']],
                    ], JSON_THROW_ON_ERROR),
                ],
            ]],
            'usage' => [
                'prompt_tokens' => 100,
                'completion_tokens' => 20,
                'total_tokens' => 120,
            ],
        ]),
    ]);

    $this->postJson('/api/call-ai-service', [
        'ranking_request' => json_encode([
            'limit' => 1,
            'candidates' => [['id' => 'flight-1', 'price_amount' => 100]],
            'allowed_ids' => ['flight-1'],
        ], JSON_THROW_ON_ERROR),
    ], [
        'X-Public-Key' => $project->key,
        'X-Api-Key' => 'project-secret',
    ])
        ->assertOk()
        ->assertJsonPath('data.ranked.0.id', 'flight-1')
        ->assertJsonPath('data._meta.usage.total_tokens', 120);

    Http::assertSent(function (Request $request): bool {
        $payload = $request->data();

        return data_get($payload, 'messages.0.role') === 'system'
            && filled(data_get($payload, 'messages.0.content'))
            && str_contains($payload['messages'][1]['content'], '"flight-1"')
            && $payload['temperature'] === 0.0
            && $payload['max_tokens'] === 800
            && $payload['provider'] === ['require_parameters' => true]
            && data_get($payload, 'response_format.json_schema.schema.properties.ranked.minItems') === 1
            && data_get($payload, 'response_format.json_schema.schema.properties.ranked.maxItems') === 1;
    });
});

test('project llm configuration can be updated independently', function (): void {
    $user = User::factory()->create();
    $project = structuredAiProject($user);
    Sanctum::actingAs($user);

    $this->putJson("/api/projects/{$project->key}/llm-configuration", [
        'system_prompt' => 'Use the revised policy.',
        'temperature' => 0,
        'max_output_tokens' => 600,
        'response_schema' => [
            'type' => 'object',
            'properties' => ['ranked' => ['type' => 'array']],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('data.ai_system_prompt', 'Use the revised policy.')
        ->assertJsonPath('data.ai_max_output_tokens', 600);

    expect($project->details()->firstOrFail()->response_schema)
        ->toBe([
            'type' => 'object',
            'properties' => ['ranked' => ['type' => 'array']],
        ]);
});
