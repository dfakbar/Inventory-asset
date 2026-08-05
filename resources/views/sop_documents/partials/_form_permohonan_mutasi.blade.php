{{-- Partial: Form Permohonan Mutasi Aset — dynamic rows, usulan perubahan, tidak mengubah status aset --}}
@php
    $assetValues = old('asset_ids', $preselectedAssetIds ?? []);
    if (empty($assetValues)) $assetValues = [''];
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Aset <span class="text-danger">*</span></label>
        <div class="dynamic-rows" id="pmAssetRows">
            @foreach ($assetValues as $val)
                <div class="dynamic-row d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-dark border flex-shrink-0 row-number">#1</span>
                    <div class="flex-grow-1">
                        <select name="asset_ids[]" class="form-select" data-searchable>
                            <option value="">-- Pilih Aset --</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}" {{ (string) $val === (string) $a->id ? 'selected' : '' }}>
                                    {{ $a->asset_code }} — {{ $a->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn row-toggle btn-outline-primary flex-shrink-0" title="Tambah baris">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            @endforeach

            <template class="row-template">
                <div class="dynamic-row d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-dark border flex-shrink-0 row-number">#1</span>
                    <div class="flex-grow-1">
                        <select name="asset_ids[]" class="form-select" data-searchable>
                            <option value="">-- Pilih Aset --</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}">{{ $a->asset_code }} — {{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn row-toggle btn-outline-primary flex-shrink-0" title="Tambah baris">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </template>
        </div>
        @error('asset_ids')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('asset_ids.*')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Pemohon</label>
        <input type="text" name="data[requester_name]"
               class="form-control {{ $errors->has('data.requester_name') ? 'is-invalid' : '' }}"
               value="{{ old('data.requester_name', auth()->user()->name) }}"
               placeholder="Nama pengaju permohonan">
    </div>

    <div class="col-12">
        <h6 class="text-muted fw-semibold mt-2 mb-1">Usulan Perubahan Tujuan</h6>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Lokasi Tujuan</label>
        <select name="data[target_location_id]"
                class="form-select {{ $errors->has('data.target_location_id') ? 'is-invalid' : '' }}" data-searchable>
            <option value="">-- Lokasi Saat Ini / Tidak Berubah --</option>
            @foreach ($locations as $loc)
                <option value="{{ $loc->id }}" {{ old('data.target_location_id') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Pengguna / Karyawan Tujuan</label>
        <select name="data[target_employee_id]"
                class="form-select {{ $errors->has('data.target_employee_id') ? 'is-invalid' : '' }}" data-searchable>
            <option value="">-- Tetap / Tidak Berubah --</option>
            @foreach ($employees as $e)
                <option value="{{ $e->id }}" {{ old('data.target_employee_id') == $e->id ? 'selected' : '' }}>
                    {{ $e->name }}
                    @if ($e->department)
                        ({{ $e->department }})
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Status Tujuan</label>
        <select name="data[target_status]"
                class="form-select {{ $errors->has('data.target_status') ? 'is-invalid' : '' }}" data-searchable>
            <option value="">-- Tetap / Tidak Berubah --</option>
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}" {{ old('data.target_status') === $s->value ? 'selected' : '' }}>
                    {{ $s->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <div class="alert alert-info py-2 mb-0 small">
            <i class="bi bi-info-circle me-1"></i>
            Dokumen ini adalah <strong>usulan</strong> permohonan mutasi. Aset <strong>tidak</strong> berubah di sistem sampai mutasi disetujui dan dicatat secara terpisah.
        </div>
    </div>
</div>
