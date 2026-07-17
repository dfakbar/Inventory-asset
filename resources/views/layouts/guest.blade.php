<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — {{ config('app.name', 'AssetMS') }}</title>

    {{-- Bootstrap 5.3 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous"
          referrerpolicy="no-referrer">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1f36 0%, #212529 60%, #0d6efd22 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
        }

        .auth-brand {
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.5px;
        }

        .auth-brand span { color: #0d6efd; }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.4);
        }

        .card-header {
            border-radius: 1rem 1rem 0 0 !important;
            border-bottom: 1px solid rgba(0,0,0,.06);
            background: #fff;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13,110,253,.15);
        }

        .btn-login {
            padding: .65rem 1.5rem;
            font-weight: 600;
            letter-spacing: .3px;
        }

        .divider-text {
            position: relative;
            text-align: center;
            font-size: .8rem;
            color: #6c757d;
        }

        .divider-text::before,
        .divider-text::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: #dee2e6;
        }

        .divider-text::before { left: 0; }
        .divider-text::after  { right: 0; }
    </style>
</head>
<body>

<div class="auth-card">

    {{-- Brand --}}
    <div class="text-center mb-4">
        <div class="auth-brand mb-1">
            <i class="bi bi-building-gear me-1"></i>Asset<span>MS</span>
        </div>
        <p class="text-white-50 small mb-0">Sistem Informasi Manajemen Aset</p>
    </div>

    {{-- Card --}}
    <div class="card">
        <div class="card-header py-3 px-4">
            <h5 class="mb-0 fw-semibold text-dark">
                @yield('card-title', '<i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Masuk ke Sistem')
            </h5>
        </div>
        <div class="card-body p-4">
            @yield('content')
        </div>
    </div>

    {{-- Footer --}}
    <p class="text-center text-white-50 small mt-4 mb-0">
        &copy; {{ date('Y') }} {{ config('app.name', 'AssetMS') }}
    </p>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
        defer></script>
@stack('scripts')
</body>
</html>
