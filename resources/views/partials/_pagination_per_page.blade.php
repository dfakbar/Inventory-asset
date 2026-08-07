{{--
    Partial: partials/_pagination_per_page.blade.php
    Dropdown ukuran halaman (per_page) — muncul jika total data > 15.
    Pass variabel: $paginator (LengthAwarePaginator).
--}}
@php
    $paginator = $paginator ?? null;
    $options = [15, 30, 60, 120, 'all'];
    $current = request('per_page');
@endphp
@if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginator->total() > 15)
    <form method="GET" action="{{ request()->url() }}" class="d-inline-flex align-items-center gap-2">
        @foreach (request()->except(['per_page', 'page']) as $key => $value)
            @if (is_array($value))
                @foreach ($value as $k => $v)
                    <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label class="small text-muted text-nowrap mb-0">
            <i class="bi bi-list-ul me-1"></i>Per halaman:
        </label>
        <select name="per_page"
                class="form-select form-select-sm"
                style="width:auto"
                onchange="this.form.submit()"
                aria-label="Ukuran halaman">
            @foreach ($options as $opt)
                <option value="{{ $opt }}" {{ (string) $current === (string) $opt ? 'selected' : '' }}>
                    {{ $opt === 'all' ? 'Semua' : $opt }}
                </option>
            @endforeach
        </select>
    </form>
@endif
