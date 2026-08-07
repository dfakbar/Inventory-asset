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
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#columnSettingsModal">
            <i class="bi bi-layout-three-columns me-1"></i>Atur Kolom
        </button>
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
        <button type="button" class="btn btn-primary js-open-create" data-create-url="{{ route('assets.create') }}">
            <i class="bi bi-plus-lg me-1"></i>Tambah Aset Baru
        </button>
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
        @php $bulkEnabled = auth()->user()->can('asset.edit') || auth()->user()->can('asset.mutate'); @endphp
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

        @if ($bulkEnabled)
        {{-- Bulk action bar --}}
        <div class="d-none align-items-center justify-content-between px-3 py-2 border-bottom bg-warning bg-opacity-10" id="bulkBar">
            <span class="small fw-semibold text-dark">
                <i class="bi bi-check2-square me-1"></i><span id="bulkCount">0</span> aset dipilih
            </span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-warning" id="openBulkEditBtn" disabled>
                    <i class="bi bi-pencil-square me-1"></i>Edit Terpilih
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearBulkSelectionBtn">
                    <i class="bi bi-x-lg me-1"></i>Batal
                </button>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        @if ($bulkEnabled)
                        <th class="text-center" style="width:40px">
                            <input type="checkbox" class="form-check-input" id="selectAllAsset"
                                   title="Pilih semua di halaman ini" aria-label="Pilih semua">
                        </th>
                        @endif
                        <th class="text-center" style="width:50px">#</th>
                        @foreach ($columns as $col)
                            <th class="text-center" style="{{ $col === 'aksi' ? 'width:120px' : '' }}">
                                {{ \App\Http\Controllers\AssetController::COLUMN_LABELS[$col] ?? ucfirst($col) }}
                            </th>
                        @endforeach
                        <th class="text-center" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr>
                            @if ($bulkEnabled)
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input row-checkbox"
                                       value="{{ $asset->id }}"
                                       aria-label="Pilih {{ $asset->asset_code }}">
                            </td>
                            @endif
                            {{-- Nomor urut --}}
                            <td class="text-center text-muted small">
                                {{ $assets->firstItem() + $loop->index }}
                            </td>

                            @foreach ($columns as $col)
                                @switch($col)
                                    @case('kode_aset')
                                        <td>
                                            <span class="font-monospace fw-semibold small text-primary">
                                                {{ $asset->asset_code }}
                                            </span>
                                        </td>
                                        @break
                                    @case('nama')
                                        <td>
                                            <span class="fw-medium">{{ $asset->name }}</span>
                                        </td>
                                        @break
                                    @case('kategori')
                                        <td>
                                            @if ($asset->category)
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">
                                                    {{ $asset->category->abbreviation ?? $asset->category->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        @break
                                    @case('lokasi')
                                        <td class="small text-muted">
                                            {{ $asset->location?->name ?? '—' }}
                                        </td>
                                        @break
                                    @case('pic')
                                        <td class="small">
                                            @if ($asset->assignedUser)
                                                <span class="d-inline-flex align-items-center gap-1">
                                                    <span class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                          style="width:22px;height:22px;font-size:.65rem">
                                                        {{ strtoupper(substr($asset->assignedUser->name, 0, 1)) }}
                                                    </span>
                                                    {{ $asset->assignedUser->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        @break
                                    @case('karyawan')
                                        <td class="small">
                                            @if ($asset->employee)
                                                <span class="d-inline-flex align-items-center gap-1">
                                                    <span class="avatar bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                          style="width:22px;height:22px;font-size:.65rem">
                                                        {{ strtoupper(substr($asset->employee->name, 0, 1)) }}
                                                    </span>
                                                    {{ $asset->employee->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        @break
                                    @case('merek_model')
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
                                        @break
                                    @case('serial_number')
                                        <td class="small font-monospace text-muted">
                                            {{ $asset->serial_number ?? '—' }}
                                        </td>
                                        @break
                                    @case('mac')
                                        <td class="small font-monospace text-muted">
                                            {{ $asset->mac_address ?? '—' }}
                                        </td>
                                        @break
                                    @case('vendor')
                                        <td class="small text-muted">
                                            {{ $asset->vendor?->name ?? '—' }}
                                        </td>
                                        @break
                                    @case('status')
                                        <td class="text-center">
                                            <span class="{{ $asset->status->badgeClass() }} d-inline-flex align-items-center gap-1 px-2 py-1">
                                                <i class="bi {{ $asset->status->icon() }}"></i>
                                                {{ $asset->status->label() }}
                                            </span>
                                        </td>
                                        @break
                                @endswitch
                            @endforeach

                            {{-- Aksi --}}
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Label --}}
                                    <button type="button"
                                            class="btn btn-sm btn-outline-dark show-label"
                                            data-asset-code="{{ $asset->asset_code }}"
                                            data-asset-name="{{ $asset->name }}"
                                            data-qr-url="{{ route('assets.qr-code', $asset) }}"
                                            data-barcode-url="{{ route('assets.barcode', $asset) }}"
                                            data-print-url="{{ route('assets.print-code', $asset) }}"
                                            title="Lihat Label QR/Barcode">
                                        <i class="bi bi-upc-scan"></i>
                                    </button>

                                    {{-- Detail --}}
                                    <button type="button"
                                            class="btn btn-sm btn-info text-white js-open-detail"
                                            data-detail-url="{{ route('assets.show', $asset) }}"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Edit --}}
                                    @if(auth()->user()->can('asset.edit') || auth()->user()->can('asset.mutate'))
                                    <button type="button"
                                            class="btn btn-sm btn-warning js-open-edit"
                                            data-edit-url="{{ route('assets.edit', $asset) }}"
                                            title="Edit Aset">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif

                                    {{-- Hapus --}}
                                    @can('asset.delete')
                                    <button type="button"
                                            class="btn btn-sm btn-danger js-open-delete"
                                            data-delete-url="{{ route('assets.destroy', $asset) }}"
                                            data-name="{{ $asset->name }}"
                                            title="Hapus Aset">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + ($bulkEnabled ? 3 : 2) }}" class="text-center py-5 text-muted">
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
        @if ($assets->total() > 15)
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 py-3 border-top px-3">
                @include('partials._pagination_per_page', ['paginator' => $assets])
                @if ($assets->hasPages())
                    {{ $assets->links() }}
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Modal QR/Barcode --}}
<div class="modal fade" id="labelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-upc-scan me-2 text-primary"></i>Label Aset
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-3">
                <div class="mb-2">
                    <div class="btn-group btn-group-sm" role="group" id="modalLabelTypeToggle">
                        <input type="radio" class="btn-check" name="modalLabelType" id="modalTypeQR" value="qr" checked>
                        <label class="btn btn-outline-primary" for="modalTypeQR">
                            <i class="bi bi-qr-code me-1"></i>QR
                        </label>
                        <input type="radio" class="btn-check" name="modalLabelType" id="modalTypeBarcode" value="barcode">
                        <label class="btn btn-outline-primary" for="modalTypeBarcode">
                            <i class="bi bi-upc-scan me-1"></i>Barcode
                        </label>
                    </div>
                </div>
                <div id="modalLabelPreviewQR">
                    <img src="" alt="QR Code" class="img-fluid" id="modalQRImage" style="max-width: 180px;">
                    <div class="mt-1 fw-bold font-monospace small" id="modalAssetCode"></div>
                    <div class="text-muted small text-truncate px-2" id="modalAssetName"></div>
                </div>
                <div id="modalLabelPreviewBarcode" style="display:none">
                    <img src="" alt="Barcode" class="img-fluid" id="modalBarcodeImage" style="max-width: 200px;">
                    <div class="mt-1 fw-bold font-monospace small" id="modalBarcodeAssetCode"></div>
                    <div class="text-muted small text-truncate px-2" id="modalBarcodeAssetName"></div>
                </div>
                <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="modalPrintBtn">
                        <i class="bi bi-printer me-1"></i>Cetak
                    </button>
                    <a href="#" id="modalDownloadBtn" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-box-seam-fill me-2 text-primary"></i>Detail Aset
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Aset
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editModalBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Batal
                </button>
                <button type="submit" form="editAssetForm" class="btn btn-sm btn-warning" id="editModalSubmit">
                    <i class="bi bi-floppy2 me-1"></i>Perbarui Aset
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Tambah Aset Baru
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createModalBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Batal
                </button>
                <button type="submit" form="createAssetForm" class="btn btn-sm btn-primary" id="createModalSubmit">
                    <i class="bi bi-check-lg me-1"></i>Simpan Aset
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold text-danger">
                    <i class="bi bi-trash3-fill me-2"></i>Hapus Aset
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    Yakin ingin menghapus aset
                    <span class="fw-bold font-monospace" id="deleteAssetName"></span>?
                </p>
                <p class="small text-danger mb-0 mt-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div id="deleteModalError" class="alert alert-danger d-none mt-3 mb-0 small py-2"></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

