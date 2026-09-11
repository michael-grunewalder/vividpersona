<?php

use App\Models\TeamInvitation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('team_invitations', function (Blueprint $table) {
            $table->string('name')->nullable()->after('email');
            $table->string('token')->nullable()->unique()->after('role');
        });

        TeamInvitation::query()->whereNull('token')->get()->each(function (TeamInvitation $invitation) {
            $invitation->forceFill(['token' => (string) Str::ulid()])->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_invitations', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn(['name', 'token']);
        });
    }
};
