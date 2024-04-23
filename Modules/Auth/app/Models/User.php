<?php

declare(strict_types=1);

namespace Modules\Auth\app\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\BaseAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Modules\Auth\app\Casts\EmailCast;
use Modules\Auth\app\Casts\PhoneCast;
use Modules\Auth\app\Enums\UserStatus;
use Modules\Auth\database\factories\UserFactory;
use MohamedGaber\SanctumRefreshToken\Traits\HasApiTokens;

class User extends BaseAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /*
   |--------------------------------------------------------------------------|
   |                              Arrays                                      |
   |--------------------------------------------------------------------------|
   */
    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'status',
        'phone',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'password' => 'hashed',
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
        'status' => UserStatus::class,
    ];

    /** @var array<string, string> */
    protected $attributes = [
        'status' => UserStatus::Active,
    ];

    /** @var array<int,string> */
    protected $hidden = [
        'password',
    ];

    /*
     |--------------------------------------------------------------------------|
     |                           Constants                                      |
     |--------------------------------------------------------------------------|
     */
    const MORPH_MAP = 1;
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

    public static function factory($count = null, $state = [])
    {
        return UserFactory::new();
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
