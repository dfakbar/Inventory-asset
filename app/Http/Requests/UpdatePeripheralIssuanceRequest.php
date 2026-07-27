<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeripheralIssuanceRequest extends FormRequest
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
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'location_id.exists' => 'Lokasi tidak ditemukan.',
            'notes.max'         => 'Catatan maksimal 2000 karakter.',
        ];
    }
}
