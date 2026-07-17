@extends('layouts.guest')

@section('title', 'Reset Password')

@section('card-title')
    <i class="bi bi-shield-lock me-2 text-primary"></i>Password Baru
@endsection

@section('content')

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small">Password Baru</label>
            <div class="input-group">
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                       placeholder="Minimal 8 karakter"
                       required autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="toggle-password" title="Tampilkan/sembunyikan password">
                    <i class="bi bi-eye" id="toggle-icon"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold small">
                Konfirmasi Password Baru
            </label>
            <div class="input-group">
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                       placeholder="Ulangi password baru"
                       required autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="toggle-password-confirm" title="Tampilkan/sembunyikan password">
                    <i class="bi bi-eye" id="toggle-icon-confirm"></i>
                </button>
            </div>
            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-floppy2 me-1"></i>Simpan Password Baru
            </button>
        </div>
    </form>

@endsection

@push('scripts')
<script>
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
