<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServiceJob;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\InvoiceService;
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
        // Permissions (one per feature)
        foreach (Permission::FEATURES as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        // Default roles
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->permissions()->sync(
            Permission::whereIn('name', ['customers', 'vehicles', 'service_jobs', 'invoices'])->pluck('id')
        );

        $mechanic = Role::firstOrCreate(['name' => 'Mechanic']);
        $mechanic->permissions()->sync(
            Permission::whereIn('name', ['vehicles', 'service_jobs'])->pluck('id')
        );

        // Main super admin — is_admin bypasses all permission checks
        User::firstOrCreate(
            ['email' => 'admin@garageflow.test'],
            [
                'name' => 'Admin',
                'password' => 'password',
            ]
        )->update(['is_admin' => true, 'role_id' => null]);

        User::firstOrCreate(
            ['email' => 'jakir@garageflow.test'],
            ['name' => 'Jakir Mechanic', 'password' => 'password123']
        )->update(['role_id' => $mechanic->id, 'is_admin' => false]);

        User::firstOrCreate(
            ['email' => 'rafiq@garageflow.test'],
            ['name' => 'Rafiq Mechanic', 'password' => 'password123']
        )->update(['role_id' => $mechanic->id, 'is_admin' => false]);

        User::firstOrCreate(
            ['email' => 'manager@garageflow.test'],
            ['name' => 'Salma Manager', 'password' => 'password123']
        )->update(['role_id' => $manager->id, 'is_admin' => false]);

        if (Customer::count() === 0) {
            Customer::factory(15)->create();
        }

        if (Vehicle::count() === 0) {
            $customerIds = Customer::pluck('id');

            Vehicle::factory(25)
                ->state(fn () => ['customer_id' => $customerIds->random()])
                ->create();
        }

        if (ServiceJob::count() === 0) {
            $vehicleIds = Vehicle::pluck('id');
            $mechanicIds = User::where('role_id', $mechanic->id)->pluck('id');
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

        if (Invoice::count() === 0) {
            $invoiceService = app(InvoiceService::class);

            $billableJobs = ServiceJob::whereIn('status', [
                ServiceJob::STATUS_COMPLETED,
                ServiceJob::STATUS_DELIVERED,
            ])->get();

            foreach ($billableJobs as $job) {
                $invoice = $invoiceService->createForJob($job);
                $invoiceService->updateLabor($invoice, fake()->numberBetween(500, 5000));

                if (fake()->boolean(60)) {
                    $invoiceService->markPaid($invoice);
                }
            }
        }
    }
}
