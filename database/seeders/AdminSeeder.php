<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        $name = env('ADMIN_NAME', 'مدير الشاليه');

        if (! $email || ! $password) {
            $this->command->error('ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env');
            $this->command->warn('Example: ADMIN_EMAIL=admin@example.com ADMIN_PASSWORD=secret');

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Admin user ready: {$user->email} (id={$user->id})");

        if ($password === 'changeme' || $password === 'password') {
            $this->command->warn('⚠️  You are using a default password. CHANGE IT BEFORE GOING TO PRODUCTION!');
            $this->command->warn('   Run: php artisan admin:reset-password '.$email.' <new-strong-password>');
        }
    }
}