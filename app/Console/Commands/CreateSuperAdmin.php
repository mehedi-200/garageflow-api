<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    protected $signature = 'garageflow:super-admin
                            {name : Full name}
                            {email : Login email}
                            {password : Password (min 8 chars)}';

    protected $description = 'Create the main super admin (is_admin = 1, bypasses all role permissions)';

    public function handle(): int
    {
        if (strlen($this->argument('password')) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        if (User::where('email', $this->argument('email'))->exists()) {
            $this->error('A user with this email already exists.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => $this->argument('password'),
            'is_admin' => true,
        ]);

        $this->info("Super admin created: {$user->email}");

        return self::SUCCESS;
    }
}
