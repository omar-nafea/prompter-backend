<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ProjectManagement\app\Models\Project;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('ai_models', function (Blueprint $table): void {
            $table->foreignIdFor(Project::class)
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        $globalModel = DB::table('ai_models')->whereNull('project_id')->orderBy('id')->first();
        if ($globalModel !== null) {
            $projectIds = DB::table('projects')->pluck('id');
            foreach ($projectIds as $projectId) {
                DB::table('ai_models')->insert([
                    'project_id' => $projectId,
                    'name' => $globalModel->name,
                    'alias' => $globalModel->alias,
                    'provider' => $globalModel->provider,
                    'api_key' => $globalModel->api_key,
                    'connector_url' => $globalModel->connector_url ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('ai_models')->whereNull('project_id')->delete();
        }

        Schema::table('ai_models', function (Blueprint $table): void {
            $table->unique('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table): void {
            $table->dropUnique(['project_id']);
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
