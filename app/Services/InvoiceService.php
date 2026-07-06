<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\ServiceJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function paginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('serviceJob.vehicle.customer')
            ->when($filters['payment_status'] ?? null, fn ($q, $status) => $q->where('payment_status', $status))
            ->when($filters['q'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('serviceJob.vehicle.customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('serviceJob.vehicle', fn ($v) => $v->where('registration_no', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Auto-create the invoice when a job reaches "completed".
     * Totals are always computed server-side.
     */
    public function createForJob(ServiceJob $job): Invoice
    {
        if ($job->invoice) {
            return $job->invoice;
        }

        $partsCost = (float) $job->items()->sum('cost');

        return Invoice::create([
            'service_job_id' => $job->id,
            'invoice_no' => $this->nextInvoiceNumber(),
            'labor_cost' => 0,
            'parts_cost' => $partsCost,
            'total' => $partsCost,
            'payment_status' => Invoice::STATUS_UNPAID,
        ]);
    }

    public function updateLabor(Invoice $invoice, float $laborCost): Invoice
    {
        $invoice->labor_cost = $laborCost;
        $invoice->total = $laborCost + (float) $invoice->parts_cost;
        $invoice->save();

        return $invoice;
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function markPaid(Invoice $invoice): array
    {
        if ($invoice->payment_status === Invoice::STATUS_PAID) {
            return ['ok' => false, 'message' => 'This invoice is already paid.'];
        }

        $invoice->update([
            'payment_status' => Invoice::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->notificationService->notifyAdmins(
            "Invoice {$invoice->invoice_no} was paid (".number_format((float) $invoice->total).').',
            "/invoices/{$invoice->id}"
        );

        return ['ok' => true, 'message' => 'Invoice marked as paid.'];
    }

    private function nextInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $sequence = Invoice::where('invoice_no', 'like', "INV-{$year}-%")->lockForUpdate()->count() + 1;

            return sprintf('INV-%d-%04d', $year, $sequence);
        });
    }
}
