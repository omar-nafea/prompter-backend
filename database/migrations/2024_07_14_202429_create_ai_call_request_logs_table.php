<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;
use Modules\Auth\app\Models\User;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_call_request_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('request_uuid')->unique();
            $table->foreignId('project_id')->nullable();
            $table->unsignedTinyInteger('status')->default(AiCallRequestStatus::Started->value);
            $table->text('request_body')->nullable();
            $table->text('prompt')->nullable();
            $table->longText('response')->nullable();
            $table->text('status_log')->nullable();
            $table->string('integration_service')->nullable();
            $table->string('ai_service_name')->nullable();
            $table->string('ai_connector')->nullable();
            $table->dateTime('last_status_at')->nullable();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignIdFor(User::class, 'deleted_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_call_request_logs');
    }
};
