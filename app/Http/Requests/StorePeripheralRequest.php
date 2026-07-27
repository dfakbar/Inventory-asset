<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeripheralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('peripheral.create');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:200'],
            'brand_id'    => ['nullable', 'integer', 'exists:brands,id'],
            'model'       => ['nullable', 'string', 'max:200'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'total_stock' => ['required', 'integer', 'min:1', 'max:9999'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Nama peripheral wajib diisi.',
            'total_stock.required' => 'Total stok wajib diisi.',
            'total_stock.min'  => 'Total stok minimal :min.',
        ];
    }
}
