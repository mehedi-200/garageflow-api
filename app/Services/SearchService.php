<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ServiceJob;
use App\Models\User;
use App\Models\Vehicle;

class SearchService
{
    private const LIMIT = 8;

    /**
     * Master search across every domain, grouped.
     */
    public function search(string $term, User $user): array
    {
        return [
            'customers' => Customer::where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })->limit(self::LIMIT)->get(),

            'vehicles' => Vehicle::with('customer')
                ->where(function ($q) use ($term) {
                    $q->where('registration_no', 'like', "%{$term}%")
                        ->orWhere('brand', 'like', "%{$term}%")
                        ->orWhere('model', 'like', "%{$term}%");
                })->limit(self::LIMIT)->get(),

            'jobs' => ServiceJob::with(['vehicle.customer', 'mechanic'])
                ->where(function ($q) use ($term) {
                    if (ctype_digit($term)) {
                        $q->where('id', (int) $term);
                    }

                    $q->orWhere('service_type', 'like', "%{$term}%")
                        ->orWhereHas('vehicle', fn ($v) => $v->where('registration_no', 'like', "%{$term}%"))
                        ->orWhereHas('vehicle.customer', fn ($c) => $c->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('mechanic', fn ($m) => $m->where('name', 'like', "%{$term}%"));
                })
                ->latest()
                ->limit(self::LIMIT)
                ->get(),

            'invoices' => Invoice::with('serviceJob.vehicle.customer')
                ->where(function ($q) use ($term) {
                    $q->where('invoice_no', 'like', "%{$term}%")
                        ->orWhereHas('serviceJob.vehicle.customer', fn ($c) => $c->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('serviceJob.vehicle', fn ($v) => $v->where('registration_no', 'like', "%{$term}%"));
                })
                ->latest()
                ->limit(self::LIMIT)
                ->get(),

            'mechanics' => User::where('role', 'mechanic')
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                })->limit(self::LIMIT)->get(),
        ];
    }
}
