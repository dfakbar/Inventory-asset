<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('asset.create');
    }

    public function rules(): array
    {
        return [
            // --- Identitas ---
            'name'              => ['required', 'string', 'min:3', 'max:200'],

            // --- Relasi ---
            'asset_category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'location_id'       => ['nullable', 'integer', 'exists:locations,id'],
            'assigned_to'       => ['nullable', 'integer', 'exists:users,id'],
            'employee_id'       => ['nullable', 'integer', 'exists:employees,id'],

            // --- Spesifikasi ---
            'brand_id'          => ['nullable', 'integer', 'exists:brands,id'],
            'vendor_id'         => ['nullable', 'integer', 'exists:vendors,id'],
            'model'             => ['nullable', 'string', 'max:100'],
            'serial_number'     => [
                'nullable',
                'string',
                'max:150',
                Rule::unique('assets', 'serial_number')->whereNull('deleted_at'),
            ],
            'mac_address'       => ['nullable', 'string', 'max:17', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],

            // --- Finansial ---
            'purchase_date'     => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_price'    => ['nullable', 'numeric', 'min:0', 'max:99999999999.99'],
            'mutation_date'     => ['nullable', 'date'],

            // --- Inventori ---
            'quantity'          => ['required', 'integer', 'min:1', 'max:9999'],

            // --- Status & Tambahan ---
            'status'            => ['required', new Enum(AssetStatus::class)],
            'notes'             => ['nullable', 'string', 'max:3000'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'Nama aset wajib diisi.',
            'name.min'                   => 'Nama aset minimal :min karakter.',
            'asset_category_id.required' => 'Kategori aset wajib dipilih.',
            'asset_category_id.exists'   => 'Kategori yang dipilih tidak valid.',
            'assigned_to.exists'         => 'Pengguna yang dipilih tidak valid.',
            'serial_number.unique'       => 'Nomor seri sudah terdaftar pada aset lain.',
            'purchase_date.before_or_equal' => 'Tanggal pembelian tidak boleh melebihi hari ini.',
            'purchase_price.min'         => 'Harga pembelian tidak boleh bernilai negatif.',
            'purchase_price.max'         => 'Harga pembelian melebihi batas maksimum.',
            'quantity.required'          => 'Jumlah aset wajib diisi.',
            'quantity.min'               => 'Jumlah aset tidak boleh kurang dari 1.',
            'quantity.max'               => 'Jumlah aset tidak boleh melebihi 9.999 unit.',
            'status.required'            => 'Status aset wajib dipilih.',
            'image.image'                => 'File yang diunggah harus berupa gambar.',
            'image.mimes'                => 'Format gambar yang didukung: JPG, JPEG, PNG, WebP.',
            'image.max'                  => 'Ukuran gambar tidak boleh melebihi 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'              => 'nama aset',
            'asset_category_id' => 'kategori',
            'location_id' => 'lokasi',
            'assigned_to'       => 'pengguna',
            'brand_id'  => 'merek',
            'vendor_id' => 'vendor',
            'model'             => 'model',
            'serial_number'     => 'nomor seri',
            'mac_address'       => 'alamat MAC',
            'purchase_date'     => 'tanggal pembelian',
            'purchase_price'    => 'harga pembelian',
            'mutation_date'     => 'tanggal mutasi',
            'quantity'          => 'jumlah',
            'status'            => 'status',
            'notes'             => 'catatan',
            'image'             => 'foto aset',
        ];
    }
}
