<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AiServiceManagement\app\DataObjects\AiCallRequestLog\AiCallStatusLogDataObject;
use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;
use Modules\AiServiceManagement\app\Observers\AiCallRequestLogObserver;
use Spatie\LaravelData\DataCollection;

#[ObservedBy(AiCallRequestLogObserver::class)]
final class AiCallRequestLog extends BaseModel
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------|
    |                              Arrays                                      |
    |--------------------------------------------------------------------------|
    */
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => AiCallRequestStatus::Started,
    ];

    protected $fillable = [
        'request_uuid',
        'project_id',
        'status',
        'request_body',
        'prompt',
        'response',
        'status_log',
        'integration_service',
        'ai_service_name',
        'ai_connector',
        'last_status_at',
    ];

    protected $casts = [
        'request_body' => 'encrypted:array',
        'prompt' => 'encrypted',
        'status' => AiCallRequestStatus::class,
        'response' => 'encrypted:array',
        'status_log' => DataCollection::class . ':' . AiCallStatusLogDataObject::class . ',default',
        'last_status_at' => 'datetime',
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
}
