<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('employee.create');
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'min:2', 'max:200'],
            'email'      => ['nullable', 'email', 'max:150', Rule::unique('employees', 'email')],
            'phone'      => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100', Rule::in(Employee::DIVISIONS)],
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
