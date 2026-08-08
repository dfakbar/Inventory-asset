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

@include('admin.users._create_form')

@endsection

@push('scripts')
<script>
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

// Init state on page load (setelah validasi error)
document.addEventListener('DOMContentLoaded', () => {
    const roleVal = document.getElementById('role').value;
    if (roleVal) toggleUserPermissions(roleVal);
});

// Toggle password visibility
document.querySelectorAll('[data-toggle-user-password]').forEach(btn => {
    const input = btn.closest('.input-group').querySelector('input');
    const icon  = btn.querySelector('[data-toggle-icon]');
    btn.addEventListener('click', () => {
        const isHidden = input.type === 'password';
        input.type     = isHidden ? 'text' : 'password';
        icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});
</script>
@endpush
