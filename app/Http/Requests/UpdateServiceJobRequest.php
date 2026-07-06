<?php

namespace App\Http\Requests;

use App\Models\ServiceJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'mechanic_id' => ['required', 'integer', 'exists:users,id'],
            'service_type' => ['required', Rule::in(ServiceJob::SERVICE_TYPES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'expected_delivery' => ['nullable', 'date'],
        ];
    }
}
