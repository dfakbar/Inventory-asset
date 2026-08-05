{{-- Partial: Berita Acara Mutasi Aset — dynamic rows, diambil dari log mutasi yang sudah terjadi --}}
@php
    $logValues = old('mutation_log_ids', $preselectedLogIds ?? []);
    if (empty($logValues)) $logValues = [''];
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Data Mutasi <span class="text-danger">*</span></label>
        <div class="dynamic-rows" id="baLogRows">
            @foreach ($logValues as $val)
                <div class="dynamic-row d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-dark border flex-shrink-0 row-number">#1</span>
                    <div class="flex-grow-1">
                        <select name="mutation_log_ids[]" class="form-select" data-searchable>
                            <option value="">-- Pilih Riwayat Mutasi --</option>
                            @foreach ($mutationLogs as $ml)
                                <option value="{{ $ml->id }}" {{ (string) $val === (string) $ml->id ? 'selected' : '' }}>
                                    {{ $ml->asset?->asset_code ?? '(aset terhapus)' }}
                                    {{ $ml->asset ? '— ' . $ml->asset->name : '' }}
                                    @if ($ml->mutation_date)
                                        ({{ $ml->mutation_date->format('d/m/Y') }})
                                    @endif
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
                        <select name="mutation_log_ids[]" class="form-select" data-searchable>
                            <option value="">-- Pilih Riwayat Mutasi --</option>
                            @foreach ($mutationLogs as $ml)
                                <option value="{{ $ml->id }}">
                                    {{ $ml->asset?->asset_code ?? '(aset terhapus)' }}
                                    {{ $ml->asset ? '— ' . $ml->asset->name : '' }}
                                    @if ($ml->mutation_date)
                                        ({{ $ml->mutation_date->format('d/m/Y') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn row-toggle btn-outline-primary flex-shrink-0" title="Tambah baris">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </template>
        </div>
        @error('mutation_log_ids')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('mutation_log_ids.*')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <div class="form-text text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Perbandingan lokasi, PIC, karyawan, dan status sebelum/sesudah akan diambil otomatis dari log mutasi terpilih.
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Nama Pelaksana / Pembuat BA</label>
        <input type="text" name="data[presenter]"
               class="form-control {{ $errors->has('data.presenter') ? 'is-invalid' : '' }}"
               value="{{ old('data.presenter', auth()->user()->name) }}"
               placeholder="Yang membuat/menyaksikan berita acara">
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Saksi</label>
        <input type="text" name="data[witness]"
               class="form-control {{ $errors->has('data.witness') ? 'is-invalid' : '' }}"
               value="{{ old('data.witness') }}"
               placeholder="Nama saksi">
    </div>
</div>
