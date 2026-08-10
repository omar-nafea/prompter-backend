<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('project_details', function (Blueprint $table): void {
            $table->text('system_prompt')->nullable()->after('ai_temperature');
            $table->unsignedInteger('max_output_tokens')->default(1024)->after('system_prompt');
            $table->json('response_schema')->nullable()->after('max_output_tokens');
        });

        Schema::table('project_inputs', function (Blueprint $table): void {
            $table->text('description')->nullable()->change();
        });

        Schema::table('project_outputs', function (Blueprint $table): void {
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_details', function (Blueprint $table): void {
            $table->dropColumn(['system_prompt', 'max_output_tokens', 'response_schema']);
        });

        Schema::table('project_inputs', function (Blueprint $table): void {
            $table->string('description')->nullable()->change();
        });

        Schema::table('project_outputs', function (Blueprint $table): void {
            $table->string('description')->nullable()->change();
        });
    }
};
