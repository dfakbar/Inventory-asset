<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use App\Enums\SopDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreSopDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('document.create');
    }

    public function rules(): array
    {
        $type = SopDocumentType::tryFrom($this->input('document_type'));

        $rules = [
            'document_type' => ['required', new Enum(SopDocumentType::class)],
            'document_date' => ['nullable', 'date'],
            'reason'        => ['nullable', 'string', 'max:3000'],
            'notes'         => ['nullable', 'string', 'max:3000'],
        ];

        if ($type === null) {
            return $rules;
        }

        if ($type->requiresAsset()) {
            $rules['asset_ids'] = ['required', 'array', 'min:1'];
            $rules['asset_ids.*'] = ['required', 'integer', 'exists:assets,id'];
        }

        if ($type === SopDocumentType::TandaTerima) {
            // Aset boleh kosong selama ada Peripheral (dan sebaliknya).
            // Baris kosong (null/'') diizinkan; kelengkapan minimal 1 Aset ATAU 1 Peripheral
            // dipastikan oleh withValidator().
            unset($rules['asset_ids'], $rules['asset_ids.*']);
            $rules['asset_ids'] = ['array'];
            $rules['asset_ids.*'] = ['nullable', 'integer', 'exists:assets,id'];
            $rules['peripheral_ids'] = ['array'];
            $rules['peripheral_ids.*'] = ['nullable', 'integer', 'exists:peripherals,id'];

            $rules['recipient_employee_id'] = [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('is_active', true),
            ];
            $rules['data.giver_name']  = ['nullable', 'string', 'max:200'];
            $rules['data.purpose']     = ['nullable', 'string', 'max:3000'];
            $rules['data.location_id'] = ['nullable', 'integer', 'exists:locations,id'];
        }

        if ($type === SopDocumentType::PermohonanMutasi) {
            $rules['data.requester_name'] = ['nullable', 'string', 'max:200'];
            $rules['data.target_location_id'] = ['nullable', 'integer', 'exists:locations,id'];
            $rules['data.target_employee_id'] = ['nullable', 'integer', Rule::exists('employees', 'id')->where('is_active', true)];
            $rules['data.target_status'] = ['nullable', new Enum(AssetStatus::class)];
            $rules['reason'] = ['required', 'string', 'max:3000'];
        }

        if ($type === SopDocumentType::BeritaAcara) {
            $rules['mutation_log_ids'] = ['required', 'array', 'min:1'];
            $rules['mutation_log_ids.*'] = ['integer', 'exists:asset_mutation_logs,id'];
            $rules['data.presenter']  = ['nullable', 'string', 'max:200'];
            $rules['data.witness']    = ['nullable', 'string', 'max:200'];
        }

        return $rules;
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        if ($this->input('document_type') !== SopDocumentType::TandaTerima->value) {
            return;
        }

        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $assetIds = array_values(array_filter($validator->getData()['asset_ids'] ?? [], fn ($v) => $v !== null && $v !== ''));
            $peripheralIds = array_values(array_filter($validator->getData()['peripheral_ids'] ?? [], fn ($v) => $v !== null && $v !== ''));

            if (empty($assetIds) && empty($peripheralIds)) {
                $validator->errors()->add('asset_ids', 'Minimal pilih satu Aset ATAU satu Peripheral.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'document_type.required'     => 'Jenis dokumen wajib dipilih.',
            'asset_id.required'          => 'Aset wajib dipilih.',
            'asset_id.exists'            => 'Aset yang dipilih tidak valid.',
            'asset_ids.required'         => 'Minimal satu aset wajib dipilih.',
            'asset_ids.array'            => 'Format aset tidak valid.',
            'asset_ids.min'              => 'Minimal satu aset wajib dipilih.',
            'asset_ids.*.required'       => 'Setiap baris aset wajib dipilih.',
            'asset_ids.*.exists'         => 'Salah satu aset yang dipilih tidak valid.',
            'peripheral_ids.array'       => 'Format peripheral tidak valid.',
            'peripheral_ids.*.required'  => 'Setiap baris peripheral wajib dipilih.',
            'peripheral_ids.*.exists'    => 'Salah satu peripheral yang dipilih tidak valid.',
            'mutation_log_ids.required'  => 'Data mutasi wajib dipilih.',
            'mutation_log_ids.array'     => 'Format data mutasi tidak valid.',
            'mutation_log_ids.min'       => 'Minimal satu data mutasi wajib dipilih.',
            'mutation_log_ids.*.exists'  => 'Salah satu data mutasi yang dipilih tidak valid.',
            'recipient_employee_id.required' => 'Penerima aset wajib dipilih.',
            'recipient_employee_id.exists'   => 'Penerima yang dipilih tidak valid.',
            'reason.required'            => 'Alasan / uraian permohonan wajib diisi.',
        ];
    }

    public function attributes(): array
    {
        return [
            'document_type'        => 'jenis dokumen',
            'asset_id'             => 'aset',
            'asset_ids'            => 'aset',
            'asset_ids.*'          => 'aset',
            'peripheral_ids'       => 'peripheral',
            'peripheral_ids.*'     => 'peripheral',
            'mutation_log_ids'     => 'data mutasi',
            'mutation_log_ids.*'   => 'data mutasi',
            'recipient_employee_id' => 'penerima',
            'document_date'        => 'tanggal dokumen',
            'reason'               => 'alasan',
            'notes'                => 'catatan',
        ];
    }
}