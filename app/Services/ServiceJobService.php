<?php

namespace App\Services;

use App\Models\ServiceItem;
use App\Models\ServiceJob;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceJobService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * The enforced status workflow. A job may only move one step forward:
     * pending -> in_progress -> completed -> delivered.
     */
    private const TRANSITIONS = [
        ServiceJob::STATUS_PENDING => [ServiceJob::STATUS_IN_PROGRESS],
        ServiceJob::STATUS_IN_PROGRESS => [ServiceJob::STATUS_COMPLETED],
        ServiceJob::STATUS_COMPLETED => [ServiceJob::STATUS_DELIVERED],
        ServiceJob::STATUS_DELIVERED => [],
        ServiceJob::STATUS_CANCELLED => [],
    ];

    public function paginated(User $user, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return ServiceJob::query()
            ->with(['vehicle.customer', 'mechanic'])
            // Mechanics only ever see their own jobs.
            ->when($user->role === 'mechanic', fn ($q) => $q->where('mechanic_id', $user->id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['mechanic_id'] ?? null, fn ($q, $id) => $q->where('mechanic_id', $id))
            ->when($filters['vehicle_id'] ?? null, fn ($q, $id) => $q->where('vehicle_id', $id))
            ->when($filters['customer_id'] ?? null, function ($q, $id) {
                $q->whereHas('vehicle', fn ($v) => $v->where('customer_id', $id));
            })
            ->when($filters['q'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->whereHas('vehicle', fn ($v) => $v->where('registration_no', 'like', "%{$search}%"))
                        ->orWhereHas('vehicle.customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): ServiceJob
    {
        $data['status'] = ServiceJob::STATUS_PENDING;

        $job = ServiceJob::create($data)->load(['vehicle.customer', 'mechanic']);

        $this->notificationService->notifyUser(
            $job->mechanic_id,
            "New job #{$job->id} ({$job->service_type}) assigned to you — {$job->vehicle->registration_no}.",
            "/jobs/{$job->id}"
        );

        return $job;
    }

    public function update(ServiceJob $job, array $data): ServiceJob
    {
        $job->update($data);

        return $job->load(['vehicle.customer', 'mechanic', 'items']);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function changeStatus(ServiceJob $job, string $newStatus, User $user): array
    {
        if ($user->role === 'mechanic' && $job->mechanic_id !== $user->id) {
            return ['ok' => false, 'message' => 'You can only update your own jobs.'];
        }

        if ($newStatus === ServiceJob::STATUS_CANCELLED) {
            if ($user->role !== 'admin') {
                return ['ok' => false, 'message' => 'Only an admin can cancel a job.'];
            }

            if ($job->status === ServiceJob::STATUS_DELIVERED) {
                return ['ok' => false, 'message' => 'A delivered job cannot be cancelled.'];
            }

            $job->update(['status' => ServiceJob::STATUS_CANCELLED]);

            return ['ok' => true, 'message' => 'Job cancelled.'];
        }

        if (! in_array($newStatus, self::TRANSITIONS[$job->status] ?? [], true)) {
            return [
                'ok' => false,
                'message' => "Invalid status transition: {$job->status} → {$newStatus}.",
            ];
        }

        $job->update(['status' => $newStatus]);

        $this->notificationService->notifyAdmins(
            "Job #{$job->id} moved to ".str_replace('_', ' ', $newStatus).'.',
            "/jobs/{$job->id}",
            $user->id
        );

        if ($newStatus === ServiceJob::STATUS_COMPLETED) {
            $this->invoiceService->createForJob($job);

            return ['ok' => true, 'message' => 'Job completed — invoice created.'];
        }

        return ['ok' => true, 'message' => 'Job status updated.'];
    }

    /**
     * @return array{ok: bool, message: string, item?: ServiceItem}
     */
    public function addItem(ServiceJob $job, array $data, User $user): array
    {
        if ($user->role === 'mechanic' && $job->mechanic_id !== $user->id) {
            return ['ok' => false, 'message' => 'You can only update your own jobs.'];
        }

        if ($job->status !== ServiceJob::STATUS_IN_PROGRESS) {
            return ['ok' => false, 'message' => 'Items can only be added while the job is in progress.'];
        }

        $item = $job->items()->create($data);

        return ['ok' => true, 'message' => 'Item added.', 'item' => $item];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function removeItem(ServiceJob $job, ServiceItem $item, User $user): array
    {
        if ($user->role === 'mechanic' && $job->mechanic_id !== $user->id) {
            return ['ok' => false, 'message' => 'You can only update your own jobs.'];
        }

        if ($job->status !== ServiceJob::STATUS_IN_PROGRESS) {
            return ['ok' => false, 'message' => 'Items can only be removed while the job is in progress.'];
        }

        if ($item->service_job_id !== $job->id) {
            return ['ok' => false, 'message' => 'This item does not belong to the job.'];
        }

        $item->delete();

        return ['ok' => true, 'message' => 'Item removed.'];
    }
}