@if ($bulkEnabled)
{{-- Modal Edit Massal --}}
<div class="modal fade" id="bulkEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Massal Aset
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info d-flex align-items-start gap-2 py-2 small mb-4">
                    <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                    <span>
                        <span class="fw-semibold" id="bulkModalCount">0</span> aset akan diperbarui.
                        Isi hanya field yang ingin diubah — field kosong tidak akan menyentuh data aset.
                    </span>
                </div>
                <form id="bulkEditForm" action="{{ route('assets.bulk-update') }}" method="POST" novalidate>
                    @csrf
                    <div id="bulkIdsContainer"></div>
                    <div class="row g-3">
                        {{-- Status --}}
                        <div class="col-md-6">
                            <label for="bulk_status" class="form-label fw-semibold">Status</label>
                            <select id="bulk_status" name="status" class="form-select" data-searchable>
                                <option value="">— Tidak diubah —</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Lokasi --}}
                        <div class="col-md-6">
                            <label for="bulk_location_id" class="form-label fw-semibold">Lokasi</label>
                            <select id="bulk_location_id" name="location_id" class="form-select" data-searchable>
                                <option value="">— Tidak diubah —</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pengguna/Karyawan --}}
                        <div class="col-md-6">
                            <label for="bulk_employee_id" class="form-label fw-semibold">Pengguna / Karyawan</label>
                            <select id="bulk_employee_id" name="employee_id" class="form-select" data-searchable>
                                <option value="">— Tidak diubah —</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }}
                                        @if ($employee->department)
                                            ({{ $employee->department }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tanggal Mutasi --}}
                        <div class="col-md-6">
                            <label for="bulk_mutation_date" class="form-label fw-semibold">Tanggal Mutasi</label>
                            <input type="date" id="bulk_mutation_date" name="mutation_date" class="form-control">
                        </div>

                        @can('asset.edit')
                        {{-- Kategori --}}
                        <div class="col-md-6">
                            <label for="bulk_asset_category_id" class="form-label fw-semibold">Kategori</label>
                            <select id="bulk_asset_category_id" name="asset_category_id" class="form-select" data-searchable>
                                <option value="">— Tidak diubah —</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                        @if ($category->abbreviation)
                                            ({{ $category->abbreviation }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">
                                <i class="bi bi-info-circle me-1"></i>Mengubah kategori akan meregenerasi kode aset.
                            </div>
                        </div>

                        {{-- Merek --}}
                        <div class="col-md-6">
                            <label for="bulk_brand_id" class="form-label fw-semibold">Merek</label>
                            <select id="bulk_brand_id" name="brand_id" class="form-select" data-searchable>
                                <option value="">— Tidak diubah —</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Vendor --}}
                        <div class="col-md-6">
                            <label for="bulk_vendor_id" class="form-label fw-semibold">Vendor</label>
                            <select id="bulk_vendor_id" name="vendor_id" class="form-select" data-searchable>
                                <option value="">— Tidak diubah —</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Model --}}
                        <div class="col-md-6">
                            <label for="bulk_model" class="form-label fw-semibold">Model</label>
                            <input type="text" id="bulk_model" name="model" class="form-control"
                                   placeholder="Tidak diubah jika kosong">
                        </div>

                        {{-- Harga Pembelian --}}
                        <div class="col-md-6">
                            <label for="bulk_purchase_price" class="form-label fw-semibold">Harga Pembelian</label>
                            <input type="number" id="bulk_purchase_price" name="purchase_price" min="0" step="0.01"
                                   class="form-control" placeholder="Tidak diubah jika kosong">
                        </div>

                        {{-- Tanggal Pembelian --}}
                        <div class="col-md-6">
                            <label for="bulk_purchase_date" class="form-label fw-semibold">Tanggal Pembelian</label>
                            <input type="date" id="bulk_purchase_date" name="purchase_date" class="form-control">
                        </div>
                        @endcan

                        {{-- Catatan --}}
                        <div class="col-12">
                            <label for="bulk_notes" class="form-label fw-semibold">Catatan</label>
                            <textarea id="bulk_notes" name="notes" rows="2" class="form-control"
                                      placeholder="Tidak diubah jika kosong"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Batal
                </button>
                <button type="submit" form="bulkEditForm" class="btn btn-sm btn-warning" id="bulkEditModalSubmit">
                    <i class="bi bi-floppy2 me-1"></i>Terapkan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@include('assets._column_settings')
