<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('location.edit');
    }

    public function rules(): array
    {
        /** @var \App\Models\Location $location */
        $location = $this->route('location');

        return [
            'name'        => ['required', 'string', 'min:3', 'max:150', Rule::unique('locations', 'name')->ignore($location->id)],
            'department'  => ['nullable', 'string', 'max:100'],
            'slug'        => [
                'nullable',
                'string',
                'max:200',
                'alpha_dash',
                // Abaikan record saat ini saat cek unique
                Rule::unique('locations', 'slug')->ignore($location->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Nama lokasi wajib diisi.',
            'name.min'        => 'Nama lokasi minimal :min karakter.',
            'name.unique'     => 'Nama lokasi sudah digunakan.',
            'slug.alpha_dash' => 'Slug hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'slug.unique'     => 'Slug sudah digunakan oleh lokasi lain.',
        ];
    }
}
