<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_connections', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('team_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('provider_id')->constrained('api_providers')->cascadeOnDelete();
            $table->text('credentials')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_connections');
    }
};
