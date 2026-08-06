{{--
    Partial: assets/_edit_form.blade.php
    Form edit aset untuk modal (di-load via AJAX dari assets.edit).
    Tombol submit berada di modal-footer dan menautkan ke form ini via atribut form="editAssetForm".
--}}
<form action="{{ route('assets.update', $asset) }}"
      method="POST"
      enctype="multipart/form-data"
      id="editAssetForm"
      novalidate>
    @csrf
    @method('PUT')

    {{-- Warning: kode aset tidak dapat diubah --}}
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
        <div>
            <strong>Kode Aset Terkunci</strong><br>
            <span class="small">Kode aset tidak dapat diubah untuk menjaga integritas histori dan pencatatan sistem.</span>
        </div>
    </div>

    {{-- Read-only: Kode Aset --}}
    <div class="mb-4">
        <label class="form-label fw-semibold text-muted">
            <i class="bi bi-upc-scan me-1"></i>Kode Aset
        </label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0">
                <i class="bi bi-lock-fill small"></i>
            </span>
            <input type="text"
                   class="form-control font-monospace bg-light text-muted border-start-0"
                   value="{{ $asset->asset_code }}"
                   disabled
                   aria-label="Kode Aset (tidak dapat diubah)">
        </div>
        <div class="form-text text-muted small">
            <i class="bi bi-shield-lock me-1"></i>Kode aset dikunci dan tidak dapat diubah.
        </div>
    </div>

    <hr class="mb-4">

    @include('assets._form', ['asset' => $asset])
</form>
