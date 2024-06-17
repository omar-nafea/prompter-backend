<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProjectManagement\app\Enums\OutputLanguageStatus;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read OutputLanguageStatus $status
 */
final class OutputLanguage extends BaseModel
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    protected $attributes = [
        'status' => OutputLanguageStatus::Enabled,
    ];

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => OutputLanguageStatus::class,
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
    public function scopeEnabled(Builder $query): void
    {
        $query->where('status', OutputLanguageStatus::Enabled);
    }
    /*
    |--------------------------------------------------------------------------|
    |                              Relations                                   |
    |--------------------------------------------------------------------------|
   */
}
