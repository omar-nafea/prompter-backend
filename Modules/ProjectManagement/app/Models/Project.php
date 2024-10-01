<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Models;

use App\Cache\Contracts\ShouldResetCache;
use App\Cache\Traits\HasResetCache;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\AiServiceManagement\app\Models\AiService;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use MohamedGaber\UniqueModelKeyGenerator\Traits\HasUniqueModelKey;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $expected_outcome
 * @property-read string $description
 * @property-read string $ai_service_id
 * @property-read string $ai_call_type_id
 * @property-read string $ai_response_type_id
 * @property-read string $user_id
 * @property-read int $max_output_length
 * @property-read string $key
 * @property-read string $api_key
 * @property-read ProjectOutputFormat $output_format
 * @property-read int $status
 * @property-read AiResponseType $aiResponseType
 * @property-read User $user
 * @property-read AiCallType $aiCallType
 * @property-read AiService $aiService
 * @property-read int $created_by
 * @property-read int $updated_by
 * @property-read int $deleted_by
 * @property-read int $deleted_at
 * @property-read int $created_at
 * @property-read int $updated_at
 * @property-read Collection<int, ProjectOutput> $outputs
 * @property-read Collection<int, ProjectInput> $inputs
 * @property-read Collection<int, OutputLanguage> $outputLanguages
 * @property-read Collection<int, ProjectObjectiveAnswer> $answers
 */
final class Project extends BaseModel implements ShouldResetCache
{
    use HasFactory;
    use HasResetCache;
    use HasUniqueModelKey;

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    /**
     * @var array<string,mixed>
     */
    protected $attributes = [
        'status' => 1,
    ];

    protected $fillable = [
        'key',
        'name',
        'expected_outcome',
        'status',
        'ai_service_id',
        'ai_call_type_id',
        'ai_response_type_id',
        'user_id',
        'api_key',
        'output_format',
        'max_output_length',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'output_format' => ProjectOutputFormat::class,
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
    public function isOwner(): Attribute
    {
        return Attribute::get(fn () => $this->user_id === auth()->id());
    }

    public function maxTokens(): Attribute
    {
        return Attribute::get(fn () => (int) ceil($this->max_output_length / 4));
    }

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

    /**
     * @param  Builder|EloquentBuilder<self>  $query
     */
    public function scopeAllowedForUser(Builder|EloquentBuilder $query, User $user): void
    {
        $query->where(
            fn (Builder|EloquentBuilder $query) => $query
                ->where('user_id', $user->id)
                ->orWhereHas(
                    'moderators',
                    static fn (Builder|EloquentBuilder $query) => $query->where('project_moderators.user_id', $user->id)
                )
        );
    }

    /*
    |--------------------------------------------------------------------------|
    |                              Relations                                   |
    |--------------------------------------------------------------------------|
   */
    /**
     * @return HasMany<ProjectObjectiveAnswer>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ProjectObjectiveAnswer::class);
    }

    /**
     * @return HasMany<ProjectOutput>
     */
    public function outputs(): HasMany
    {
        return $this->hasMany(ProjectOutput::class);
    }

    /**
     * @return HasMany<ProjectInput>
     */
    public function inputs(): HasMany
    {
        return $this->hasMany(ProjectInput::class);
    }

    /**
     * @return BelongsTo<AiService,self>
     */
    public function aiService(): BelongsTo
    {
        return $this->belongsTo(AiService::class);
    }

    /**
     * @return BelongsTo<AiCallType,self>
     */
    public function aiCallType(): BelongsTo
    {
        return $this->belongsTo(AiCallType::class);
    }

    /**
     * @return BelongsTo<AiResponseType,self>
     */
    public function aiResponseType(): BelongsTo
    {
        return $this->belongsTo(AiResponseType::class);
    }

    /**
     * @return BelongsToMany<OutputLanguage>
     */
    public function outputLanguages(): BelongsToMany
    {
        return $this->belongsToMany(OutputLanguage::class, 'project_output_languages')
            ->using(ProjectOutputLanguage::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User>
     */
    public function moderators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_moderators')
            ->using(ProjectModerator::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsTo<User>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasOne<ProjectDetail>
     */
    public function details(): HasOne
    {
        return $this->hasOne(ProjectDetail::class);
    }
}
