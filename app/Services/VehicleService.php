<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VehicleService
{
    public function paginated(?string $search, ?int $customerId, int $perPage = 10): LengthAwarePaginator
    {
        return Vehicle::query()
            ->with('customer')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('registration_no', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data)->load('customer');
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);

        return $vehicle->load('customer');
    }

    public function delete(Vehicle $vehicle): void
    {
        $vehicle->delete();
    }
}
