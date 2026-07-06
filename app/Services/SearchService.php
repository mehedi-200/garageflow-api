<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ServiceJob;
use App\Models\User;
use App\Models\Vehicle;

class SearchService
{
    /**
     * Master search across customers, vehicles and jobs (grouped).
     */
    public function search(string $term, User $user): array
    {
        $customers = Customer::where('name', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->limit(5)
            ->get();

        $vehicles = Vehicle::with('customer')
            ->where('registration_no', 'like', "%{$term}%")
            ->limit(5)
            ->get();

        $jobs = ServiceJob::with(['vehicle.customer', 'mechanic'])
            ->where(function ($q) use ($term) {
                if (ctype_digit($term)) {
                    $q->where('id', (int) $term);
                }

                $q->orWhereHas('vehicle', fn ($v) => $v->where('registration_no', 'like', "%{$term}%"))
                    ->orWhereHas('vehicle.customer', fn ($c) => $c->where('name', 'like', "%{$term}%"));
            })
            ->latest()
            ->limit(5)
            ->get();

        return [
            'customers' => $customers,
            'vehicles' => $vehicles,
            'jobs' => $jobs,
        ];
    }
}