@endsection

@push('scripts')
<script>
const columnSettingsSaveUrl = '{{ route('assets.save-columns') }}';
const columnSettingsDefault = @json(\App\Http\Controllers\AssetController::DEFAULT_COLUMNS);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// ── Modal Label QR/Barcode (existing) ──
document.querySelectorAll('.show-label').forEach(btn => {
    btn.addEventListener('click', function() {
        const qrUrl        = this.dataset.qrUrl;
        const barcodeUrl   = this.dataset.barcodeUrl;
        const assetCode    = this.dataset.assetCode;
        const assetName    = this.dataset.assetName;

        document.getElementById('modalQRImage').src           = qrUrl;
        document.getElementById('modalBarcodeImage').src      = barcodeUrl;
        document.getElementById('modalAssetCode').textContent = assetCode;
        document.querySelectorAll('#modalBarcodeAssetCode, #modalAssetCode').forEach(el => el.textContent = assetCode);
        document.querySelectorAll('#modalBarcodeAssetName, #modalAssetName').forEach(el => el.textContent = assetName);

        currentPrintUrl = this.dataset.printUrl || '';
        currentType     = 'qr';
        document.getElementById('modalTypeQR').checked = true;
        document.getElementById('modalLabelPreviewQR').style.display    = '';
        document.getElementById('modalLabelPreviewBarcode').style.display = 'none';

        const modal = new bootstrap.Modal(document.getElementById('labelModal'));
        modal.show();
    });
});

