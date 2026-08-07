@extends('layouts.app')

@section('title', 'Dokumen SOP Aset')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dokumen SOP Aset</li>
@endsection

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-clipboard-check text-primary me-2"></i>Dokumen SOP Aset
        </h4>
        <p class="text-muted small mb-0">Arsip form registrasi, tanda terima, permohonan mutasi, dan berita acara mutasi aset.</p>
    </div>
    @can('document.create')
    <button type="button" class="btn btn-primary js-open-create" data-create-url="{{ route('documents.create') }}">
        <i class="bi bi-plus-lg me-1"></i>Buat Dokumen
    </button>
    @endcan
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <form method="GET" action="{{ route('documents.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="type" class="form-select form-select-sm text-dark">
                    <option value="">— Semua Jenis —</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>
                            {{ $t->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari nomor/aset/penerima..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-light btn-sm"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-light btn-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nomor Dokumen</th>
                        <th>Jenis</th>
                        <th>Aset</th>
                        <th>Penerima</th>
                        <th>Tanggal</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr>
                            <td class="text-muted small">{{ $loop->iteration + ($documents->currentPage() - 1) * $documents->perPage() }}</td>
                            <td class="small font-monospace fw-semibold">{{ $doc->document_number }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi {{ $doc->document_type->icon() }} me-1"></i>{{ $doc->document_type->label() }}
                                </span>
                            </td>
                            <td class="small">
                                @if ($doc->asset)
                                    <a href="{{ route('assets.show', $doc->asset_id) }}" class="text-decoration-none">
                                        {{ $doc->asset->asset_code }}
                                    </a>
                                    <div class="text-muted small">{{ $doc->asset->name }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $doc->recipientEmployee?->name ?? '—' }}</td>
                            <td class="small text-nowrap">
                                {{ $doc->document_date ? $doc->document_date->format('d/m/Y') : '—' }}
                            </td>
                            <td class="small">{{ $doc->createdBy?->name ?? 'System' }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary js-open-detail"
                                            data-detail-url="{{ route('documents.show', $doc) }}"
                                            data-pdf-url="{{ route('documents.pdf', $doc) }}"
                                            data-print-url="{{ route('documents.print', $doc) }}"
                                            data-delete-url="{{ route('documents.destroy', $doc) }}"
                                            data-document-number="{{ $doc->document_number }}"
                                            data-document-type="{{ $doc->document_type->label() }}"
                                            title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('documents.pdf', $doc) }}" class="btn btn-sm btn-outline-success" title="Unduh PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @can('document.delete')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger js-open-delete"
                                            data-delete-url="{{ route('documents.destroy', $doc) }}"
                                            data-document-number="{{ $doc->document_number }}"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada dokumen SOP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-center py-3">
        {{ $documents->links() }}
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-clipboard-check me-2 text-primary"></i>Detail Dokumen
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody"></div>
            <div class="modal-footer py-2 flex-wrap">
                <a href="#" id="detailModalPdfBtn" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-download me-1"></i>Unduh PDF
                </a>
                <a href="#" id="detailModalPrintBtn" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                    <i class="bi bi-printer me-1"></i>Cetak / Print
                </a>
                @can('document.delete')
                <button type="button" class="btn btn-sm btn-outline-danger js-open-delete-modal"
                        id="detailModalDeleteBtn"
                        title="Hapus Dokumen">
                    <i class="bi bi-trash me-1"></i>Hapus
                </button>
                @endcan
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Buat Dokumen SOP
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createModalBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Batal
                </button>
                <button type="submit" form="createSopDocumentForm" class="btn btn-sm btn-primary" id="createModalSubmit">
                    <i class="bi bi-check-lg me-1"></i>Simpan Dokumen
                </button>
            </div>
        </div>
    </div>
</div>

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

function modalGet(url) {
    return fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    });
}

function setModalLoading(body) {
    body.innerHTML = '<div class="text-center py-5 text-muted">' +
        '<div class="spinner-border text-primary mb-2" role="status"></div>' +
        '<div class="small">Memuat...</div></div>';
}

function initSearchableWithin(container) {
    container.querySelectorAll('select[data-searchable]').forEach(el => {
        if (window.initSearchableSelect) window.initSearchableSelect(el);
    });
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function clearFormErrors(form) {
    form.querySelectorAll('.alert.alert-danger').forEach(a => a.remove());
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

function showFormAlert(form, message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show small py-2 mb-4';
    alert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>' + escapeHtml(message);
    form.prepend(alert);
}

function renderFormErrors(form, errors) {
    const errList = Object.values(errors).flat();
    if (errList.length) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show small py-2 mb-4';
        alert.innerHTML = '<strong>Terdapat ' + errList.length + ' kesalahan pada formulir:</strong><ul class="mb-0 mt-1 ps-3">' +
            errList.map(m => '<li>' + escapeHtml(m) + '</li>').join('') + '</ul>';
        form.prepend(alert);
    }

    Object.keys(errors).forEach(key => {
        const field = form.elements[key];
        if (!field) return;
        field.classList.add('is-invalid');
        const wrapper = field.closest('.searchable-wrapper');
        wrapper?.querySelector('.searchable-input')?.classList.add('is-invalid');

        let fb = field.nextElementSibling;
        if (!fb || !fb.classList.contains('invalid-feedback')) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback d-block';
            field.parentNode.insertBefore(fb, field.nextSibling);
        }
        fb.textContent = errors[key][0];
    });
}

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

