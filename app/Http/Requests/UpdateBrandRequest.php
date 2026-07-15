<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('brand.edit');
    }

    public function rules(): array
    {
        $brand = $this->route('brand');

        return [
            'name'        => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('brands', 'name')->ignore($brand->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama merek wajib diisi.',
            'name.min'      => 'Nama merek minimal :min karakter.',
            'name.unique'   => 'Merek sudah terdaftar.',
        ];
    }
}
