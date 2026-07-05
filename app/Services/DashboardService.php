<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ServiceJob;
use App\Models\Vehicle;

class DashboardService
{
    public function stats(): array
    {
        $monthStart = now()->startOfMonth();

        return [
            'total_customers' => Customer::count(),
            'total_vehicles' => Vehicle::count(),
            'vehicles_in_service' => ServiceJob::whereIn('status', [
                ServiceJob::STATUS_PENDING,
                ServiceJob::STATUS_IN_PROGRESS,
            ])->distinct('vehicle_id')->count('vehicle_id'),
            'completed_this_month' => ServiceJob::whereIn('status', [
                ServiceJob::STATUS_COMPLETED,
                ServiceJob::STATUS_DELIVERED,
            ])->where('updated_at', '>=', $monthStart)->count(),
            'revenue_this_month' => (float) Invoice::where('payment_status', Invoice::STATUS_PAID)
                ->where('paid_at', '>=', $monthStart)
                ->sum('total'),
            'jobs_by_status' => ServiceJob::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'recent_jobs' => ServiceJob::with(['vehicle.customer', 'mechanic'])
                ->latest()
                ->limit(6)
                ->get(),
        ];
    }
}
