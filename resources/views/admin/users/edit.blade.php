@extends('layouts.app')

@section('title', 'Edit User — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-muted">Manajemen User</a>
    </li>
    <li class="breadcrumb-item active">Edit: {{ $user->name }}</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square text-warning me-2"></i>Edit User</h4>
        <p class="text-muted small mb-0 mt-1">Perbarui data dan hak akses <strong>{{ $user->name }}</strong>.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

{{-- Warning jika edit diri sendiri --}}
@if($user->id === auth()->id())
<div class="alert alert-warning d-flex gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
    <div>
        <strong>Anda sedang mengedit akun sendiri.</strong><br>
        <span class="small">Role tidak dapat diubah untuk mencegah kehilangan akses administrator.</span>
    </div>
</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('admin.users.update', $user) }}" method="POST" novalidate>
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── Kolom Kiri: Informasi Akun ── --}}
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-warning py-2 px-3">
                <h6 class="mb-0 fw-semibold text-dark"><i class="bi bi-person-fill me-2"></i>Informasi Akun</h6>
            </div>
            <div class="card-body p-4">

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email"
                           class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small">
                        Password Baru
                        <span class="text-muted fw-normal">(kosongkan jika tidak diubah)</span>
                    </label>
                    <div class="input-group">
                        <input type="password" id="password" name="password"
                               class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 karakter, kombinasi huruf & angka">
                        <button class="btn btn-outline-secondary" type="button" id="toggle-password" title="Tampilkan/sembunyikan password">
                            <i class="bi bi-eye" id="toggle-icon"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold small">Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" placeholder="Ulangi password baru">
                        <button class="btn btn-outline-secondary" type="button" id="toggle-password-confirm" title="Tampilkan/sembunyikan password">
                            <i class="bi bi-eye" id="toggle-icon-confirm"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label fw-semibold small">Role <span class="text-danger">*</span></label>
                    @if($user->id === auth()->id())
                        {{-- Tidak bisa ubah role sendiri --}}
                        <input type="hidden" name="role" value="{{ $user->role->value }}">
                        <input type="text" class="form-control bg-light text-muted"
                               value="{{ $user->role->label() }}" disabled>
                        <div class="form-text"><i class="bi bi-lock me-1"></i>Role tidak dapat diubah untuk akun sendiri.</div>
                    @else
                        <select id="role" name="role"
                                class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}"
                                onchange="togglePermissions(this.value)" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}"
                                    {{ old('role', $user->role->value) === $r->value ? 'selected' : '' }}>
                                    {{ $r->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif
                </div>

                <div id="info-admin" class="alert alert-info small mb-0
                    {{ old('role', $user->role->value) === 'admin' ? '' : 'd-none' }}">
                    <i class="bi bi-shield-fill me-1"></i>
                    <strong>Administrator</strong> otomatis mendapatkan semua akses.
                </div>

            </div>
        </div>
    </div>

    {{-- ── Kolom Kanan: Permission ── --}}
    <div class="col-lg-7">
        <div class="card shadow-sm border-0"
             id="permissions-card"
             style="{{ old('role', $user->role->value) === 'admin' ? 'opacity:.4;pointer-events:none' : '' }}">
            <div class="card-header bg-dark text-white py-2 px-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-key-fill me-2"></i>Hak Akses (Permission)</h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="checkAll(true)">
                        <i class="bi bi-check-all me-1"></i>Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="checkAll(false)">
                        <i class="bi bi-x me-1"></i>Hapus
                    </button>
                </div>
            </div>
            <div class="card-body p-4">

                <div class="alert alert-info small mb-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Permission yang dicentang saat ini: <strong>{{ count($userPermissions) }} dari {{ collect($permissionGroups)->flatten()->count() }}</strong> permission.
                </div>

                @foreach($permissionGroups as $groupLabel => $permissions)
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                        <h6 class="mb-0 fw-semibold text-dark">{{ $groupLabel }}</h6>
                    </div>
                    <div class="row g-2 ps-3">
                        @foreach($permissions as $permName => $permLabel)
                        @php
                            $isChecked = in_array($permName, old('permissions', $userPermissions));
                        @endphp
                        <div class="col-sm-6">
                            <div class="form-check p-3 border rounded {{ $isChecked ? 'bg-primary bg-opacity-10 border-primary' : 'bg-light' }}">
                                <input class="form-check-input permission-check"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permName }}"
                                       id="perm_{{ Str::replace('.', '_', $permName) }}"
                                       {{ $isChecked ? 'checked' : '' }}
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

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Batal
    </a>
    <button type="submit" class="btn btn-warning">
        <i class="bi bi-floppy2 me-1"></i>Perbarui User
    </button>
</div>

</form>
@endsection

@push('scripts')
<script>
function togglePermissions(role) {
    const card = document.getElementById('permissions-card');
    const info = document.getElementById('info-admin');
    if (role === 'admin') {
        card.style.opacity = '0.4';
        card.style.pointerEvents = 'none';
        info?.classList.remove('d-none');
    } else {
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';
        info?.classList.add('d-none');
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
