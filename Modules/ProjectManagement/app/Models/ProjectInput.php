<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ProjectManagement\app\Enums\DataType;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read DataType $data_type
 * @property-read bool $is_required
 * @property-read int|null $max_length
 * @property-read string $description
 * @property-read int $project_id
 * @property-read Project $project
 */
final class ProjectInput extends BaseModel
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    protected $fillable = [
        'name',
        'data_type',
        'is_required',
        'max_length',
        'description',
        'project_id',
        'created_by',
        'updated_by',
        'deleted_by',

    ];

    protected $casts = [
        'data_type' => DataType::class,
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

    public function enumValues(): HasMany
    {
        return $this->hasMany(ProjectInputEnumValue::class);
    }
}
