<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'registration_no' => [
                'required',
                'string',
                'max:30',
                Rule::unique('vehicles', 'registration_no')->ignore($this->route('vehicle')),
            ],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'between:1950,'.(date('Y') + 1)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