document.querySelectorAll('input[name="modalLabelType"]').forEach(radio => {
    radio.addEventListener('change', function() {
        currentType = this.value;
        const isQR = currentType === 'qr';
        document.getElementById('modalLabelPreviewQR').style.display    = isQR ? '' : 'none';
        document.getElementById('modalLabelPreviewBarcode').style.display = isQR ? 'none' : '';
    });
});

let currentPrintUrl = '';
let currentType = 'qr';
document.getElementById('modalPrintBtn')?.addEventListener('click', () => {
    if (currentPrintUrl) {
        window.open(currentPrintUrl + '?type=' + currentType + '&count=1&print=1', '_blank');
    }
});
document.getElementById('modalDownloadBtn')?.addEventListener('click', function() {
    const qrUrl = document.getElementById('modalQRImage').src;
    const barcodeUrl = document.getElementById('modalBarcodeImage').src;
    this.href = currentType === 'qr' ? qrUrl : barcodeUrl;
    this.download = document.getElementById('modalAssetCode').textContent + '-' + currentType + '.svg';
});

// ══════════════════════════════════════════════════════
// Modal Detail / Edit / Create / Hapus (AJAX, tanpa pindah halaman)
// ══════════════════════════════════════════════════════

function modalGet(url) {
    return fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    });
}

