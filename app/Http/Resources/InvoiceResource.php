<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'labor_cost' => (float) $this->labor_cost,
            'parts_cost' => (float) $this->parts_cost,
            'total' => (float) $this->total,
            'payment_status' => $this->payment_status,
            'paid_at' => $this->paid_at?->toDateTimeString(),
            'job' => new ServiceJobResource($this->whenLoaded('serviceJob')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
