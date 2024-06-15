<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\OutputLanguageStatus;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('output_languages', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedTinyInteger('status')->default(OutputLanguageStatus::Enabled);
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
        Schema::dropIfExists('output_languages');
    }
};
