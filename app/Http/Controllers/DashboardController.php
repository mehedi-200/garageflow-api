<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceJobResource;
use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(): JsonResponse
    {
        $stats = $this->dashboardService->stats();
        $stats['recent_jobs'] = ServiceJobResource::collection($stats['recent_jobs']);

        return $this->sendSuccess($stats);
    }
}
