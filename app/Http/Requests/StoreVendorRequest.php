<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('vendor.create');
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'min:2', 'max:150', Rule::unique('vendors', 'name')],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'string', 'email', 'max:100'],
            'address'        => ['nullable', 'string', 'max:2000'],
            'description'    => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama vendor wajib diisi.',
            'name.min'      => 'Nama vendor minimal :min karakter.',
            'name.unique'   => 'Vendor sudah terdaftar.',
            'email.email'   => 'Format email tidak valid.',
        ];
    }
}
