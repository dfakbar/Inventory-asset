{{-- Partial: Form Registrasi Aset — dynamic rows, pilih aset yang akan di-registrasikan/dicetak ulang --}}
@php
    $assetValues = old('asset_ids', $preselectedAssetIds ?? []);
    if (empty($assetValues)) $assetValues = [''];
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Aset <span class="text-danger">*</span></label>
        <div class="dynamic-rows" id="regAssetRows">
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
        <div class="form-text text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Seluruh data aset (kode, kategori, merek, model, serial, MAC, lokasi, PIC, finansial) akan diambil otomatis dari sistem ke dalam PDF.
        </div>
    </div>
</div>
