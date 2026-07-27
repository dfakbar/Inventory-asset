<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeripheralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('peripheral.edit');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:200'],
            'brand_id'    => ['nullable', 'integer', 'exists:brands,id'],
            'model'       => ['nullable', 'string', 'max:200'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'total_stock'   => ['required', 'integer', 'min:0', 'max:9999'],
            'current_stock' => ['required', 'integer', 'min:0', 'max:9999', 'lte:total_stock'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama peripheral wajib diisi.',
        ];
    }
}
