{{--
    Partial: partials/_search_bar.blade.php
    Form pencarian GET. Di-include di halaman index.
    Parameter: $route (action form), $label, $placeholder,
               $empty (opsional, bool) — true = hasil kosong → tampilkan alert "Tidak Ditemukan",
               $emptyEntity (opsional) — label entitas untuk alert tidak ditemukan / pencarian selesai,
               $count (opsional, int) — jumlah hasil untuk alert "Pencarian Selesai".
--}}
@php
    $label       = $label ?? 'Cari';
    $placeholder = $placeholder ?? 'Cari...';
    $empty       = $empty ?? false;
    $emptyEntity = $emptyEntity ?? 'data';
    $count       = $count ?? null;
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
                @if ($empty)
                    @include('partials._not_found', [
                        'entity'   => $emptyEntity,
                        'resetUrl' => $route,
                    ])
                @else
                    @include('partials._search_done', [
                        'entity' => $emptyEntity,
                        'count'  => $count,
                    ])
                @endif
            @endif
        </form>
    </div>
</div>