function setModalLoading(body) {
    body.innerHTML = '<div class="text-center py-5 text-muted">' +
        '<div class="spinner-border text-primary mb-2" role="status"></div>' +
        '<div class="small">Memuat...</div></div>';
}

function initSearchableWithin(container) {
    container.querySelectorAll('select[data-searchable]').forEach(el => {
        if (window.initSearchableSelect) window.initSearchableSelect(el);
    });
}

function initImagePreview(container) {
    const imageInput = container.querySelector('#image');
    if (!imageInput) return;
    const previewBox = container.querySelector('#new-image-preview');
    const previewImg = container.querySelector('#new-image-preview-img');
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                if (previewImg) previewImg.src = e.target.result;
                if (previewBox) previewBox.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            if (previewBox) previewBox.classList.add('d-none');
            if (previewImg) previewImg.src = '#';
        }
    });
}

function initDetailLabelControls(container) {
    const typeRadios = container.querySelectorAll('input[name="labelType"]');
    if (typeRadios.length === 0) return;
    const previewQR = container.querySelector('#labelPreviewQR');
    const previewBarcode = container.querySelector('#labelPreviewBarcode');
    const downloadBtn = container.querySelector('#downloadLabelBtn');

    function updateLabel() {
        const type = container.querySelector('input[name="labelType"]:checked')?.value || 'qr';
        if (previewQR) previewQR.style.display = type === 'qr' ? 'block' : 'none';
        if (previewBarcode) previewBarcode.style.display = type === 'barcode' ? 'block' : 'none';
        if (downloadBtn) {
            downloadBtn.href = type === 'qr' ? downloadBtn.dataset.qrUrl : downloadBtn.dataset.barcodeUrl;
            downloadBtn.download = downloadBtn.dataset.assetCode + '-' + type + '.svg';
        }
    }
    typeRadios.forEach(r => r.addEventListener('change', updateLabel));
    updateLabel();

    const printDropdown = container.querySelector('#printDropdown');
    const printUrl = printDropdown?.dataset.printUrl || '';
    printDropdown?.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const count = this.dataset.count;
            const type = container.querySelector('input[name="labelType"]:checked')?.value || 'qr';
            window.open(printUrl + '?type=' + type + '&count=' + count + '&print=1', '_blank', 'width=800,height=600');
        });
    });
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function clearFormErrors(form) {
    form.querySelectorAll('.alert.alert-danger').forEach(a => a.remove());
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

function showFormAlert(form, message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show small py-2 mb-4';
    alert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>' + escapeHtml(message);
    form.prepend(alert);
}

function renderFormErrors(form, errors) {
    const errList = Object.values(errors).flat();
    if (errList.length) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show small py-2 mb-4';
        alert.innerHTML = '<strong>Terdapat ' + errList.length + ' kesalahan pada formulir:</strong><ul class="mb-0 mt-1 ps-3">' +
            errList.map(m => '<li>' + escapeHtml(m) + '</li>').join('') + '</ul>';
        form.prepend(alert);
    }

    Object.keys(errors).forEach(key => {
        const field = form.elements[key];
        if (!field) return;
        field.classList.add('is-invalid');
        const wrapper = field.closest('.searchable-wrapper');
        wrapper?.querySelector('.searchable-input')?.classList.add('is-invalid');

        let fb = field.nextElementSibling;
        if (!fb || !fb.classList.contains('invalid-feedback')) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback d-block';
            field.parentNode.insertBefore(fb, field.nextSibling);
        }
        fb.textContent = errors[key][0];
    });
}

