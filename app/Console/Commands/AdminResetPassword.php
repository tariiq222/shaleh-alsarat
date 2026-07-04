<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminResetPassword extends Command
{
    protected $signature = 'admin:reset-password
                            {email? : Admin email (defaults to ADMIN_EMAIL env)}
                            {--password= : New password (will prompt if not provided)}';

    protected $description = 'Reset the admin user password from CLI. Used when password is forgotten.';

    public function handle(): int
    {
        $email = $this->argument('email') ?: env('ADMIN_EMAIL');

        if (! $email) {
            $this->error('Provide an email argument or set ADMIN_EMAIL in .env.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");

            return self::FAILURE;
        }

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Enter new password (min 8 chars)');
            $confirm = $this->secret('Confirm password');
            if ($password !== $confirm) {
                $this->error('Passwords do not match.');

                return self::FAILURE;
            }
        }

        $validator = Validator::make(['password' => $password], [
            'password' => ['string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('password'));

            return self::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password updated for {$user->email}");

        return self::SUCCESS;
    }
}