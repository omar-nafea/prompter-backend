<?php

declare(strict_types=1);

namespace Modules\Auth\app\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\BaseAuthenticatable;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Modules\Auth\app\Casts\EmailCast;
use Modules\Auth\app\Casts\PhoneCast;
use Modules\Auth\app\Enums\UserStatus;
use Modules\Auth\database\factories\UserFactory;
use Modules\ProjectManagement\app\Models\Project;
use MohamedGaber\SanctumRefreshToken\Traits\HasApiTokens;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read Email $email
 * @property-read Phone $phone
 * @property-read UserStatus $status
 * @property-read Collection<int, Project> $projects
 *
 */
final class User extends BaseAuthenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

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
    public const MORPH_MAP = 1;
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

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
