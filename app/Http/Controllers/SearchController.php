<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\ServiceJobResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VehicleResource;
use App\Services\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return $this->sendSuccess([
                'customers' => [], 'vehicles' => [], 'jobs' => [], 'invoices' => [], 'mechanics' => [],
            ]);
        }

        $results = $this->searchService->search($term, $request->user());

        return $this->sendSuccess([
            'customers' => CustomerResource::collection($results['customers']),
            'vehicles' => VehicleResource::collection($results['vehicles']),
            'jobs' => ServiceJobResource::collection($results['jobs']),
            'invoices' => InvoiceResource::collection($results['invoices']),
            'mechanics' => UserResource::collection($results['mechanics']),
        ]);
    }
}
