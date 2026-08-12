<?php

declare(strict_types=1);

use Database\Seeders\AiCallTypeSeeder;
use Database\Seeders\AiResponseTypeSeeder;
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

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([AiCallTypeSeeder::class, AiResponseTypeSeeder::class]);
});

function projectForAccessTest(User $owner, string $name): Project
{
    return $owner->projects()->create([
        'name' => $name,
        'expected_outcome' => 'Return a useful test response.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 200,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'project-key-' . $name,
    ]);
}

test('super admins see every project and its creator', function (): void {
    $firstOwner = User::factory()->create(['name' => 'First Owner']);
    $secondOwner = User::factory()->create(['name' => 'Second Owner']);
    $firstProject = projectForAccessTest($firstOwner, 'First Project');
    $secondProject = projectForAccessTest($secondOwner, 'Second Project');

    Sanctum::actingAs(User::factory()->superAdmin()->create());

    $projects = collect($this->getJson('/api/projects?per_page=50')->assertOk()->json('data'));

    expect($projects)->toHaveCount(2)
        ->and($projects->firstWhere('id', $firstProject->key)['creator']['name'])->toBe('First Owner')
        ->and($projects->firstWhere('id', $secondProject->key)['creator']['email'])
        ->toBe($secondOwner->email->toNative());
});

test('test input generation uses the application default model', function (): void {
    $owner = User::factory()->create();
    $project = projectForAccessTest($owner, 'Input Generation Project');
    $project->aiModel()->create([
        'name' => 'project-model',
        'alias' => 'Project model',
        'provider' => AiModelProvider::OpenAi,
        'api_key' => 'project-secret',
    ]);
    $nameInput = $project->inputs()->create([
        'name' => 'customer_name',
        'data_type' => DataType::String,
        'is_required' => true,
        'max_length' => 100,
        'description' => 'The full name of the customer.',
    ]);
    $categoryInput = $project->inputs()->create([
        'name' => 'category',
        'data_type' => DataType::Enum,
        'is_required' => true,
        'max_length' => 10,
        'description' => 'The customer category.',
    ]);
    $option = $categoryInput->enumValues()->create(['value' => 'Premium']);
    AiModel::query()->create([
        'name' => 'default-model',
        'alias' => 'Application default',
        'provider' => AiModelProvider::OpenAi,
        'api_key' => 'default-secret',
    ]);

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'customer_name' => 'Ada Lovelace',
                        'category' => [$option->id],
                    ], JSON_THROW_ON_ERROR),
                ],
            ]],
        ]),
    ]);
    Sanctum::actingAs($owner);

    $this->postJson("/api/projects/{$project->key}/test-inputs")
        ->assertOk()
        ->assertJsonPath('data.customer_name', 'Ada Lovelace')
        ->assertJsonPath('data.category.0', $option->id);

    Http::assertSent(function (Request $request) use ($nameInput): bool {
        return $request['model'] === 'default-model'
            && str_contains($request['messages'][1]['content'], $nameInput->description);
    });
});
