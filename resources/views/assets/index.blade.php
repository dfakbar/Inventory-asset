@extends('layouts.app')

@section('title', 'Manajemen Aset')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Manajemen Aset</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-box-seam-fill text-primary me-2"></i>Manajemen Aset
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola seluruh inventaris aset perusahaan</p>
    </div>
    <div class="d-flex gap-2">
        @can('asset.viewAny')
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Ekspor
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="{{ route('assets.export.csv', request()->query()) }}">
                        <i class="bi bi-filetype-csv me-2"></i>Ekspor CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('reports.index') }}">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Laporan PDF
                    </a>
                </li>
            </ul>
        </div>
        @endcan
        @can('asset.create')
        <a href="{{ route('assets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Aset Baru
        </a>
        @endcan
    </div>
</div>

{{-- ── Filter & Table Card ── --}}
<div class="card shadow-sm border-0">
    {{-- Card Header: Filter --}}
    <div class="card-header bg-primary text-white py-3">
        <form method="GET" action="{{ route('assets.index') }}" id="filter-form">
            <div class="row g-2 align-items-end">
                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label small text-white-50 mb-1">
                        <i class="bi bi-search me-1"></i>Pencarian
                    </label>
                    <input type="text"
                           id="search"
                           name="search"
                           class="form-control form-control-sm"
                           placeholder="Cari kode aset, nama, merek, serial..."
                           value="{{ request('search') }}">
                </div>

                {{-- Status --}}
                <div class="col-6 col-md-3">
                    <label for="status" class="form-label small text-white-50 mb-1">
                        <i class="bi bi-tag me-1"></i>Status
                    </label>
                    <select id="status" name="status" class="form-select form-select-sm" data-searchable>
                        <option value="">— Semua Status —</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}"
                                {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori --}}
                <div class="col-6 col-md-3">
                    <label for="category_id" class="form-label small text-white-50 mb-1">
                        <i class="bi bi-grid me-1"></i>Kategori
                    </label>
                    <select id="category_id" name="category_id" class="form-select form-select-sm" data-searchable>
                        <option value="">— Semua Kategori —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol --}}
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-light btn-sm flex-fill">
                        <i class="bi bi-funnel-fill me-1"></i>Cari
                    </button>
                    <a href="{{ route('assets.index') }}" class="btn btn-outline-light btn-sm flex-fill">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Card Body: Table --}}
    <div class="card-body p-0">
        {{-- Summary bar --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-light">
            <span class="small text-muted">
                <i class="bi bi-collection me-1"></i>
                Total:
                <span class="fw-semibold text-dark">{{ $assets->total() }}</span> aset
                @if (request()->hasAny(['search', 'status', 'category_id']))
                    <span class="ms-2 badge bg-warning text-dark">
                        <i class="bi bi-funnel-fill me-1"></i>Filter aktif
                    </span>
                @endif
            </span>
            <span class="small text-muted">
                Halaman {{ $assets->currentPage() }} dari {{ $assets->lastPage() }}
            </span>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width:50px">#</th>
                        <th style="min-width:130px">Kode Aset</th>
                        <th style="min-width:180px">Nama Aset</th>
                        <th style="min-width:120px">Kategori</th>
                        <th style="min-width:150px">Lokasi</th>
                        <th style="min-width:140px">Merek / Model</th>
                        <th style="min-width:120px">Vendor</th>
                        <th class="text-center" style="min-width:140px">Status</th>
                        <th class="text-center" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr>
                            {{-- Nomor urut --}}
                            <td class="text-center text-muted small">
                                {{ $assets->firstItem() + $loop->index }}
                            </td>

                            {{-- Kode Aset --}}
                            <td>
                                <span class="font-monospace fw-semibold small text-primary">
                                    {{ $asset->asset_code }}
                                </span>
                            </td>

                            {{-- Nama Aset --}}
                            <td>
                                <span class="fw-medium">{{ $asset->name }}</span>
                            </td>

                            {{-- Kategori --}}
                            <td>
                                @if ($asset->category)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">
                                        {{ $asset->category->abbreviation ?? $asset->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Lokasi --}}
                            <td class="small text-muted">
                                {{ $asset->location?->name ?? '—' }}
                            </td>

                            {{-- Merek / Model --}}
                            <td class="small">
                                @if ($asset->brand?->name || $asset->model)
                                    <span class="text-dark">{{ $asset->brand?->name }}</span>
                                    @if ($asset->model)
                                        <br>
                                        <span class="text-muted">{{ $asset->model }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Vendor --}}
                            <td class="small text-muted">
                                {{ $asset->vendor?->name ?? '—' }}
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                <span class="{{ $asset->status->badgeClass() }} d-inline-flex align-items-center gap-1 px-2 py-1">
                                    <i class="bi {{ $asset->status->icon() }}"></i>
                                    {{ $asset->status->label() }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Detail --}}
                                    <a href="{{ route('assets.show', $asset) }}"
                                       class="btn btn-sm btn-info text-white"
                                       title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if(auth()->user()->can('asset.edit') || auth()->user()->can('asset.mutate'))
                                     <a href="{{ route('assets.edit', $asset) }}"
                                        class="btn btn-sm btn-warning"
                                        title="Edit Aset">
                                          <i class="bi bi-pencil"></i>
                                     </a>
                                     @endif

                                    @can('asset.delete')
                                    <form action="{{ route('assets.destroy', $asset) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus aset \'{{ addslashes($asset->name) }}\'?\nTindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus Aset">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2 opacity-30"></i>
                                <span class="fw-medium">Belum ada data aset.</span>
                                @if (request()->hasAny(['search', 'status', 'category_id']))
                                    <br>
                                    <small>Coba ubah atau
                                        <a href="{{ route('assets.index') }}">hapus filter</a>
                                        yang diterapkan.
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($assets->hasPages())
            <div class="d-flex justify-content-center py-3 border-top px-3">
                {{ $assets->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
