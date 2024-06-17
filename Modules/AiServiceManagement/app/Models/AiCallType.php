<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Models;

use App\Models\BaseModel;
use Database\Factories\AiCallTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AiServiceManagement\app\Enums\AiCallType as AiCallTypeEnum;
use Modules\AiServiceManagement\app\Enums\AiCallTypeStatus;
/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $description
 * @property-read AiCallTypeEnum $type
 * @property-read AiCallTypeStatus $status
 */
final class AiCallType extends BaseModel
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
        'type',
        'status',
    ];

    protected $casts = [
        'type' => AiCallTypeEnum::class,
        'status' => AiCallTypeStatus::class,
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
        return AiCallTypeFactory::new();
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
