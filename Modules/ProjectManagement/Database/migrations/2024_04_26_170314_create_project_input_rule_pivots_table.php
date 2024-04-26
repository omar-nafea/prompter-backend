<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_input_rule_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\ProjectManagement\app\Models\Project::class);
            $table->foreignIdFor(\Modules\ProjectManagement\app\Models\ProjectInputRule::class)->constrained()->restrictOnDelete();
            $table->json('params');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_input_rule_pivots');
    }
};
