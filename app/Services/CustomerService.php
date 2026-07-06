<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function paginated(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Customer::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
