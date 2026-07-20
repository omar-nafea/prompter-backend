<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ai_services');
    }

    public function down(): void
    {
        // Intentionally left empty: the legacy ai_services table is superseded
        // by ai_models and is not restored on rollback.
    }
};
