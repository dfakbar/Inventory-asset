{{-- Partial: Form Tanda Terima Aset & Peripheral — dynamic rows (Aset + Peripheral) --}}
@php
    $assetValues     = old('asset_ids', $preselectedAssetIds ?? []);
    $peripheralValues = old('peripheral_ids', $preselectedPeripheralIds ?? []);
    if (empty($assetValues)) $assetValues = [''];
    if (empty($peripheralValues)) $peripheralValues = [''];
@endphp

<div class="row g-3">
    {{-- Section: Aset (dynamic rows) --}}
    <div class="col-12">
        <label class="form-label fw-semibold">Aset</label>
        <div class="dynamic-rows" id="ttAssetRows">
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

    {{-- Section: Peripheral (dynamic rows) --}}
    <div class="col-12">
        <label class="form-label fw-semibold">Peripheral</label>
        <div class="dynamic-rows" id="ttPeripheralRows">
            @foreach ($peripheralValues as $val)
                <div class="dynamic-row d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-dark border flex-shrink-0 row-number">#1</span>
                    <div class="flex-grow-1">
                        <select name="peripheral_ids[]" class="form-select" data-searchable>
                            <option value="">-- Pilih Peripheral --</option>
                            @foreach ($peripherals as $p)
                                <option value="{{ $p->id }}" {{ (string) $val === (string) $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
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
                        <select name="peripheral_ids[]" class="form-select" data-searchable>
                            <option value="">-- Pilih Peripheral --</option>
                            @foreach ($peripherals as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn row-toggle btn-outline-primary flex-shrink-0" title="Tambah baris">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </template>
        </div>
        @error('peripheral_ids')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('peripheral_ids.*')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <div class="form-text text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Minimal pilih satu <strong>Aset</strong> atau satu <strong>Peripheral</strong>.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Penerima (Pengguna / Karyawan) <span class="text-danger">*</span></label>
        <select name="recipient_employee_id" class="form-select {{ $errors->has('recipient_employee_id') ? 'is-invalid' : '' }}" data-searchable required>
            <option value="">-- Pilih Penerima --</option>
            @foreach ($employees as $e)
                <option value="{{ $e->id }}" {{ old('recipient_employee_id') == $e->id ? 'selected' : '' }}>
                    {{ $e->name }}
                    @if ($e->department)
                        ({{ $e->department }})
                    @endif
                </option>
            @endforeach
        </select>
        @error('recipient_employee_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Pemberi</label>
        <input type="text" name="data[giver_name]"
               class="form-control {{ $errors->has('data.giver_name') ? 'is-invalid' : '' }}"
               value="{{ old('data.giver_name', auth()->user()->name) }}"
               placeholder="Nama yang menyerahkan aset">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Lokasi Penempatan</label>
        <select name="data[location_id]" class="form-select {{ $errors->has('data.location_id') ? 'is-invalid' : '' }}" data-searchable>
            <option value="">-- Pilih Lokasi Penempatan --</option>
            @foreach ($locations as $loc)
                <option value="{{ $loc->id }}" {{ (string) old('data.location_id') === (string) $loc->id ? 'selected' : '' }}>
                    {{ $loc->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted small">Kosongkan jika mengikuti lokasi aset/peripheral yang dipilih.</div>
        @error('data.location_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Tujuan / Keperluan</label>
        <input type="text" name="data[purpose]"
               class="form-control {{ $errors->has('data.purpose') ? 'is-invalid' : '' }}"
               value="{{ old('data.purpose') }}"
               placeholder="Tujuan penggunaan aset, mis. untuk pekerjaan harian">
    </div>
</div>
