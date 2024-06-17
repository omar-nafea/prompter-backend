<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Models;

use App\Models\BaseModel;
use Database\Factories\AiServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

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

    /*
    |--------------------------------------------------------------------------|
    |                             Helpers                                      |
    |--------------------------------------------------------------------------|
    */
    protected static function newFactory()
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
