<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CustomerService $customerService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $customers = $this->customerService->paginated(
            $request->query('q'),
            (int) $request->query('per_page', 10)
        );

        return $this->sendSuccess(CustomerResource::collection($customers)->response()->getData(true));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        return $this->sendSuccess(new CustomerResource($customer), 'Customer created successfully.', 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return $this->sendSuccess(new CustomerResource($customer->load('vehicles')));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->customerService->update($customer, $request->validated());

        return $this->sendSuccess(new CustomerResource($customer), 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete($customer);

        return $this->sendSuccess(null, 'Customer deleted successfully.');
    }
}
