<?php

declare(strict_types=1);

use Modules\ProjectManagement\app\Console\Commands\SyncSlrProjectsCommand;

function assertStrictSchemaCompliant(array $schema, string $path = 'root'): void
{
    $type = $schema['type'] ?? null;

    if ($type === 'object' || isset($schema['properties'])) {
        expect($schema['additionalProperties'] ?? null)->toBeFalse("{$path} must set additionalProperties: false");

        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];
        sort($required);
        $names = array_keys($properties);
        sort($names);
        expect($required)->toBe($names, "{$path} must require every property");

        foreach ($properties as $name => $property) {
            expect(is_array($property))->toBeTrue("{$path}.{$name} must be a schema array");
            assertStrictSchemaCompliant($property, "{$path}.{$name}");
        }

        return;
    }

    if ($type === 'array') {
        expect(isset($schema['items']) && is_array($schema['items']))->toBeTrue("{$path} must define items");
        assertStrictSchemaCompliant($schema['items'], "{$path}[]");

        return;
    }
}

function slrSpecs(): array
{
    $command = new SyncSlrProjectsCommand();
    $method = new ReflectionMethod(SyncSlrProjectsCommand::class, 'getSlrProjectsSpec');
    $method->setAccessible(true);

    return $method->invoke($command);
}

test('all seven SLR response schemas are strict-mode compliant', function (): void {
    $specs = slrSpecs();

    expect($specs)->toHaveCount(7);

    foreach ($specs as $spec) {
        expect($spec['response_schema']['type'] ?? null)->toBe('object', "{$spec['code']} root must be an object");
        assertStrictSchemaCompliant($spec['response_schema'], $spec['code']);
    }
});

test('strict schema normalizer closes objects, requires all properties, and itemizes arrays', function (): void {
    $method = new ReflectionMethod(SyncSlrProjectsCommand::class, 'toStrictSchema');
    $method->setAccessible(true);

    $normalized = $method->invoke(null, [
        'type' => 'object',
        'required' => ['kept'],
        'properties' => [
            'kept' => ['type' => 'string'],
            'added' => ['type' => ['string', 'null']],
            'bare_list' => ['type' => 'array'],
            'nested' => [
                'type' => 'object',
                'properties' => ['x' => ['type' => 'string']],
            ],
        ],
    ]);

    expect($normalized['additionalProperties'])->toBeFalse();
    expect($normalized['required'])->toEqualCanonicalizing(['kept', 'added', 'bare_list', 'nested']);
    expect($normalized['properties']['bare_list']['items'])->toBe(['type' => 'string']);
    expect($normalized['properties']['nested']['additionalProperties'])->toBeFalse();
    expect($normalized['properties']['nested']['required'])->toBe(['x']);
    // Nullable unions survive normalization untouched.
    expect($normalized['properties']['added'])->toBe(['type' => ['string', 'null']]);
});

test('P03 through P07 configure cumulative evidence inputs', function (): void {
    $namesByCode = [];
    foreach (slrSpecs() as $spec) {
        $namesByCode[$spec['code']] = array_column($spec['inputs'], 'name');
    }

    foreach (['P03', 'P04', 'P05', 'P06', 'P07'] as $code) {
        expect($namesByCode[$code])->toContain('source_versions_json');
        expect($namesByCode[$code])->toContain('reviewer_comments_json');
    }
});
