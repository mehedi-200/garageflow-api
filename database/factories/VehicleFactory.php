<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = [
            'Toyota' => ['Corolla', 'Premio', 'Axio', 'Noah', 'Hiace'],
            'Honda' => ['Civic', 'City', 'Vezel', 'CR-V'],
            'Nissan' => ['Sunny', 'X-Trail', 'Note'],
            'Mitsubishi' => ['Lancer', 'Pajero', 'Outlander'],
            'Suzuki' => ['Swift', 'Alto', 'WagonR'],
        ];
        $brand = fake()->randomElement(array_keys($brands));

        return [
            'registration_no' => strtoupper(fake()->unique()->bothify('DHA-##-####')),
            'brand' => $brand,
            'model' => fake()->randomElement($brands[$brand]),
            'year' => fake()->numberBetween(2005, 2026),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
