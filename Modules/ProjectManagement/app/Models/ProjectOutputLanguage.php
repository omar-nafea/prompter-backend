<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\ProjectManagement\App\Enums\OutputLanguageStatus;

class ProjectOutputLanguage extends Pivot
{
    use HasFactory;

    protected $table = 'project_output_languages';

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    protected $fillable = [
        'project_id',
        'output_language_id',
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
