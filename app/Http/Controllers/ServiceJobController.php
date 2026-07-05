<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceJobRequest;
use App\Http\Requests\UpdateJobStatusRequest;
use App\Http\Requests\UpdateServiceJobRequest;
use App\Http\Resources\ServiceJobResource;
use App\Models\ServiceJob;
use App\Services\ServiceJobService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceJobController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ServiceJobService $serviceJobService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $jobs = $this->serviceJobService->paginated(
            $request->user(),
            $request->only(['q', 'status', 'mechanic_id', 'vehicle_id', 'customer_id']),
            (int) $request->query('per_page', 10)
        );

        return $this->sendSuccess(ServiceJobResource::collection($jobs)->response()->getData(true));
    }

    public function store(StoreServiceJobRequest $request): JsonResponse
    {
        $job = $this->serviceJobService->create($request->validated());

        return $this->sendSuccess(new ServiceJobResource($job), 'Service job created successfully.', 201);
    }

    public function show(Request $request, ServiceJob $serviceJob): JsonResponse
    {
        if ($request->user()->role === 'mechanic' && $serviceJob->mechanic_id !== $request->user()->id) {
            return $this->sendError('You can only view your own jobs.', 403);
        }

        return $this->sendSuccess(
            new ServiceJobResource($serviceJob->load(['vehicle.customer', 'mechanic', 'items']))
        );
    }

    public function update(UpdateServiceJobRequest $request, ServiceJob $serviceJob): JsonResponse
    {
        $job = $this->serviceJobService->update($serviceJob, $request->validated());

        return $this->sendSuccess(new ServiceJobResource($job), 'Service job updated successfully.');
    }

    public function updateStatus(UpdateJobStatusRequest $request, ServiceJob $serviceJob): JsonResponse
    {
        $result = $this->serviceJobService->changeStatus(
            $serviceJob,
            $request->validated('status'),
            $request->user()
        );

        if (! $result['ok']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendSuccess(
            new ServiceJobResource($serviceJob->fresh()->load(['vehicle.customer', 'mechanic', 'items'])),
            $result['message']
        );
    }
}
