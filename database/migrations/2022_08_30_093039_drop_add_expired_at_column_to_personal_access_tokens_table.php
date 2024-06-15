<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        PersonalAccessToken::query()->chunk(1000, function ($tokensChunk): void {
            $tokensChunk->each(function ($token): void {
                $token->update(['expires_at' => $token->expired_at]);
            });
        });
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->dropColumn('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->timestamp('expired_at')->nullable()->after('last_used_at');
        });
        PersonalAccessToken::query()->chunk(1000, function ($tokensChunk): void {
            $tokensChunk->each(function ($token): void {
                $token->update(['expired_at' => $token->expires_at]);
            });
        });
    }
};
