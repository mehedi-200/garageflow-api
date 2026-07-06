<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly InvoiceService $invoiceService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $invoices = $this->invoiceService->paginated(
            $request->only(['q', 'payment_status']),
            (int) $request->query('per_page', 10)
        );

        return $this->sendSuccess(InvoiceResource::collection($invoices)->response()->getData(true));
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return $this->sendSuccess(
            new InvoiceResource($invoice->load('serviceJob.vehicle.customer', 'serviceJob.mechanic', 'serviceJob.items'))
        );
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->updateLabor($invoice, (float) $request->validated('labor_cost'));

        return $this->sendSuccess(new InvoiceResource($invoice), 'Labor cost updated.');
    }

    public function pay(Invoice $invoice): JsonResponse
    {
        $result = $this->invoiceService->markPaid($invoice);

        if (! $result['ok']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendSuccess(new InvoiceResource($invoice->fresh()), $result['message']);
    }
}
