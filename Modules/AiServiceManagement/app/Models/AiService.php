<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Models;

use App\Models\BaseModel;
use Database\Factories\AiServiceFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AiServiceManagement\app\Enums\AiServiceModelsType;
use Modules\AiServiceManagement\app\Enums\AiServiceStatus;

/**
 * @property-read int $id,
 * @property-read string $name,
 * @property-read string $description,
 * @property-read int $price,
 * @property-read AiServiceStatus $status
 */
final class AiService extends BaseModel
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    protected $fillable = [
        'name',
        'description',
        'price',
        'max_tokens',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => AiServiceStatus::class,
    ];
    /*
     |--------------------------------------------------------------------------|
     |                           Constants                                      |
     |--------------------------------------------------------------------------|
     */

    /*
    |--------------------------------------------------------------------------|
    |                             Mutators                                     |
    |--------------------------------------------------------------------------|
    */

    /*
    |--------------------------------------------------------------------------|
    |                            Accessors                                     |
    |--------------------------------------------------------------------------|
    */
    /**
     * @return Attribute<int,never>
     */
    protected function maxCharacters(): Attribute
    {
        return Attribute::get(
            //            fn () => (int) ($this->max_tokens / config('ai-service-management.characters_per_token_divisor')),
            fn () => (int) ($this->max_tokens),
        );
    }

    /**
     * @return Attribute<bool,never>
     */
    protected function supportsConfigurableTemperature(): Attribute
    {
        return Attribute::get(
            fn () => in_array($this->id, AiServiceModelsType::supportsTemperatureIds())
        );
    }

    /**
     * @return Attribute<float,never>
     */
    protected function defaultTemperature(): Attribute
    {
        return Attribute::get(
            static fn () => (float) config('ai-service-management.default_temperature')
        );
    }

    /*
    |--------------------------------------------------------------------------|
    |                             Helpers                                      |
    |--------------------------------------------------------------------------|
    */
    protected static function newFactory(): AiServiceFactory
    {
        return AiServiceFactory::new();
    }
    /*
    |--------------------------------------------------------------------------|
    |                              Scopes                                      |
    |--------------------------------------------------------------------------|
   */

    /*
    |--------------------------------------------------------------------------|
    |                              Relations                                   |
    |--------------------------------------------------------------------------|
   */
}
