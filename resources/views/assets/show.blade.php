@extends('layouts.app')

@section('title', 'Detail Aset — ' . $asset->asset_code)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('assets.index') }}" class="text-decoration-none text-muted">Manajemen Aset</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $asset->asset_code }}</li>
@endsection

@section('content')

{{-- ── Page Header ── --}}
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-box-seam-fill text-primary me-2"></i>Detail Aset
        </h4>
        <p class="font-monospace text-muted mb-0 fs-6">{{ $asset->asset_code }}</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()->can('asset.edit') || auth()->user()->can('asset.mutate'))
        <a href="{{ route('assets.edit', $asset) }}" class="btn btn-warning">
            <i class="bi bi-pencil-fill me-1"></i>Edit Aset
        </a>
        @endif

        @can('asset.delete')
        <form action="{{ route('assets.destroy', $asset) }}"
              method="POST"
              onsubmit="return confirm('Hapus aset \'{{ addslashes($asset->name) }}\'?\nTindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash3-fill me-1"></i>Hapus
            </button>
        </form>
        @endcan

        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

{{-- ── Two-column layout ── --}}
<div class="row g-4">

    {{-- ════════════════════════════
         Kolom Kiri (informasi)
    ════════════════════════════ --}}
    <div class="col-lg-8">

        {{-- Card: Informasi Utama --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-2 px-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle-fill me-2"></i>Informasi Utama
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small" style="width:35%">
                                Kode Aset
                            </th>
                            <td class="py-3 pe-3">
                                <span class="font-monospace fw-bold fs-6 text-primary">
                                    {{ $asset->asset_code }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Nama Aset</th>
                            <td class="py-3 pe-3 fw-semibold">{{ $asset->name }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Kategori</th>
                            <td class="py-3 pe-3">
                                @if ($asset->category)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1">
                                        <i class="bi bi-grid me-1"></i>
                                        {{ $asset->category->name }}
                                        @if ($asset->category->abbreviation)
                                            <span class="text-muted">({{ $asset->category->abbreviation }})</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Status</th>
                            <td class="py-3 pe-3">
                                <span class="{{ $asset->status->badgeClass() }} d-inline-flex align-items-center gap-1 px-2 py-1">
                                    <i class="bi {{ $asset->status->icon() }}"></i>
                                    {{ $asset->status->label() }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Lokasi</th>
                            <td class="py-3 pe-3">
                                @if ($asset->location)
                                    <div class="fw-medium">{{ $asset->location->name }}</div>
                                    @if ($asset->location->full_address)
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $asset->location->full_address }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Tanggal Mutasi</th>
                            <td class="py-3 pe-3">
                                @if ($asset->mutation_date)
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3-event text-muted"></i>
                                        {{ $asset->mutation_date->translatedFormat('d F Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 py-3 text-muted fw-medium small">Pengguna / PIC</th>
                            <td class="py-3 pe-3">
                                @if ($asset->assignedUser)
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <span class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                                              style="width:28px;height:28px;font-size:.75rem">
                                            {{ strtoupper(substr($asset->assignedUser->name, 0, 1)) }}
                                        </span>
                                        {{ $asset->assignedUser->name }}
                                    </span>
                                @else
                                    <span class="text-muted">Belum ditugaskan</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Card: Spesifikasi --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white py-2 px-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cpu-fill me-2"></i>Spesifikasi Perangkat
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small" style="width:35%">Merek</th>
                            <td class="py-3 pe-3">{{ $asset->brand ?: '—' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Model</th>
                            <td class="py-3 pe-3">{{ $asset->model ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-3 py-3 text-muted fw-medium small">Nomor Seri</th>
                            <td class="py-3 pe-3">
                                @if ($asset->serial_number)
                                    <span class="font-monospace">{{ $asset->serial_number }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Card: Finansial --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white py-2 px-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-coin me-2"></i>Informasi Finansial
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        @if(auth()->user()->can('asset.manage_finances'))
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small" style="width:35%">
                                Tanggal Pembelian
                            </th>
                            <td class="py-3 pe-3">
                                @if ($asset->purchase_date)
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3 text-muted"></i>
                                        {{ $asset->purchase_date->translatedFormat('d F Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if(auth()->user()->can('asset.manage_finances'))
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Harga Pembelian</th>
                            <td class="py-3 pe-3">
                                @if ($asset->purchase_price)
                                    <span class="fw-semibold text-success fs-6">
                                        Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th class="ps-3 py-3 text-muted fw-medium small">Jumlah / Kuantitas</th>
                            <td class="py-3 pe-3">
                                <span class="badge bg-primary fs-6 px-3">
                                    {{ $asset->quantity ?? 1 }}
                                </span>
                                <span class="text-muted small ms-1">unit</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Card: Catatan (conditional) --}}
        @if ($asset->notes)
            <div class="card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-header bg-warning bg-opacity-10 py-2 px-3">
                    <h6 class="mb-0 fw-semibold text-warning-emphasis">
                        <i class="bi bi-sticky-fill me-2"></i>Catatan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted" style="white-space: pre-wrap;">{{ $asset->notes }}</p>
                </div>
            </div>
        @endif
    </div>{{-- /col-lg-8 --}}

    {{-- ════════════════════════════
         Kolom Kanan (foto + status)
    ════════════════════════════ --}}
    <div class="col-lg-4">

        {{-- Card: Foto Aset --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header py-2 px-3 bg-light">
                <h6 class="mb-0 fw-semibold text-secondary">
                    <i class="bi bi-image me-2"></i>Foto Aset
                </h6>
            </div>
            <div class="card-body p-0 overflow-hidden" style="border-radius: 0 0 .375rem .375rem">
                @if ($asset->image)
                    <img src="{{ asset('storage/' . $asset->image) }}"
                         alt="Foto {{ $asset->name }}"
                         class="img-fluid w-100"
                         style="max-height: 300px; object-fit: cover;">
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-muted bg-light py-5">
                        <i class="bi bi-image-fill" style="font-size: 4rem; opacity: .25;"></i>
                        <small class="mt-2">Tidak ada foto</small>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card: Status --}}
        <div class="card shadow-sm border-0">
            <div class="card-header py-2 px-3 bg-light">
                <h6 class="mb-0 fw-semibold text-secondary">
                    <i class="bi bi-activity me-2"></i>Status Aset
                </h6>
            </div>
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <i class="bi {{ $asset->status->icon() }} d-block"
                       style="font-size: 3rem;"></i>
                </div>
                <span class="{{ $asset->status->badgeClass() }} d-inline-flex align-items-center gap-2 px-3 py-2 fs-6">
                    <i class="bi {{ $asset->status->icon() }}"></i>
                    {{ $asset->status->label() }}
                </span>
                <p class="text-muted small mt-3 mb-0 px-2">
                    @switch($asset->status->value)
                        @case('In Use')
                            Aset sedang aktif digunakan oleh pengguna.
                            @break
                        @case('Spare')
                            Aset tersedia sebagai cadangan.
                            @break
                        @case('Service')
                            Aset sedang dalam proses servis/perbaikan.
                            @break
                        @case('Broken')
                            Aset mengalami kerusakan dan tidak dapat digunakan.
                            @break
                        @case('Disposal')
                            Aset telah diproses untuk disposal/penghapusan.
                            @break
                        @case('Broken-Check')
                            Aset dilaporkan rusak dan perlu dicek ulang.
                            @break
                    @endswitch
                </p>
            </div>

            {{-- Quick info strip --}}
            <div class="card-footer bg-light py-2 px-3 d-flex justify-content-between small text-muted">
                <span>
                    <i class="bi bi-clock me-1"></i>
                    Dibuat: {{ $asset->created_at->diffForHumans() }}
                </span>
                <span>
                    <i class="bi bi-pencil me-1"></i>
                    {{ $asset->updated_at->diffForHumans() }}
                </span>
            </div>
        </div>

    </div>{{-- /col-lg-4 --}}
</div>{{-- /row --}}

@endsection
