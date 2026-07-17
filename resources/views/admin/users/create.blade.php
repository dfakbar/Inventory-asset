@extends('layouts.app')

@section('title', 'Tambah User Baru')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-muted">Manajemen User</a>
    </li>
    <li class="breadcrumb-item active">Tambah User</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-person-plus-fill text-primary me-2"></i>Tambah User Baru</h4>
        <p class="text-muted small mb-0 mt-1">Buat akun baru dan tentukan hak aksesnya.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan ditemukan:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('admin.users.store') }}" method="POST" novalidate>
@csrf

<div class="row g-4">

    {{-- ── Kolom Kiri: Informasi Akun ── --}}
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white py-2 px-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-person-fill me-2"></i>Informasi Akun</h6>
            </div>
            <div class="card-body p-4">

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name') }}" placeholder="Nama lengkap user" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                    <input type="text" id="username" name="username"
                           class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                           value="{{ old('username') }}" placeholder="Username untuk login" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email"
                           class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email') }}" placeholder="email@perusahaan.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" id="password" name="password"
                               class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 karakter, kombinasi huruf & angka" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggle-password" title="Tampilkan/sembunyikan password">
                            <i class="bi bi-eye" id="toggle-icon"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold small">Konfirmasi Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                               placeholder="Ulangi password" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggle-password-confirm" title="Tampilkan/sembunyikan password">
                            <i class="bi bi-eye" id="toggle-icon-confirm"></i>
                        </button>
                    </div>
                    @error('password_confirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label fw-semibold small">Role <span class="text-danger">*</span></label>
                    <select id="role" name="role"
                            class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}"
                            onchange="togglePermissions(this.value)" required>
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->value }}" {{ old('role') === $r->value ? 'selected' : '' }}>
                                {{ $r->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Info admin --}}
                <div id="info-admin" class="alert alert-info small d-none mb-0">
                    <i class="bi bi-shield-fill me-1"></i>
                    <strong>Administrator</strong> otomatis mendapatkan <em>semua</em> akses tanpa perlu pengaturan permission.
                </div>

            </div>
        </div>
    </div>

    {{-- ── Kolom Kanan: Permission ── --}}
    <div class="col-lg-7">
        <div class="card shadow-sm border-0" id="permissions-card">
            <div class="card-header bg-dark text-white py-2 px-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-key-fill me-2"></i>Hak Akses (Permission)</h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="checkAll(true)">
                        <i class="bi bi-check-all me-1"></i>Pilih Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="checkAll(false)">
                        <i class="bi bi-x me-1"></i>Hapus Semua
                    </button>
                </div>
            </div>
            <div class="card-body p-4">

                <div class="alert alert-warning small mb-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Permission hanya berlaku untuk user dengan role <strong>Staff</strong>.
                    Centang aksi yang diizinkan untuk user ini.
                </div>

                @foreach($permissionGroups as $groupLabel => $permissions)
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                        <h6 class="mb-0 fw-semibold text-dark">{{ $groupLabel }}</h6>
                    </div>
                    <div class="row g-2 ps-3">
                        @foreach($permissions as $permName => $permLabel)
                        <div class="col-sm-6">
                            <div class="form-check p-3 border rounded {{ in_array($permName, old('permissions', [])) ? 'bg-primary bg-opacity-10 border-primary' : 'bg-light' }}">
                                <input class="form-check-input permission-check"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permName }}"
                                       id="perm_{{ Str::replace('.', '_', $permName) }}"
                                       {{ in_array($permName, old('permissions', [])) ? 'checked' : '' }}
                                       onchange="highlightCheck(this)">
                                <label class="form-check-label small fw-medium"
                                       for="perm_{{ Str::replace('.', '_', $permName) }}">
                                    @php
                                        $icon = match(true) {
                                            str_contains($permName, 'viewAny') => 'bi-eye-fill text-info',
                                            str_contains($permName, 'create')  => 'bi-plus-circle-fill text-success',
                                            str_contains($permName, 'edit')    => 'bi-pencil-fill text-warning',
                                            str_contains($permName, 'delete')  => 'bi-trash-fill text-danger',
                                            default                            => 'bi-circle-fill text-secondary',
                                        };
                                    @endphp
                                    <i class="bi {{ $icon }} me-1"></i>{{ $permLabel }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @if(!$loop->last)<hr>@endif
                @endforeach

            </div>
        </div>
    </div>

</div>

{{-- Action buttons --}}
<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Batal
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-floppy2 me-1"></i>Simpan User
    </button>
</div>

</form>
@endsection

@push('scripts')
<script>
function togglePermissions(role) {
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

function checkAll(state) {
    document.querySelectorAll('.permission-check').forEach(c => {
        c.checked = state;
        highlightCheck(c);
    });
}

function highlightCheck(el) {
    const wrapper = el.closest('.form-check');
    if (el.checked) {
        wrapper.classList.add('bg-primary', 'bg-opacity-10', 'border-primary');
        wrapper.classList.remove('bg-light');
    } else {
        wrapper.classList.remove('bg-primary', 'bg-opacity-10', 'border-primary');
        wrapper.classList.add('bg-light');
    }
}

// Init state on page load (setelah validasi error)
document.addEventListener('DOMContentLoaded', () => {
    const roleVal = document.getElementById('role').value;
    if (roleVal) togglePermissions(roleVal);
});

// Toggle password visibility
const toggleBtn  = document.getElementById('toggle-password');
const toggleIcon = document.getElementById('toggle-icon');
const pwInput    = document.getElementById('password');
if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const isHidden = pwInput.type === 'password';
        pwInput.type   = isHidden ? 'text' : 'password';
        toggleIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
}

// Toggle konfirmasi password visibility
const toggleBtnConfirm  = document.getElementById('toggle-password-confirm');
const toggleIconConfirm = document.getElementById('toggle-icon-confirm');
const pwInputConfirm    = document.getElementById('password_confirmation');
if (toggleBtnConfirm) {
    toggleBtnConfirm.addEventListener('click', () => {
        const isHidden = pwInputConfirm.type === 'password';
        pwInputConfirm.type   = isHidden ? 'text' : 'password';
        toggleIconConfirm.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
}
</script>
@endpush
