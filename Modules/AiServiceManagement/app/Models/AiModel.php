<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $alias
 * @property-read AiModelProvider $provider
 * @property-read string $api_key
 * @property-read string|null $connector_url
 */
final class AiModel extends Model
{
    protected $fillable = [
        'name',
        'alias',
        'provider',
        'api_key',
        'connector_url',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'provider' => AiModelProvider::class,
        'api_key' => 'encrypted',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * Last four characters of the decrypted key, for safe display.
     *
     * @return Attribute<string,never>
     */
    protected function apiKeyHint(): Attribute
    {
        return Attribute::get(function (): string {
            $key = $this->api_key ?? '';

            return $key === '' ? '' : '••••' . mb_substr($key, -4);
        });
    }
}
