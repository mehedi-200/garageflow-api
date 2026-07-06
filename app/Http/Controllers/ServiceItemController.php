<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceItemRequest;
use App\Http\Resources\ServiceItemResource;
use App\Models\ServiceItem;
use App\Models\ServiceJob;
use App\Services\ServiceJobService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceItemController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ServiceJobService $serviceJobService)
    {
    }

    public function store(StoreServiceItemRequest $request, ServiceJob $serviceJob): JsonResponse
    {
        $result = $this->serviceJobService->addItem($serviceJob, $request->validated(), $request->user());

        if (! $result['ok']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendSuccess(new ServiceItemResource($result['item']), $result['message'], 201);
    }

    public function destroy(Request $request, ServiceJob $serviceJob, ServiceItem $item): JsonResponse
    {
        $result = $this->serviceJobService->removeItem($serviceJob, $item, $request->user());

        if (! $result['ok']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendSuccess(null, $result['message']);
    }
}
