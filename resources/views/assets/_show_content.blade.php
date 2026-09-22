{{--
    Partial: assets/_show_content.blade.php
    Isi halaman detail aset — dipakai oleh show.blade.php (halaman penuh)
    dan oleh modal detail di assets/index.blade.php (via AJAX).
    Tombol Edit/Hapus/Kembali dilengkapi kelas `js-*` agar dapat ditangkap
    oleh delegation handler di index page; di halaman show ia tetap berfungsi
    sebagai link/form biasa.
--}}

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
        <a href="{{ route('assets.edit', $asset) }}"
           class="btn btn-warning js-open-edit-modal"
           data-edit-url="{{ route('assets.edit', $asset) }}">
            <i class="bi bi-pencil-fill me-1"></i>Edit Aset
        </a>
        @endif

        @can('asset.delete')
        <form action="{{ route('assets.destroy', $asset) }}"
              method="POST"
              class="js-open-delete-modal"
              data-delete-url="{{ route('assets.destroy', $asset) }}"
              data-name="{{ $asset->name }}"
              onsubmit="return confirm('Hapus aset \'{{ addslashes($asset->name) }}\'?\nTindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash3-fill me-1"></i>Hapus
            </button>
        </form>
        @endcan

        @can('document.create')
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-clipboard-check me-1"></i>Dokumen SOP
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('documents.create', ['type' => 'registrasi', 'asset_id' => $asset->id]) }}">
                        <i class="bi bi-clipboard-check me-2"></i>Form Registrasi Aset
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('documents.create', ['type' => 'tanda_terima', 'asset_id' => $asset->id]) }}">
                        <i class="bi bi-table me-2"></i>Form Tanda Terima Aset
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('documents.create', ['type' => 'permohonan_mutasi', 'asset_id' => $asset->id]) }}">
                        <i class="bi bi-send me-2"></i>Form Permohonan Mutasi
                    </a>
                </li>
            </ul>
        </div>
        @endcan

        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary js-back-from-detail">
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
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">PIC (System)</th>
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
                        <tr>
                            <th class="ps-3 py-3 text-muted fw-medium small">Pengguna / Karyawan</th>
                            <td class="py-3 pe-3">
                                @if ($asset->employee)
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <span class="avatar bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                                              style="width:28px;height:28px;font-size:.75rem">
                                            {{ strtoupper(substr($asset->employee->name, 0, 1)) }}
                                        </span>
                                        {{ $asset->employee->name }}
                                        @if ($asset->employee->department)
                                            <span class="text-muted small">({{ $asset->employee->department }})</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
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
                            <td class="py-3 pe-3">{{ $asset->brand?->name ?: '—' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Vendor</th>
                            <td class="py-3 pe-3">{{ $asset->vendor?->name ?: '—' }}</td>
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
                        <tr>
                            <th class="ps-3 py-3 text-muted fw-medium small">MAC Address</th>
                            <td class="py-3 pe-3">
                                @if ($asset->mac_address)
                                    <span class="font-monospace">{{ $asset->mac_address }}</span>
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

        {{-- Card: Riwayat Maintenance & Upgrade --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-info text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-white">
                    <i class="bi bi-tools me-2"></i>Riwayat Maintenance & Upgrade Komponen
                </h6>
                @if(auth()->user()->can('asset.edit') || auth()->user()->can('asset.mutate'))
                <button type="button" class="btn btn-sm btn-light text-info fw-semibold" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                    <i class="bi bi-plus-lg me-1"></i>Tambah
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                @if ($asset->maintenances->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-info-circle fs-4 d-block mb-1"></i>
                        Belum ada catatan maintenance atau upgrade komponen.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Tanggal</th>
                                    <th>Aksi & Komponen</th>
                                    <th>Spesifikasi</th>
                                    <th>Biaya</th>
                                    <th>Pelaksana</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($asset->maintenances as $main)
                                <tr>
                                    <td class="ps-3 text-nowrap">
                                        {{ $main->maintenance_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td>
                                        @if ($main->action_type === 'addition')
                                            <span class="badge bg-success bg-opacity-15 text-success border border-success-subtle mb-1">
                                                <i class="bi bi-plus-circle me-1"></i>Penambahan / Upgrade
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-15 text-danger border border-danger-subtle mb-1">
                                                <i class="bi bi-dash-circle me-1"></i>Pengurangan / Cabut
                                            </span>
                                        @endif
                                        <div class="fw-bold">{{ $main->component_name }}</div>
                                    </td>
                                    <td>
                                        @if ($main->previous_spec)
                                            <div class="text-muted text-decoration-line-through small">{{ $main->previous_spec }}</div>
                                        @endif
                                        <div class="fw-semibold text-dark"><i class="bi bi-arrow-right-short text-primary"></i> {{ $main->new_spec }}</div>
                                        @if ($main->notes)
                                            <div class="text-muted small mt-1 font-italic">{{ $main->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($main->cost)
                                            Rp {{ number_format($main->cost, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $main->performedBy?->name ?? 'System' }}</td>
                                    <td class="text-end pe-3">
                                        @if(auth()->user()->can('asset.edit') || auth()->user()->can('asset.mutate'))
                                        <form action="{{ route('assets.maintenances.destroy', [$asset, $main]) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus catatan maintenance ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Include Modal --}}
        @include('assets._maintenance_modal', ['asset' => $asset])

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

        {{-- Card: QR Code / Barcode --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header py-2 px-3 bg-light">
                <h6 class="mb-0 fw-semibold text-secondary">
                    <i class="bi bi-upc-scan me-2"></i>Label Aset
                </h6>
            </div>
            <div class="card-body text-center py-3">
                <div class="mb-2">
                    <div class="btn-group btn-group-sm" role="group" id="labelTypeToggle">
                        <input type="radio" class="btn-check" name="labelType" id="typeQR" value="qr" checked>
                        <label class="btn btn-outline-primary" for="typeQR">
                            <i class="bi bi-qr-code me-1"></i>QR
                        </label>
                        <input type="radio" class="btn-check" name="labelType" id="typeBarcode" value="barcode">
                        <label class="btn btn-outline-primary" for="typeBarcode">
                            <i class="bi bi-upc-scan me-1"></i>Barcode
                        </label>
                    </div>
                </div>

                <div id="labelPreviewQR">
                    <img src="{{ route('assets.qr-code', $asset) }}"
                         alt="QR Code {{ $asset->asset_code }}"
                         class="img-fluid"
                         style="max-width: 180px;">
                    <div class="mt-1 fw-bold font-monospace small">{{ $asset->asset_code }}</div>
                    <div class="text-muted small text-truncate px-2">{{ $asset->name }}</div>
                </div>
                <div id="labelPreviewBarcode" style="display:none">
                    <img src="{{ route('assets.barcode', $asset) }}"
                         alt="Barcode {{ $asset->asset_code }}"
                         class="img-fluid"
                         style="max-width: 200px;">
                    <div class="mt-1 fw-bold font-monospace small">{{ $asset->asset_code }}</div>
                    <div class="text-muted small text-truncate px-2">{{ $asset->name }}</div>
                </div>

                <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-printer me-1"></i>Cetak
                        </button>
                        <ul class="dropdown-menu" id="printDropdown" data-print-url="{{ route('assets.print-code', $asset) }}">
                            @for ($i = 1; $i <= 4; $i++)
                            <li><a class="dropdown-item" href="#" data-count="{{ $i }}">{{ $i }} Label</a></li>
                            @endfor
                        </ul>
                    </div>
                    <a href="#"
                       id="downloadLabelBtn"
                       class="btn btn-sm btn-outline-secondary"
                       data-qr-url="{{ route('assets.qr-code', $asset) }}"
                       data-barcode-url="{{ route('assets.barcode', $asset) }}"
                       data-asset-code="{{ $asset->asset_code }}">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                </div>
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
