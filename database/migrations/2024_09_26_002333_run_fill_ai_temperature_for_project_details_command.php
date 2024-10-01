<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Artisan::call('app:fill-ai-temperature-for-project-details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