function bindAssetForm(form, modalId) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearFormErrors(form);
        const submitBtn = document.getElementById(modalId + 'ModalSubmit');
        const fd = new FormData(form);
        if (submitBtn) submitBtn.disabled = true;

        fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: fd
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                location.reload();
            } else if (data.errors) {
                if (submitBtn) submitBtn.disabled = false;
                renderFormErrors(form, data.errors);
            } else if (data.error) {
                if (submitBtn) submitBtn.disabled = false;
                showFormAlert(form, data.error);
            } else {
                if (submitBtn) submitBtn.disabled = false;
                showFormAlert(form, 'Terjadi kesalahan. Silakan coba lagi.');
            }
        })
        .catch(() => {
            if (submitBtn) submitBtn.disabled = false;
            showFormAlert(form, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        });
    });
}

function openDetailModal(url) {
    const body = document.getElementById('detailModalBody');
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal'));
    setModalLoading(body);
    modal.show();
    modalGet(url)
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat detail.');
            return res.text();
        })
        .then(html => {
            body.innerHTML = html;
            initDetailLabelControls(body);
        })
        .catch(() => {
            body.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>' +
                '<span class="fw-medium">Gagal memuat detail aset.</span></div>';
        });
}

function openEditModal(url) {
    const body = document.getElementById('editModalBody');
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal'));
    setModalLoading(body);
    modal.show();
    modalGet(url)
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat form.');
            return res.text();
        })
        .then(html => {
            body.innerHTML = html;
            initSearchableWithin(body);
            initImagePreview(body);
            const form = body.querySelector('#editAssetForm');
            if (form) bindAssetForm(form, 'edit');
        })
        .catch(() => {
            body.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>' +
                '<span class="fw-medium">Gagal memuat form edit.</span></div>';
        });
}

function openCreateModal(url) {
    const body = document.getElementById('createModalBody');
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('createModal'));
    setModalLoading(body);
    modal.show();
    modalGet(url)
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat form.');
            return res.text();
        })
        .then(html => {
            body.innerHTML = html;
            initSearchableWithin(body);
            initImagePreview(body);
            const form = body.querySelector('#createAssetForm');
            if (form) bindAssetForm(form, 'create');
        })
        .catch(() => {
            body.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>' +
                '<span class="fw-medium">Gagal memuat form tambah aset.</span></div>';
        });
}

let currentDeleteUrl = '';
function openDeleteModal(url, name) {
    document.getElementById('deleteAssetName').textContent = name;
    const errBox = document.getElementById('deleteModalError');
    errBox.classList.add('d-none');
    errBox.textContent = '';
    currentDeleteUrl = url;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!currentDeleteUrl) return;
    this.disabled = true;
    const errBox = document.getElementById('deleteModalError');
    errBox.classList.add('d-none');
    errBox.textContent = '';

    fetch(currentDeleteUrl, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (res.ok) {
            location.reload();
        } else {
            this.disabled = false;
            errBox.textContent = data.error || 'Gagal menghapus aset. Silakan coba lagi.';
            errBox.classList.remove('d-none');
        }
    })
    .catch(() => {
        this.disabled = false;
        errBox.textContent = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
        errBox.classList.remove('d-none');
    });
});

// ══════════════════════════════════════════════════════
// Bulk Edit (checkbox per halaman)
// ══════════════════════════════════════════════════════

const bulkBar        = document.getElementById('bulkBar');
const bulkCountEl    = document.getElementById('bulkCount');
const selectAllEl    = document.getElementById('selectAllAsset');
const openBulkBtn    = document.getElementById('openBulkEditBtn');
const clearBulkBtn   = document.getElementById('clearBulkSelectionBtn');
const bulkEditForm   = document.getElementById('bulkEditForm');
const bulkIdsWrap    = document.getElementById('bulkIdsContainer');
const bulkModalCount = document.getElementById('bulkModalCount');

function getSelectedAssetIds() {
    return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
}

function updateBulkBar() {
    if (!bulkBar) return;
    const ids = getSelectedAssetIds();
    bulkCountEl.textContent = ids.length;
    bulkBar.classList.toggle('d-none', ids.length === 0);
    bulkBar.classList.toggle('d-flex', ids.length > 0);
    openBulkBtn.disabled = ids.length === 0;
    if (selectAllEl) {
        const boxes = document.querySelectorAll('.row-checkbox');
        selectAllEl.checked = boxes.length > 0 && boxes.length === document.querySelectorAll('.row-checkbox:checked').length;
    }
}

