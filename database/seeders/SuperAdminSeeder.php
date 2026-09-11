<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrNew(['email' => 'admin@vividpersona.test']);

        $admin->forceFill([
            'name' => 'System Admin',
            'password' => 'password',
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ])->save();
    }
}
