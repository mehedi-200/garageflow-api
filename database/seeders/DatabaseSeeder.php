<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\ServiceJob;
use App\Models\User;
use App\Models\Vehicle;
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

        if (Vehicle::count() === 0) {
            $customerIds = Customer::pluck('id');

            Vehicle::factory(25)
                ->state(fn () => ['customer_id' => $customerIds->random()])
                ->create();
        }

        User::firstOrCreate(
            ['email' => 'rafiq@garageflow.test'],
            ['name' => 'Rafiq Mechanic', 'password' => 'password123', 'role' => 'mechanic']
        );

        if (ServiceJob::count() === 0) {
            $vehicleIds = Vehicle::pluck('id');
            $mechanicIds = User::where('role', 'mechanic')->pluck('id');
            $statuses = [
                ServiceJob::STATUS_PENDING,
                ServiceJob::STATUS_IN_PROGRESS,
                ServiceJob::STATUS_COMPLETED,
                ServiceJob::STATUS_DELIVERED,
                ServiceJob::STATUS_CANCELLED,
            ];
            $parts = ['Brake pads', 'Engine oil', 'Oil filter', 'Air filter', 'Spark plugs', 'Coolant', 'Battery', 'Wiper blades', 'Labor extras'];

            for ($i = 0; $i < 40; $i++) {
                $status = fake()->randomElement($statuses);

                $job = ServiceJob::create([
                    'vehicle_id' => $vehicleIds->random(),
                    'mechanic_id' => $mechanicIds->random(),
                    'service_type' => fake()->randomElement(ServiceJob::SERVICE_TYPES),
                    'status' => $status,
                    'description' => fake()->optional(0.7)->sentence(10),
                    'expected_delivery' => fake()->optional(0.8)->dateTimeBetween('-1 month', '+2 weeks'),
                    'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
                ]);

                if ($status !== ServiceJob::STATUS_PENDING && $status !== ServiceJob::STATUS_CANCELLED) {
                    foreach (fake()->randomElements($parts, fake()->numberBetween(1, 4)) as $part) {
                        $job->items()->create([
                            'name' => $part,
                            'cost' => fake()->numberBetween(300, 8000),
                        ]);
                    }
                }
            }
        }
    }
}
