<?php

declare(strict_types=1);

namespace Modules\Auth\app\Models;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property-read int $id
 * @property-read string $platform_type
 * @property-read string $identity_type
 * @property-read CarbonImmutable $verified_at
 * @property-read int $user_id
 * @property-read User $user
 */
final class ApiSession extends BaseModel
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    protected $fillable = [
        'platform_type',
        'identity_type',
        'verified_at',
        'user_id',
    ];

    protected $casts = [
        'identity_type' => UserTypes::class,
        'verified_at' => 'datetime',
        'platform_type' => PlatformType::class,
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
