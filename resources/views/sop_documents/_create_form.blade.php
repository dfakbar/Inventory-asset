@php
    $data = old('data', []);
    $preselectedAssetIds    = $preselectedAssetIds ?? [];
    $preselectedLogIds      = $preselectedLogIds ?? [];
    $preselectedPeripheralIds = $preselectedPeripheralIds ?? [];
@endphp

<div class="mb-3">
    <label class="form-label fw-semibold">
        <i class="bi bi-layers me-1"></i>Jenis Dokumen
    </label>
    <select id="createDocTypeSelect"
            class="form-select"
            data-create-url="{{ route('documents.create') }}">
        @foreach ($types as $t)
            <option value="{{ $t->value }}" {{ $t === $type ? 'selected' : '' }}>
                {{ $t->label() }}
            </option>
        @endforeach
    </select>
</div>

<form method="POST" action="{{ route('documents.store') }}" id="createSopDocumentForm">
    @csrf
    <input type="hidden" name="document_type" value="{{ $type->value }}">

    @include('sop_documents.partials._form_' . $type->value)

    <hr>

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
</form>