function initDynamicRows(container) {
    container.querySelectorAll('.dynamic-rows').forEach(rowsEl => {
        rowsEl.addEventListener('click', (e) => {
            const btn = e.target.closest('.row-toggle');
            if (!btn) return;
            const row = btn.closest('.dynamic-row');
            if (!row) return;
            const template = rowsEl.querySelector('.row-template');

            if (btn.dataset.action === 'add') {
                if (!template) return;
                const content = template.content.cloneNode(true);
                const newRow = content.querySelector('.dynamic-row');
                rowsEl.appendChild(content);
                const newSelect = newRow.querySelector('select[data-searchable]');
                if (newSelect && window.initSearchableSelect) {
                    window.initSearchableSelect(newSelect);
                }
            } else {
                if (rowsEl.querySelectorAll('.dynamic-row').length <= 1) return;
                row.remove();
            }
            refreshDynamicRows(rowsEl);
        });
        refreshDynamicRows(rowsEl);
    });
}

function bindSopDocumentForm(form, modalId) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearFormErrors(form);
        const submitBtn = document.getElementById(modalId + 'ModalSubmit');
        const fd = new FormData(form);
        if (submitBtn) submitBtn.disabled = true;

        fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: fd
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                location.reload();
            } else if (data.errors) {
                if (submitBtn) submitBtn.disabled = false;
                renderFormErrors(form, data.errors);
            } else if (data.error) {
                if (submitBtn) submitBtn.disabled = false;
                showFormAlert(form, data.error);
            } else {
                if (submitBtn) submitBtn.disabled = false;
                showFormAlert(form, 'Terjadi kesalahan. Silakan coba lagi.');
            }
        })
        .catch(() => {
            if (submitBtn) submitBtn.disabled = false;
            showFormAlert(form, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        });
    });
}

function openDetailModal(btn) {
    const body = document.getElementById('detailModalBody');
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal'));

    document.getElementById('detailModalPdfBtn').href = btn.dataset.pdfUrl;
    const printBtn = document.getElementById('detailModalPrintBtn');
    printBtn.href = btn.dataset.printUrl;
    const deleteBtn = document.getElementById('detailModalDeleteBtn');
    if (deleteBtn) {
        deleteBtn.dataset.deleteUrl = btn.dataset.deleteUrl;
        deleteBtn.dataset.documentNumber = btn.dataset.documentNumber;
    }
    document.getElementById('detailModal').querySelector('.modal-title').innerHTML =
        '<i class="bi bi-clipboard-check me-2 text-primary"></i>Detail Dokumen — ' +
        escapeHtml(btn.dataset.documentType || '') + ' <span class="font-monospace">' + escapeHtml(btn.dataset.documentNumber || '') + '</span>';

    setModalLoading(body);
    modal.show();
    modalGet(btn.dataset.detailUrl)
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat detail.');
            return res.text();
        })
        .then(html => {
            body.innerHTML = html;
        })
        .catch(() => {
            body.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>' +
                '<span class="fw-medium">Gagal memuat detail dokumen.</span></div>';
        });
}

function initCreateForm(body) {
    initSearchableWithin(body);
    initDynamicRows(body);
    const form = body.querySelector('#createSopDocumentForm');
    if (form) bindSopDocumentForm(form, 'create');

    const typeSelect = body.querySelector('#createDocTypeSelect');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const baseUrl = this.dataset.createUrl;
            const newType = this.value;
            setModalLoading(body);
            modalGet(baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'type=' + encodeURIComponent(newType))
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memuat form.');
                    return res.text();
                })
                .then(html => {
                    body.innerHTML = html;
                    initCreateForm(body);
                })
                .catch(() => {
                    body.innerHTML = '<div class="text-center py-5 text-muted">' +
                        '<i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>' +
                        '<span class="fw-medium">Gagal memuat form dokumen.</span></div>';
                });
        });
    }
}

function openCreateModal(url) {
    const body = document.getElementById('createModalBody');
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('createModal'));
    setModalLoading(body);
    modal.show();
    modalGet(url)
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat form.');
            return res.text();
        })
        .then(html => {
            body.innerHTML = html;
            initCreateForm(body);
        })
        .catch(() => {
            body.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>' +
                '<span class="fw-medium">Gagal memuat form tambah dokumen.</span></div>';
        });
}

let currentDeleteUrl = '';
function openDeleteModal(url, number) {
    document.getElementById('deleteDocNumber').textContent = number;
    const errBox = document.getElementById('deleteModalError');
    errBox.classList.add('d-none');
    errBox.textContent = '';
    currentDeleteUrl = url;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal'))?.hide();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
}

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
            location.reload();
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

document.addEventListener('click', function(e) {
    const createBtn = e.target.closest('.js-open-create');
    if (createBtn) { e.preventDefault(); openCreateModal(createBtn.dataset.createUrl); return; }

    const detailBtn = e.target.closest('.js-open-detail');
    if (detailBtn) { e.preventDefault(); openDetailModal(detailBtn); return; }

    const deleteBtn = e.target.closest('.js-open-delete');
    if (deleteBtn) { e.preventDefault(); openDeleteModal(deleteBtn.dataset.deleteUrl, deleteBtn.dataset.documentNumber); return; }

    const injectDelete = e.target.closest('.js-open-delete-modal');
    if (injectDelete) {
        e.preventDefault();
        openDeleteModal(injectDelete.dataset.deleteUrl, injectDelete.dataset.documentNumber);
        return;
    }
});
</script>
@endpush