@extends('layouts.app')

@section('title', $document->document_number . ' — ' . $document->document_type->label())

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('documents.index') }}" class="text-decoration-none text-muted">Dokumen SOP Aset</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $document->document_number }}</li>
@endsection

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi {{ $document->document_type->icon() }} text-primary me-2"></i>{{ $document->document_type->label() }}
        </h4>
        <p class="font-monospace text-muted mb-0 fs-6">{{ $document->document_number }}</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('documents.print', $document) }}" target="_blank" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i>Cetak / Print
        </a>
        <a href="{{ route('documents.pdf', $document) }}" class="btn btn-outline-primary">
            <i class="bi bi-download me-1"></i>Unduh PDF
        </a>
        @can('document.delete')
        <button type="button"
                class="btn btn-outline-danger js-open-delete"
                data-delete-url="{{ route('documents.destroy', $document) }}"
                data-document-number="{{ $document->document_number }}"
                title="Hapus Dokumen">
            <i class="bi bi-trash me-1"></i>Hapus
        </button>
        @endcan
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

@include('sop_documents._show_content')

{{-- Modal Hapus --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold text-danger">
                    <i class="bi bi-trash3-fill me-2"></i>Hapus Dokumen
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    Yakin ingin menghapus dokumen
                    <span class="fw-bold font-monospace" id="deleteDocNumber"></span>?
                </p>
                <p class="small text-danger mb-0 mt-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div id="deleteModalError" class="alert alert-danger d-none mt-3 mb-0 small py-2"></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let currentDeleteUrl = '';

function openDeleteModal(url, number) {
    document.getElementById('deleteDocNumber').textContent = number;
    const errBox = document.getElementById('deleteModalError');
    errBox.classList.add('d-none');
    errBox.textContent = '';
    currentDeleteUrl = url;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
}

document.querySelectorAll('.js-open-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        openDeleteModal(this.dataset.deleteUrl, this.dataset.documentNumber);
    });
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!currentDeleteUrl) return;
    this.disabled = true;
    const errBox = document.getElementById('deleteModalError');
    errBox.classList.add('d-none');
    errBox.textContent = '';

    fetch(currentDeleteUrl, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (res.ok) {
            window.location.href = '{{ route('documents.index') }}';
        } else {
            this.disabled = false;
            errBox.textContent = data.error || 'Gagal menghapus dokumen. Silakan coba lagi.';
            errBox.classList.remove('d-none');
        }
    })
    .catch(() => {
        this.disabled = false;
        errBox.textContent = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
        errBox.classList.remove('d-none');
    });
});
</script>
@endpush
