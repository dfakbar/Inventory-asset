<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class BulkUpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->can('asset.edit') || auth()->user()->can('asset.mutate'));
    }

    public function rules(): array
    {
        $canFullEdit = auth()->user()->can('asset.edit');

        $rules = [
            'ids'           => ['required', 'array', 'min:1', 'max:500'],
            'ids.*'         => ['integer', Rule::exists('assets', 'id')],
            'status'        => ['nullable', new Enum(AssetStatus::class)],
            'location_id'   => ['nullable', 'integer', 'exists:locations,id'],
            'employee_id'   => ['nullable', 'integer', Rule::exists('employees', 'id')->where('is_active', true)],
            'mutation_date' => ['nullable', 'date'],
            'notes'         => ['nullable', 'string', 'max:3000'],
        ];

        if ($canFullEdit) {
            $rules += [
                'asset_category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
                'brand_id'          => ['nullable', 'integer', 'exists:brands,id'],
                'vendor_id'         => ['nullable', 'integer', 'exists:vendors,id'],
                'model'             => ['nullable', 'string', 'max:100'],
                'purchase_date'     => ['nullable', 'date', 'before_or_equal:today'],
                'purchase_price'    => ['nullable', 'numeric', 'min:0', 'max:99999999999.99'],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'ids.required'        => 'Tidak ada aset yang dipilih.',
            'ids.min'             => 'Tidak ada aset yang dipilih.',
            'ids.*.exists'        => 'Salah satu aset yang dipilih tidak valid.',
            'employee_id.exists'  => 'Pengguna yang dipilih tidak valid.',
            'location_id.exists'  => 'Lokasi yang dipilih tidak valid.',
            'asset_category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'brand_id.exists'     => 'Merek yang dipilih tidak valid.',
            'vendor_id.exists'    => 'Vendor yang dipilih tidak valid.',
            'purchase_date.before_or_equal' => 'Tanggal pembelian tidak boleh melebihi hari ini.',
            'purchase_price.min'  => 'Harga pembelian tidak boleh bernilai negatif.',
        ];
    }
}
