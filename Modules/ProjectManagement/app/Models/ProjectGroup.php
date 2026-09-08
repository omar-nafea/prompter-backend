<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Models;

use App\Cache\Contracts\ShouldResetCache;
use App\Cache\Traits\HasResetCache;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Modules\Auth\app\Models\User;
use MohamedGaber\UniqueModelKeyGenerator\Traits\HasUniqueModelKey;

/**
 * @property-read int $id
 * @property-read string $key
 * @property-read string $name
 * @property-read string|null $description
 * @property-read string|null $instructions
 * @property-read int $user_id
 * @property-read User $owner
 * @property-read Collection<int, Project> $projects
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
final class ProjectGroup extends BaseModel implements ShouldResetCache
{
    use HasFactory;
    use HasResetCache;
    use HasUniqueModelKey;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'instructions',
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @return HasMany<Project>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_group_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param  Builder|EloquentBuilder<self>  $query
     */
    public function scopeAllowedForUser(Builder|EloquentBuilder $query, User $user): void
    {
        if ($user->role->isSuperAdmin()) {
            return;
        }

        $query->where('user_id', $user->id);
    }
}
