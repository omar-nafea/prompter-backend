<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\app\Enums\UserRole;
use Modules\Auth\app\Models\User;

uses(RefreshDatabase::class);

test('public registration is disabled', function (): void {
    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ])->assertNotFound();
});

test('super admin can list create users and change passwords', function (): void {
    $admin = User::factory()->superAdmin()->create();
    Sanctum::actingAs($admin);

    $this->getJson('/api/users')
        ->assertOk()
        ->assertJsonPath('data.users.0.id', $admin->id)
        ->assertJsonStructure(['data' => ['users', 'roles']]);

    $this->postJson('/api/users', [
        'name' => 'Service User',
        'email' => 'service@example.com',
        'password' => '123456',
        'password_confirmation' => '123456',
        'role' => UserRole::Service->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.email', 'service@example.com')
        ->assertJsonPath('data.role.value', UserRole::Service->value)
        ->assertJsonPath('data.email_verified', true)
        ->assertJsonMissing(['password' => '123456']);

    $created = User::query()->where('email', 'service@example.com')->firstOrFail();
    expect($created->email_verified_at)->not->toBeNull();

    $this->putJson("/api/users/{$created->id}/password", [
        'password' => 'abcdef',
        'password_confirmation' => 'abcdef',
    ])->assertOk();
});

test('non super admin cannot manage users', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/users')->assertForbidden();
    $this->postJson('/api/users', [
        'name' => 'Blocked',
        'email' => 'blocked@example.com',
        'password' => '123456',
        'password_confirmation' => '123456',
        'role' => UserRole::Tester->value,
    ])->assertForbidden();
});

test('user can be created without a phone', function (): void {
    Sanctum::actingAs(User::factory()->superAdmin()->create());

    $this->postJson('/api/users', [
        'name' => 'No Phone',
        'email' => 'nophone@example.com',
        'password' => '123456',
        'password_confirmation' => '123456',
        'role' => UserRole::Tester->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.phone', null);

    $this->assertDatabaseHas('users', [
        'email' => 'nophone@example.com',
        'phone' => null,
        'role' => UserRole::Tester->value,
    ]);
});
