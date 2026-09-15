<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('provider_connections');
        Schema::dropIfExists('api_providers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The legacy provider tables are intentionally not recreated.
    }
};
