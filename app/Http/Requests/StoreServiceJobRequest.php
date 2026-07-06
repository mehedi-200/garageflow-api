<?php

namespace App\Http\Requests;

use App\Models\ServiceJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'mechanic_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'mechanic'),
            ],
            'service_type' => ['required', Rule::in(ServiceJob::SERVICE_TYPES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'expected_delivery' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
