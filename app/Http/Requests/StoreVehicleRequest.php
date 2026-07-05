<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'registration_no' => ['required', 'string', 'max:30', 'unique:vehicles,registration_no'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'between:1950,'.(date('Y') + 1)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
