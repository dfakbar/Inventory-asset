<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->can('asset.edit') || auth()->user()->can('asset.mutate'));
    }

    public function rules(): array
    {
        return [
            'action_type'      => ['required', 'in:addition,reduction'],
            'component_name'   => ['required', 'string', 'max:255'],
            'previous_spec'    => ['nullable', 'string', 'max:255'],
            'new_spec'         => ['required', 'string', 'max:255'],
            'cost'             => ['nullable', 'numeric', 'min:0'],
            'maintenance_date' => ['required', 'date'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
