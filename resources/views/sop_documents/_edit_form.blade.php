@php
    $data = old('data', $document->data ?? []);
    $preselectedAssetIds       = $preselectedAssetIds ?? [];
    $preselectedLogIds         = $preselectedLogIds ?? [];
    $preselectedPeripheralIds  = $preselectedPeripheralIds ?? [];
@endphp

<div class="alert alert-light border small py-2 d-flex align-items-center gap-2">
    <i class="bi {{ $type->icon() }} text-primary"></i>
    <div>
        <span class="fw-semibold">{{ $type->label() }}</span>
        <span class="font-monospace text-muted ms-1">{{ $document->document_number }}</span>
        <div class="text-muted">Jenis dokumen dikunci — hanya isi/detail yang dapat diubah. Nomor dokumen tidak berubah.</div>
    </div>
</div>

<form method="POST" action="{{ route('documents.update', $document) }}" id="editSopDocumentForm">
    @csrf
    @method('PUT')

    @include('sop_documents.partials._form_' . $type->value)

    <hr>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Tanggal Dokumen</label>
            <input type="date" name="document_date"
                   class="form-control {{ $errors->has('document_date') ? 'is-invalid' : '' }}"
                   value="{{ old('document_date', $document->document_date?->format('Y-m-d')) }}">
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
                  placeholder="{{ $type->value === 'permohonan_mutasi' ? 'Alasan diajukannya permohonan mutasi aset ini...' : 'Informasi tambahan dokumen...' }}">{{ old('notes', $document->notes) }}</textarea>
        @error('notes')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    @if ($type->value === 'permohonan_mutasi')
    <div class="mb-3">
        <label class="form-label fw-semibold">Uraian / Alasan Mutasi <span class="text-danger">*</span></label>
        <textarea name="reason" rows="3"
                  class="form-control {{ $errors->has('reason') ? 'is-invalid' : '' }}"
                  placeholder="Uraikan alasan dan kebutuhan mutasi...">{{ old('reason', $document->reason) }}</textarea>
        @error('reason')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    @endif
</form>
