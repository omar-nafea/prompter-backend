<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\app\Models\User;

uses(RefreshDatabase::class);

test('an AI model is saved only after a successful connection test', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $payload = [
        'name' => 'gpt-4o-mini',
        'alias' => 'Main model',
        'provider' => 1,
        'api_key' => 'secret-key',
    ];

    Http::fakeSequence('api.openai.com/*')
        ->push(['error' => ['message' => 'Invalid key']], 401)
        ->push(['choices' => [['message' => ['content' => 'ok']]]]);

    $this->putJson('/api/ai-model', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('connection');
    $this->assertDatabaseCount('ai_models', 0);

    $this->putJson('/api/ai-model', $payload)
        ->assertOk()
        ->assertJsonPath('data.name', 'gpt-4o-mini')
        ->assertJsonMissing(['api_key' => 'secret-key']);

    $this->assertDatabaseCount('ai_models', 1);
    expect(DB::table('ai_models')->value('api_key'))->not->toBe('secret-key');
});

test('an OpenAI-compatible model uses and stores its connector URL', function (): void {
    Sanctum::actingAs(User::factory()->create());
    Http::preventStrayRequests();
    Http::fake([
        'https://api.z.ai/api/paas/v4/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
        ]),
    ]);

    $this->putJson('/api/ai-model', [
        'name' => 'glm-5.2',
        'alias' => 'GLM 5.2',
        'provider' => 4,
        'api_key' => 'z-ai-secret-key',
        'connector_url' => 'https://api.z.ai/api/paas/v4/chat/completions',
    ])->assertOk()
        ->assertJsonPath('data.connector_url', 'https://api.z.ai/api/paas/v4/chat/completions');

    $this->assertDatabaseHas('ai_models', [
        'name' => 'glm-5.2',
        'provider' => 4,
        'connector_url' => 'https://api.z.ai/api/paas/v4/chat/completions',
    ]);
});
