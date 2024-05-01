<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends BaseModel
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    protected $fillable = [
        'name',
        'expected_outcome',
        'status',
        'ai_service_id',
        'ai_call_type_id',
        'ai_response_type_id',
        'user_id',
        'api_key',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $attributes = [
        'status' => 1,
    ];

    protected $casts = [
        'api_key' => 'encrypted',
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
    public function answers(): HasMany
    {
        return $this->hasMany(ProjectObjectiveAnswer::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ProjectOutput::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(ProjectInput::class);
    }
}
