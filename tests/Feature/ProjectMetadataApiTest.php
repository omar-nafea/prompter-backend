<?php

declare(strict_types=1);

use Database\Seeders\AiCallTypeSeeder;
use Database\Seeders\AiResponseTypeSeeder;
use Database\Seeders\OutputLanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        AiCallTypeSeeder::class,
        AiResponseTypeSeeder::class,
        OutputLanguageSeeder::class,
    ]);
});

test('consumer app can set project metadata via X-Api-Key and X-Public-Key headers', function (): void {
    $user = User::factory()->create();

    $project = $user->projects()->create([
        'name' => 'Metadata Consumer Test',
        'expected_outcome' => 'Testing metadata API.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 500,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'secret-api-key-12345',
        'metadata' => [
            'initial_key' => 'initial_val',
        ],
    ]);

    // Call metadata API using project API credentials
    $response = $this->withHeaders([
        'X-Api-Key' => 'secret-api-key-12345',
        'X-Public-Key' => $project->key,
    ])->postJson("/api/projects/{$project->key}/metadata", [
        'metadata' => [
            'title' => 'SLR Study on Diabetes',
            'topic' => 'Type 2 Diabetes Interventions',
            'purpose' => 'Systematic Evidence Review',
            'review_type' => 'Systematic review',
            'disease_area' => 'Endocrinology',
            'research_idea' => 'Evaluate metformin versus lifestyle changes',
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.metadata.title', 'SLR Study on Diabetes')
        ->assertJsonPath('data.metadata.topic', 'Type 2 Diabetes Interventions')
        ->assertJsonPath('data.metadata.disease_area', 'Endocrinology');

    expect($project->fresh()->metadata)->toBe([
        'title' => 'SLR Study on Diabetes',
        'topic' => 'Type 2 Diabetes Interventions',
        'purpose' => 'Systematic Evidence Review',
        'review_type' => 'Systematic review',
        'disease_area' => 'Endocrinology',
        'research_idea' => 'Evaluate metformin versus lifestyle changes',
    ]);
});

test('logged in user can set project metadata with bearer token', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = $user->projects()->create([
        'name' => 'Bearer Meta Test',
        'expected_outcome' => 'Testing bearer metadata API.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 500,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'secret-key',
    ]);

    $response = $this->putJson("/api/projects/{$project->key}/metadata", [
        'metadata' => [
            'env' => 'production',
            'version' => '1.0.0',
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.metadata.env', 'production');

    expect($project->fresh()->metadata)->toBe([
        'env' => 'production',
        'version' => '1.0.0',
    ]);
});

test('unauthorized requests without api key or bearer token are rejected', function (): void {
    $user = User::factory()->create();

    $project = $user->projects()->create([
        'name' => 'Unauthorized Meta Test',
        'expected_outcome' => 'Testing unauthorized.',
        'ai_call_type_id' => AiCallType::query()->value('id'),
        'ai_response_type_id' => AiResponseType::query()->value('id'),
        'max_output_length' => 500,
        'output_format' => ProjectOutputFormat::Json,
        'api_key' => 'secret-key',
    ]);

    $this->postJson("/api/projects/{$project->key}/metadata", [
        'metadata' => ['key' => 'value'],
    ])->assertStatus(401);

    $this->withHeaders([
        'X-Api-Key' => 'wrong-key',
        'X-Public-Key' => $project->key,
    ])->postJson("/api/projects/{$project->key}/metadata", [
        'metadata' => ['key' => 'value'],
    ])->assertStatus(403);
});
