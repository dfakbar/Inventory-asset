<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('category.create');
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:2', 'max:100', Rule::unique('asset_categories', 'name')],
            'abbreviation' => [
                'required',
                'string',
                'min:2',
                'max:10',
                Rule::unique('asset_categories', 'abbreviation'),
            ],
            'description'  => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama kategori wajib diisi.',
            'name.min'              => 'Nama kategori minimal :min karakter.',
            'name.unique'           => 'Nama kategori sudah digunakan.',
            'abbreviation.required' => 'Singkatan kategori wajib diisi.',
            'abbreviation.unique'   => 'Singkatan sudah digunakan oleh kategori lain.',
            'abbreviation.min'      => 'Singkatan minimal :min karakter.',
        ];
    }
}
