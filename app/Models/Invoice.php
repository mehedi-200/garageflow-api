<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_job_id', 'invoice_no', 'labor_cost', 'parts_cost', 'total', 'payment_status', 'paid_at'])]
class Invoice extends Model
{
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class);
    }
}
