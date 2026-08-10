{{--
    Partial: partials/_not_found.blade.php
    Alert amber "Tidak Ditemukan" untuk hasil pencarian/filter kosong.
    Parameter:
        $entity     (opsional, default 'data') — label entitas, mis. 'lokasi', 'merek'
        $search     (opsional) — kata kunci yang ditampilkan (default request('search'))
        $resetUrl   (opsional) — URL untuk menghapus pencarian/filter
--}}
@php
    $entity   = $entity ?? 'data';
    $search   = $search ?? request('search');
    $resetUrl = $resetUrl ?? null;
@endphp
<div class="alert alert-warning d-flex align-items-center gap-2 py-1 px-3 shadow-sm mb-3" role="alert">
    <i class="bi bi-search-heart flex-shrink-0 text-warning"></i>
    <span class="small text-warning-emphasis">
        <strong>Tidak Ditemukan.</strong>
        Tidak ada {{ $entity }} yang cocok dengan
        <strong>&ldquo;{{ $search }}&rdquo;</strong>.
        @if ($resetUrl)
            Periksa kembali kata kunci, atau
            <a href="{{ $resetUrl }}" class="text-warning-emphasis fw-semibold text-decoration-underline">
                hapus pencarian
            </a>.
        @else
            Periksa kembali kata kunci yang dimasukkan.
        @endif
    </span>
</div>
