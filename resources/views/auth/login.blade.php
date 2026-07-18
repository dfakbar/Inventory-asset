@extends('layouts.guest')

@section('title', 'Login')

@section('card-title')
    <i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Masuk ke Sistem
@endsection

@section('content')

    {{-- Notifikasi registrasi dinonaktifkan --}}
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Status session (setelah logout / link reset password terkirim) --}}
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Error dari middleware atau redirect --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div id="loginFields">
        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            {{-- Email / Username --}}
            <div class="mb-3">
                <label for="login" class="form-label fw-semibold small">
                    <i class="bi bi-person me-1 text-muted"></i>Email / Username
                </label>
                <input type="text"
                       id="login"
                       name="login"
                       class="form-control {{ $errors->has('login') ? 'is-invalid' : '' }}"
                       value="{{ old('login') }}"
                       placeholder="email@perusahaan.com atau username"
                       required autofocus autocomplete="username">
                @error('login')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label fw-semibold small mb-0">
                        <i class="bi bi-lock me-1 text-muted"></i>Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-decoration-none small text-primary">
                            Lupa password?
                        </a>
                    @endif
                </div>
                <div class="input-group">
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="••••••••"
                           required autocomplete="current-password">
                    <button class="btn btn-outline-secondary"
                            type="button"
                            id="toggle-password"
                            title="Tampilkan/sembunyikan password">
                        <i class="bi bi-eye" id="toggle-icon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Remember Me --}}
            <div class="mb-4 form-check">
                <input type="checkbox"
                       class="form-check-input"
                       id="remember_me"
                       name="remember">
                <label class="form-check-label small text-muted" for="remember_me">
                    Ingat saya di perangkat ini
                </label>
            </div>

            {{-- Submit --}}
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                </button>
            </div>
        </form>
    </div>

    {{-- Check Asset Toggle --}}
    <div class="divider-text my-3">atau</div>

    <div class="text-center" id="loginFormToggle">
        <a href="#" id="checkAssetToggle" class="text-decoration-none text-primary small">
            <i class="bi bi-search me-1"></i>Check Asset
        </a>
    </div>

    <div id="trackForm" style="display:none;">
        <form method="GET" action="{{ route('public.track') }}" class="row g-2">
            <div class="col-8">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Kode Aset atau Serial Number" required>
            </div>
            <div class="col-4 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
        </form>
        <div class="mt-2 text-center">
            <a href="#" id="backToLogin" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
            </a>
        </div>
    </div>

    {{-- Info sistem tertutup --}}
    <div class="mt-4 pt-3 border-top text-center">
        <p class="text-muted small mb-0">
            <i class="bi bi-shield-lock me-1"></i>
            Sistem ini bersifat <strong>tertutup</strong>.<br>
            Hanya akun yang dibuat oleh Administrator yang dapat masuk.
        </p>
    </div>

@endsection

@push('scripts')
<script>
    (() => {
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

        // Check Asset toggle
        const loginFields     = document.getElementById('loginFields');
        const loginFormToggle = document.getElementById('loginFormToggle');
        const trackForm       = document.getElementById('trackForm');
        const checkAssetLink  = document.getElementById('checkAssetToggle');
        const backToLoginLink = document.getElementById('backToLogin');
        const dividerText     = document.querySelector('.divider-text');
        const infoText        = document.querySelector('.border-top');

        if (checkAssetLink && trackForm && loginFields && loginFormToggle) {
            checkAssetLink.addEventListener('click', (e) => {
                e.preventDefault();
                loginFields.style.display = 'none';
                loginFormToggle.style.display = 'none';
                dividerText.style.display = 'none';
                if (infoText) infoText.style.display = 'none';
                trackForm.style.display = 'block';
            });

            backToLoginLink.addEventListener('click', (e) => {
                e.preventDefault();
                trackForm.style.display = 'none';
                loginFields.style.display = 'block';
                loginFormToggle.style.display = 'block';
                dividerText.style.display = 'block';
                if (infoText) infoText.style.display = 'block';
            });
        }
    })();
</script>
@endpush
