<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('slr sync exits nonzero when creates are blocked by a missing api key', function (): void {
    Http::fake(static function ($request) {
        $url = (string) $request->url();

        if (str_ends_with($url, '/ai-call-types')) {
            return Http::response(['data' => [['id' => 3, 'name' => 'One by one call']]], 200);
        }
        if (str_ends_with($url, '/ai-response-types')) {
            return Http::response(['data' => [['id' => 4, 'name' => 'Instant response']]], 200);
        }
        if (str_ends_with($url, '/project-output-languages')) {
            return Http::response(['data' => [['id' => 5, 'name' => 'English']]], 200);
        }
        if (str_ends_with($url, '/project-objective-questions')) {
            return Http::response(['data' => []], 200);
        }
        if (str_ends_with($url, '/projects')) {
            return Http::response(['data' => []], 200);
        }

        return Http::response(null, 404);
    });

    $exit = Artisan::call('slr:sync-projects', [
        '--base-url' => 'http://127.0.0.1:8003/api',
        '--token' => 'test-token',
    ]);

    // No OpenRouter key is configured, so every create is refused instead of
    // producing credential-less projects; the failure must surface as a
    // nonzero exit code for deployment scripts.
    expect($exit)->toBe(1);
    Http::assertNotSent(static fn ($request): bool => $request->method() === 'POST'
        && str_ends_with((string) $request->url(), '/projects'));
});
