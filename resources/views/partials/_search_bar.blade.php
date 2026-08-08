{{--
    Partial: partials/_search_bar.blade.php
    Form pencarian GET. Di-include di halaman index.
    Parameter: $route (action form), $label, $placeholder, $hint (opsional).
--}}
@php
    $label       = $label ?? 'Cari';
    $placeholder = $placeholder ?? 'Cari...';
    $hint        = $hint ?? 'Pastikan data belum terdaftar sebelum menambah data baru.';
@endphp
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ $route }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="form-label small text-muted mb-1">
                    <i class="bi bi-search me-1"></i>{{ $label }}
                </label>
                <input type="text"
                       id="search"
                       name="search"
                       class="form-control"
                       placeholder="{{ $placeholder }}"
                       value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ $route }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Reset
                    </a>
                @endif
            </div>
            @if(request()->filled('search'))
                <div class="col-12">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>{{ $hint }}
                    </small>
                </div>
            @endif
        </form>
    </div>
</div>
