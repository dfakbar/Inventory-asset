<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === \App\Enums\UserRole::Admin;
    }

    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->route('user');

        return [
            'name'          => ['required', 'string', 'min:3', 'max:100'],
            'email'         => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password'      => ['nullable', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
            'role'          => ['required', new Enum(UserRole::class)],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Alamat email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah digunakan user lain.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
            'role.required'         => 'Role wajib dipilih.',
            'permissions.*.exists'  => 'Salah satu permission tidak valid.',
        ];
    }
}
