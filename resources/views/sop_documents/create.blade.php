@extends('layouts.app')

@section('title', 'Buat Dokumen SOP — ' . $type->label())

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('documents.index') }}" class="text-decoration-none text-muted">Dokumen SOP Aset</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Buat Dokumen</li>
@endsection

@php
    $data = old('data', []);
    $preselectedAssetIds    = $preselectedAssetIds ?? [];
    $preselectedLogIds      = $preselectedLogIds ?? [];
    $preselectedPeripheralIds = $preselectedPeripheralIds ?? [];
@endphp

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clipboard-check text-primary me-2"></i>Buat Dokumen SOP
        </h4>
        <p class="text-muted small mb-0 mt-1">Data diisi otomatis dari sistem, lengkapi bagian pelengkap lalu simpan.</p>
    </div>
    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

{{-- Pilih jenis dokumen --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light py-2">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-layers me-2"></i>1. Pilih Jenis Dokumen</h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach ($types as $t)
                <div class="col-md-3">
                    <a href="{{ route('documents.create', ['type' => $t->value]) }}"
                       class="btn w-100 text-start {{ $t === $type ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi {{ $t->icon() }} me-2"></i>{{ $t->label() }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger py-2 small">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Form isi dokumen --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-pencil-square me-2"></i>2. Isi Detail — {{ $type->label() }}
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('documents.store') }}" id="sopDocumentForm">
            @csrf
            <input type="hidden" name="document_type" value="{{ $type->value }}">

            @include('sop_documents.partials._form_' . $type->value)

            <hr>

            {{-- Field umum --}}
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal Dokumen</label>
                    <input type="date" name="document_date"
                           class="form-control {{ $errors->has('document_date') ? 'is-invalid' : '' }}"
                           value="{{ old('document_date', now()->toDateString()) }}">
                    @error('document_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3 mt-3">
                <label class="form-label fw-semibold">
                    Catatan Tambahan
                    @if ($type->value === 'permohonan_mutasi')
                        <span class="text-danger">* (Alasan Permohonan)</span>
                    @endif
                </label>
                <textarea name="notes" rows="3"
                          class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                          placeholder="{{ $type->value === 'permohonan_mutasi' ? 'Alasan diajukannya permohonan mutasi aset ini...' : 'Informasi tambahan dokumen...' }}">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            @if ($type->value === 'permohonan_mutasi')
            <div class="mb-3">
                <label class="form-label fw-semibold">Uraian / Alasan Mutasi <span class="text-danger">*</span></label>
                <textarea name="reason" rows="3"
                          class="form-control {{ $errors->has('reason') ? 'is-invalid' : '' }}"
                          placeholder="Uraikan alasan dan kebutuhan mutasi...">{{ old('reason') }}</textarea>
                @error('reason')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            @endif

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Simpan Dokumen
                </button>
                <a href="{{ route('documents.create', ['type' => $type->value]) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function refreshDynamicRows(container) {
        const rows = container.querySelectorAll('.dynamic-row');
        rows.forEach((row, i) => {
            const num = row.querySelector('.row-number');
            if (num) num.textContent = '#' + (i + 1);
            const btn = row.querySelector('.row-toggle');
            if (!btn) return;
            const isLast = i === rows.length - 1;
            if (isLast) {
                btn.dataset.action = 'add';
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-outline-primary');
                btn.title = 'Tambah baris';
                btn.innerHTML = '<i class="bi bi-plus-lg"></i>';
            } else {
                btn.dataset.action = 'remove';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-outline-danger');
                btn.title = 'Hapus baris';
                btn.innerHTML = '<i class="bi bi-dash-lg"></i>';
            }
        });
    }

    document.querySelectorAll('.dynamic-rows').forEach((container) => {
        container.addEventListener('click', (e) => {
            const btn = e.target.closest('.row-toggle');
            if (!btn) return;
            const row = btn.closest('.dynamic-row');
            if (!row) return;
            const template = container.querySelector('.row-template');

            if (btn.dataset.action === 'add') {
                if (!template) return;
                const content = template.content.cloneNode(true);
                const newRow = content.querySelector('.dynamic-row');
                container.appendChild(content);
                const newSelect = newRow.querySelector('select[data-searchable]');
                if (newSelect && window.initSearchableSelect) {
                    window.initSearchableSelect(newSelect);
                }
            } else {
                if (container.querySelectorAll('.dynamic-row').length <= 1) return;
                row.remove();
            }
            refreshDynamicRows(container);
        });
        refreshDynamicRows(container);
    });
})();
</script>
@endpush