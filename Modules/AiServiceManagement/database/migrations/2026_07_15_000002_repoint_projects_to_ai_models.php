<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            if (Schema::hasColumn('projects', 'ai_service_id')) {
                $table->dropForeign(['ai_service_id']);
                $table->dropColumn('ai_service_id');
            }
        });
    }

    public function down(): void {}
};
