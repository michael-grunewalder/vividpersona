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
        Schema::create('influencers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('team_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->json('niches')->nullable();
            $table->string('niche_custom')->nullable();
            $table->text('backstory')->nullable();
            $table->unsignedInteger('personality')->default(50);
            $table->string('ethnicity')->nullable();
            $table->string('skin_tone')->nullable();
            $table->string('hair_color')->nullable();
            $table->string('hair_length')->nullable();
            $table->string('hair_texture')->nullable();
            $table->string('eye_color')->nullable();
            $table->string('build')->nullable();
            $table->string('unique_features')->nullable();
            $table->json('vibe_words')->nullable();
            $table->string('clothing_style')->nullable();
            $table->text('physical_desc')->nullable();
            $table->string('aspect_ratio')->default('9:16');
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('pending');
            $table->string('main_image')->nullable();
            $table->text('prompt')->nullable();
            $table->json('generation_history')->nullable();
            $table->json('wardrobe_slots')->nullable();
            $table->string('face_ref_path')->nullable();
            $table->string('style_ref_path')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencers');
    }
};
