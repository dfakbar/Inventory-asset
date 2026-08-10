{{--
    Partial: partials/_search_done.blade.php
    Alert hijau "Pencarian Selesai" untuk hasil pencarian/filter yang ditemukan.
    Struktur identik dengan partials/_not_found.blade.php (warna sukses).
    Parameter:
        $entity     (opsional, default 'data') — label entitas, mis. 'merek'
        $count      (opsional, int) — jumlah hasil; jika null hanya tampil "Pencarian selesai."
--}}
@php
    $entity = $entity ?? 'data';
    $count  = $count ?? null;
@endphp
<div class="alert alert-success d-flex align-items-center gap-2 py-1 px-3 shadow-sm mb-3" role="alert">
    <i class="bi bi-check-circle-fill flex-shrink-0 text-success"></i>
    <span class="small text-success-emphasis">
        <strong>Pencarian selesai.</strong>
        @if ($count !== null)
            Menampilkan <strong>{{ $count }}</strong> {{ $entity }}.
        @endif
    </span>
</div>
