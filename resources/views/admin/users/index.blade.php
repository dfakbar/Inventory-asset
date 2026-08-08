@extends('layouts.app')

@section('title', 'Manajemen User')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Manajemen User</li>
@endsection

@section('content')

{{-- ── Header ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-people-fill text-primary me-2"></i>Manajemen User
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola akun pengguna sistem</p>
    </div>
    <button type="button" class="btn btn-primary js-open-create" data-create-url="{{ route('admin.users.create') }}">
        <i class="bi bi-person-plus-fill me-1"></i>Tambah User
    </button>
</div>

{{-- ── Search ── --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="form-label small text-muted mb-1">
                    <i class="bi bi-search me-1"></i>Cari User
                </label>
                <input type="text"
                       id="search"
                       name="search"
                       class="form-control"
                       placeholder="Cari nama, username, atau email..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Reset
                    </a>
                @endif
            </div>
            @if(request()->filled('search'))
                <div class="col-12">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Menampilkan hasil untuk &ldquo;<strong>{{ request('search') }}</strong>&rdquo;.
                        Pastikan user belum terdaftar sebelum menambah user baru.
                    </small>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- ── Table Card ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center ps-3" style="width:55px">No</th>
                        <th style="min-width:180px">Nama</th>
                        <th style="min-width:130px">Username</th>
                        <th style="min-width:200px">Email</th>
                        <th style="min-width:130px">Role</th>
                        <th class="text-center" style="min-width:90px">Status</th>
                        <th style="min-width:150px">Tgl Dibuat</th>
                        <th class="text-center pe-3" style="width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="text-center text-muted small ps-3">
                                {{ $users->firstItem() + $loop->index }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:34px;height:34px">
                                        <i class="bi bi-person-fill text-secondary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                        @if ($user->id === auth()->id())
                                            <div class="small text-muted">(Anda)</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted">{{ $user->username ?? '—' }}</td>
                            <td class="small text-muted">{{ $user->email }}</td>
                            <td>
                                <span class="{{ $user->role->badgeClass() }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($user->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $user->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit User">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Toggle Active (admin only) --}}
                                    @if(auth()->user()->isAdmin() && $user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $user->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                title="{{ $user->is_active ? 'Nonaktifkan User' : 'Aktifkan User' }}">
                                            <i class="bi {{ $user->is_active ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Hapus --}}
                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus user \'{{ addslashes($user->name) }}\'?\nTindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="{{ $user->id === auth()->id() ? 'Tidak dapat menghapus akun sendiri' : 'Hapus User' }}"
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">Belum ada data user.</span><br>
                                <small>
                                    <a href="{{ route('admin.users.create') }}">Tambah user pertama</a>
                                    sekarang.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->total() > 15)
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 py-3 border-top px-3">
                @include('partials._pagination_per_page', ['paginator' => $users])
                @if ($users->hasPages())
                    {{ $users->links() }}
                @endif
            </div>
        @endif
    </div>
</div>

{{-- ── Modal Tambah User ── --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-person-plus-fill text-primary me-2"></i>Tambah User Baru
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="createModalBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// ── Helpers (dipakai form modal) ────────────────────────────
function toggleUserPermissions(role) {
    const card   = document.getElementById('permissions-card');
    const info   = document.getElementById('info-admin');
    const checks = document.querySelectorAll('.permission-check');

    if (role === 'admin') {
        card.style.opacity   = '0.4';
        card.style.pointerEvents = 'none';
        info.classList.remove('d-none');
        checks.forEach(c => c.checked = false);
    } else {
        card.style.opacity   = '1';
        card.style.pointerEvents = 'auto';
        info.classList.add('d-none');
    }
}

function checkAllPermissions(state) {
    document.querySelectorAll('.permission-check').forEach(c => {
        c.checked = state;
        highlightPermissionCheck(c);
    });
}

function highlightPermissionCheck(el) {
    const wrapper = el.closest('.form-check');
    if (el.checked) {
        wrapper.classList.add('bg-primary', 'bg-opacity-10', 'border-primary');
        wrapper.classList.remove('bg-light');
    } else {
        wrapper.classList.remove('bg-primary', 'bg-opacity-10', 'border-primary');
        wrapper.classList.add('bg-light');
    }
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function setModalLoading(body) {
    body.innerHTML = '<div class="text-center py-5 text-muted">' +
        '<div class="spinner-border text-primary mb-2" role="status"></div>' +
        '<div class="small">Memuat...</div></div>';
}

function clearUserFormErrors(form) {
    form.querySelectorAll('.alert.alert-danger').forEach(a => a.remove());
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

function showUserFormAlert(form, message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show small py-2 mb-4';
    alert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>' + escapeHtml(message);
    form.prepend(alert);
}

function renderUserFormErrors(form, errors) {
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

        let fb = field.nextElementSibling;
        if (!fb || !fb.classList.contains('invalid-feedback')) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback d-block';
            field.parentNode.insertBefore(fb, field.nextSibling);
        }
        fb.textContent = errors[key][0];
    });
}

// ── Bind form modal (submit via AJAX) ───────────────────────
function bindUserForm(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearUserFormErrors(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
        const fd = new FormData(form);

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
                renderUserFormErrors(form, data.errors);
            } else if (data.error) {
                if (submitBtn) submitBtn.disabled = false;
                showUserFormAlert(form, data.error);
            } else {
                if (submitBtn) submitBtn.disabled = false;
                showUserFormAlert(form, 'Terjadi kesalahan. Silakan coba lagi.');
            }
        })
        .catch(() => {
            if (submitBtn) submitBtn.disabled = false;
            showUserFormAlert(form, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        });
    });
}

function initUserFormFields(container) {
    // Toggle password visibility
    container.querySelectorAll('[data-toggle-user-password]').forEach(btn => {
        const input = btn.closest('.input-group').querySelector('input');
        const icon  = btn.querySelector('[data-toggle-icon]');
        btn.addEventListener('click', () => {
            const isHidden = input.type === 'password';
            input.type     = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // Init role state (setelah validasi error / reset)
    const roleVal = container.querySelector('#role')?.value;
    if (roleVal) toggleUserPermissions(roleVal);
}

// ── Buka modal tambah user ──────────────────────────────────
function openCreateUserModal(url) {
    const body = document.getElementById('createModalBody');
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('createModal'));
    setModalLoading(body);
    modal.show();

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memuat form.');
        return res.text();
    })
    .then(html => {
        body.innerHTML = html;
        initUserFormFields(body);
        const form = body.querySelector('#userCreateForm');
        if (form) bindUserForm(form);
    })
    .catch(() => {
        body.innerHTML = '<div class="text-center py-5 text-muted">' +
            '<i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>' +
            '<span class="fw-medium">Gagal memuat form tambah user.</span></div>';
    });
}

// Reset form saat modal ditutup
document.getElementById('createModal')?.addEventListener('hidden.bs.modal', () => {
    document.getElementById('createModalBody').innerHTML = '';
});

// Delegate klik tombol "Tambah User"
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.js-open-create');
    if (btn) openCreateUserModal(btn.dataset.createUrl);
});
</script>
@endpush
