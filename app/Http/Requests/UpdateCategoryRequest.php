<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('category.edit');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('abbreviation')) {
            $this->merge([
                'abbreviation' => strtoupper(trim($this->input('abbreviation'))),
            ]);
        }
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name'         => ['required', 'string', 'min:2', 'max:100', Rule::unique('asset_categories', 'name')->ignore($category->id)],
            'abbreviation' => [
                'required',
                'string',
                'min:2',
                'max:10',
                Rule::unique('asset_categories', 'abbreviation')->ignore($category->id),
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
        ];
    }
}
