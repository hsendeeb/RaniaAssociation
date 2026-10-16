<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * ينشئ (أو يحدّث) حساب مشرف النظام الذي يمكنه الدخول إلى لوحة تحكم Filament
 * على المسار /admin.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@rania-association.test');

        $admin = User::firstOrNew(['email' => $email]);

        $admin->forceFill([
            'name' => env('ADMIN_NAME', 'مدير النظام'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'is_admin' => true,
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();
    }
}
