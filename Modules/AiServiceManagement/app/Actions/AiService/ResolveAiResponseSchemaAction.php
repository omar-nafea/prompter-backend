<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use InvalidArgumentException;

final class ResolveAiResponseSchemaAction
{
    /**
     * @param  array<string, mixed>|null  $schema
     * @param  array<string, mixed>  $inputs
     * @return array<string, mixed>|null
     */
    public function execute(?array $schema, array $inputs): ?array
    {
        if ($schema === null) {
            return null;
        }

        /** @var array<string, mixed> $resolved */
        $resolved = $this->resolveValue($schema, $this->decodeJsonInputs($inputs));

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @return array<string, mixed>
     */
    private function decodeJsonInputs(array $inputs): array
    {
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $inputs[$key] = $decoded;
                }
            }
        }

        return $inputs;
    }

    /** @param array<string, mixed> $inputs */
    private function resolveValue(mixed $value, array $inputs): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->resolveValue($item, $inputs), $value);
        }

        if ( ! is_string($value) || preg_match('/^\{\{input\.(.+)}}$/', $value, $matches) !== 1) {
            return $value;
        }

        $resolved = data_get($inputs, $matches[1]);
        if ($resolved === null || is_array($resolved) || is_object($resolved)) {
            throw new InvalidArgumentException("Response schema placeholder [{$value}] did not resolve to a scalar input.");
        }

        return $resolved;
    }
}
