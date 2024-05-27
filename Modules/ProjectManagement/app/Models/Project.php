<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\AiServiceManagement\app\Models\AiService;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use MohamedGaber\UniqueModelKeyGenerator\Traits\HasUniqueModelKey;

class Project extends BaseModel
{
    use HasFactory;
    use HasUniqueModelKey;

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
        'output_format',
        'max_output_length',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $attributes = [
        'status' => 1,
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

    public function aiService(): BelongsTo
    {
        return $this->belongsTo(AiService::class);
    }

    public function aiCallType(): BelongsTo
    {
        return $this->belongsTo(AiCallType::class);
    }

    public function aiResponseType(): BelongsTo
    {
        return $this->belongsTo(AiResponseType::class);
    }

    public function outputLanguages()
    {
        return $this->belongsToMany(OutputLanguage::class, 'project_output_languages')
            ->using(ProjectOutputLanguage::class)
            ->withTimestamps();
    }
}
