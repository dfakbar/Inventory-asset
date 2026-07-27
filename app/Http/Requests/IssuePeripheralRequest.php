<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssuePeripheralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('peripheral.issue');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:9999'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min'      => 'Minimal :1.',
        ];
    }
}
