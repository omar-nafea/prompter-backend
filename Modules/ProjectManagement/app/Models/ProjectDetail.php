<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;
use Modules\Auth\app\Models\User;

final class ProjectDetail extends BaseModel
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    protected $fillable = [
        'has_exceeded_max_tokens',
        'ai_temperature',
        'system_prompt',
        'max_output_tokens',
        'response_schema',
        'project_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'ai_temperature' => 'float',
        'max_output_tokens' => 'integer',
        'response_schema' => 'array',
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

    /**
     * @param  Builder|\Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeAllowedForUser(Builder|\Illuminate\Database\Eloquent\Builder $query, User $user): void {}
    /*
    |--------------------------------------------------------------------------|
    |                              Relations                                   |
    |--------------------------------------------------------------------------|
   */
}