selectAllEl?.addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

document.querySelectorAll('.row-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

clearBulkBtn?.addEventListener('click', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    if (selectAllEl) selectAllEl.checked = false;
    updateBulkBar();
});

openBulkBtn?.addEventListener('click', function() {
    const ids = getSelectedAssetIds();
    if (ids.length === 0) return;

    bulkModalCount.textContent = ids.length;

    if (bulkEditForm) bulkEditForm.reset();
    document.querySelectorAll('#bulkEditModal .searchable-wrapper').forEach(w => {
        const sel = w.querySelector('select');
        const input = w.querySelector('.searchable-input');
        if (sel && input) {
            const opt = sel.options[sel.selectedIndex];
            input.value = (opt && sel.value !== '') ? opt.text : '';
        }
    });

    bulkIdsWrap.innerHTML = ids.map(id => '<input type="hidden" name="ids[]" value="' + id + '">').join('');

    initSearchableWithin(document.getElementById('bulkEditModal'));
    bootstrap.Modal.getOrCreateInstance(document.getElementById('bulkEditModal')).show();
});

const bulkForm = document.getElementById('bulkEditForm');
if (bulkForm) {
    bulkForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearFormErrors(bulkForm);
        const submitBtn = document.getElementById('bulkEditModalSubmit');
        if (submitBtn) submitBtn.disabled = true;

        fetch(bulkForm.getAttribute('action'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: new FormData(bulkForm)
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                location.reload();
            } else if (data.errors) {
                if (submitBtn) submitBtn.disabled = false;
                renderFormErrors(bulkForm, data.errors);
            } else if (data.error) {
                if (submitBtn) submitBtn.disabled = false;
                showFormAlert(bulkForm, data.error);
            } else {
                if (submitBtn) submitBtn.disabled = false;
                showFormAlert(bulkForm, 'Terjadi kesalahan. Silakan coba lagi.');
            }
        })
        .catch(() => {
            if (submitBtn) submitBtn.disabled = false;
            showFormAlert(bulkForm, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        });
    });
}

// ── Event delegation: tombol di baris tabel + konten yang di-inject di modal detail ──
document.addEventListener('click', function(e) {
    const createBtn = e.target.closest('.js-open-create');
    if (createBtn) { e.preventDefault(); openCreateModal(createBtn.dataset.createUrl); return; }

    const detailBtn = e.target.closest('.js-open-detail');
    if (detailBtn) { e.preventDefault(); openDetailModal(detailBtn.dataset.detailUrl); return; }

    const editBtn = e.target.closest('.js-open-edit');
    if (editBtn) { e.preventDefault(); openEditModal(editBtn.dataset.editUrl); return; }

    const deleteBtn = e.target.closest('.js-open-delete');
    if (deleteBtn) { e.preventDefault(); openDeleteModal(deleteBtn.dataset.deleteUrl, deleteBtn.dataset.name); return; }

    // Tombol Edit di dalam modal detail
    const injectEdit = e.target.closest('.js-open-edit-modal');
    if (injectEdit) {
        e.preventDefault();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal'))?.hide();
        setTimeout(() => openEditModal(injectEdit.dataset.editUrl), 250);
        return;
    }

    // Form Hapus di dalam modal detail
    const injectDelete = e.target.closest('.js-open-delete-modal');
    if (injectDelete) {
        e.preventDefault();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal'))?.hide();
        setTimeout(() => openDeleteModal(injectDelete.dataset.deleteUrl, injectDelete.dataset.name), 250);
        return;
    }

    // Tombol Kembali di dalam modal detail
    const backBtn = e.target.closest('.js-back-from-detail');
    if (backBtn) {
        e.preventDefault();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal'))?.hide();
    }
});
</script>
<script src="{{ asset('js/column-settings.js') }}"></script>
@endpush
