<?php

namespace Database\Factories;

use App\Models\ServiceJob;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceJob>
 */
class ServiceJobFactory extends Factory
{
    protected $model = ServiceJob::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'mechanic_id' => User::factory(),
            'service_type' => fake()->randomElement(ServiceJob::SERVICE_TYPES),
            'status' => ServiceJob::STATUS_PENDING,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
