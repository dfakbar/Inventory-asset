<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('employee.edit');
    }

    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'name'       => ['required', 'string', 'min:2', 'max:200'],
            'email'      => ['nullable', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($employee->id)],
            'phone'      => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'position'   => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.min'      => 'Nama pengguna minimal :min karakter.',
            'email.unique'  => 'Email sudah terdaftar.',
        ];
    }
}
