<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\VehicleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly VehicleService $vehicleService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $vehicles = $this->vehicleService->paginated(
            $request->query('q'),
            $request->query('customer_id') ? (int) $request->query('customer_id') : null,
            (int) $request->query('per_page', 10)
        );

        return $this->sendSuccess(VehicleResource::collection($vehicles)->response()->getData(true));
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleService->create($request->validated());

        return $this->sendSuccess(new VehicleResource($vehicle), 'Vehicle created successfully.', 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return $this->sendSuccess(new VehicleResource($vehicle->load('customer')));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $vehicle = $this->vehicleService->update($vehicle, $request->validated());

        return $this->sendSuccess(new VehicleResource($vehicle), 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->vehicleService->delete($vehicle);

        return $this->sendSuccess(null, 'Vehicle deleted successfully.');
    }
}
