<?php

declare(strict_types=1);

use Database\Seeders\AiCallTypeSeeder;
use Database\Seeders\AiResponseTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiModel;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Models\Project;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([AiCallTypeSeeder::class, AiResponseTypeSeeder::class]);
});

function createProjectWithModel(User $user, array $modelAttributes = []): Project
{
    /** @var Project $project */
    $project = $user->projects()->create([
        'name' => 'Project ' . uniqid(),
        'expected_outcome' => 'Expected outcome text for tests.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 200,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'project-api-key-' . uniqid(),
    ]);

    $project->aiModel()->create([
        'name' => $modelAttributes['name'] ?? 'gpt-4o-mini',
        'alias' => $modelAttributes['alias'] ?? 'Main model',
        'provider' => $modelAttributes['provider'] ?? AiModelProvider::OpenAi,
        'api_key' => $modelAttributes['api_key'] ?? 'secret-key',
        'connector_url' => $modelAttributes['connector_url'] ?? null,
    ]);

    return $project->fresh(['aiModel']);
}

test('ai model providers endpoint is available', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/ai-model-providers')
        ->assertOk()
        ->assertJsonStructure(['data' => ['providers']]);
});

test('global ai model endpoints are removed', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/ai-model')->assertNotFound();
    $this->putJson('/api/ai-model', [])->assertNotFound();
});

test('each project keeps its own ai model configuration', function (): void {
    $user = User::factory()->create();
    $projectA = createProjectWithModel($user, [
        'name' => 'model-a',
        'alias' => 'Model A',
        'api_key' => 'key-a',
    ]);
    $projectB = createProjectWithModel($user, [
        'name' => 'model-b',
        'alias' => 'Model B',
        'provider' => AiModelProvider::OpenRouter,
        'api_key' => 'key-b',
    ]);

    expect($projectA->aiModel?->name)->toBe('model-a')
        ->and($projectB->aiModel?->name)->toBe('model-b')
        ->and($projectA->aiModel?->id)->not->toBe($projectB->aiModel?->id)
        ->and(DB::table('ai_models')->where('project_id', $projectA->id)->value('api_key'))
        ->not->toBe('key-a')
        ->and(AiModel::count())->toBe(2);

    Http::preventStrayRequests();
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
        ]),
    ]);

    $resolved = $projectA->aiModel()->firstOrFail();
    $connection = app(\Modules\AiServiceManagement\app\Gateway\AiProviderResolver::class)
        ->for($resolved->provider)
        ->test($resolved);

    expect($connection['success'])->toBeTrue();
    Http::assertSent(fn ($request) => $request['model'] === 'model-a');
});

test('testers cannot create projects', function (): void {
    Sanctum::actingAs(User::factory()->tester()->create());

    $this->postJson('/api/projects', [
        'name' => 'Nope',
        'expected_outcome' => 'Nope outcome',
    ])->assertForbidden();
});

test('role gates distinguish super admin service and tester', function (): void {
    expect(User::factory()->create()->can('manage-projects'))->toBeTrue()
        ->and(User::factory()->tester()->create()->can('manage-projects'))->toBeFalse()
        ->and(User::factory()->superAdmin()->create()->can('manage-users'))->toBeTrue()
        ->and(User::factory()->create()->can('manage-users'))->toBeFalse();
});
