<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@garageflow.test'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        if (Customer::count() === 0) {
            Customer::factory(15)->create();
        }
    }
}
