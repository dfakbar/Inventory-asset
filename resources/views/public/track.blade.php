<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check Asset — {{ config('app.name', 'AssetMS') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous"
          referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-bar {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: .65rem 1.25rem;
        }
        .track-container {
            flex: 1;
            max-width: 780px;
            margin: 2rem auto;
            width: 100%;
            padding: 0 1rem;
        }
        .search-card {
            background: #fff;
            border-radius: .75rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1px solid #e9ecef;
        }
        .detail-card, .timeline-card {
            background: #fff;
            border-radius: .75rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        .detail-card .card-header, .timeline-card .card-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            padding: .85rem 1.25rem;
        }
        .detail-table th {
            width: 35%;
            color: #6c757d;
            font-weight: 500;
            font-size: .85rem;
            padding: .65rem 1.25rem;
            background: #fafbfc;
        }
        .detail-table td {
            padding: .65rem 1.25rem;
            font-size: .9rem;
        }
        .detail-table tr:not(:last-child) th,
        .detail-table tr:not(:last-child) td {
            border-bottom: 1px solid #f0f0f0;
        }
        .timeline {
            position: relative;
            padding: 1rem 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 2.15rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding: 0 0 1.5rem 3.75rem;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-bullet {
            position: absolute;
            left: 1.45rem;
            width: 1.15rem;
            height: 1.15rem;
            border-radius: 50%;
            background: #0d6efd;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #0d6efd;
            top: .25rem;
        }
        .timeline-date {
            font-size: .78rem;
            color: #6c757d;
        }
        .timeline-label {
            font-size: .82rem;
            font-weight: 600;
            color: #495057;
        }
        .timeline-change {
            font-size: .85rem;
        }
        .timeline-actor {
            font-size: .78rem;
            color: #6c757d;
        }
        .not-found-icon {
            font-size: 4rem;
            opacity: .3;
        }
        .pagination {
            justify-content: center;
            margin-top: 1rem;
        }
        .camera-container {
            background: #fff;
            border-radius: .75rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1px solid #e9ecef;
        }
        #cameraReader {
            overflow: hidden;
            border-radius: .5rem;
        }
        #cameraReader video {
            max-width: 100%;
            height: auto;
        }
        .footer-text {
            font-size: .78rem;
            color: #adb5bd;
            text-align: center;
            padding: 1rem;
        }
    </style>
</head>
<body>

<div class="top-bar d-flex justify-content-between align-items-center">
    <a href="{{ route('login') }}" class="text-decoration-none text-primary small">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Halaman Login
    </a>
    <span class="small text-muted">{{ config('app.name', 'AssetMS') }}</span>
</div>

<div class="track-container">

    {{-- ── Search ── --}}
    <div class="search-card p-3 mb-4">
        <form method="GET" action="{{ route('public.track') }}" class="row g-2 align-items-center">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Masukkan Kode Aset atau Serial Number"
                           value="{{ $search ?? '' }}"
                           required
                           autofocus>
                </div>
            </div>
            <div class="col-auto d-flex gap-1">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                <button type="button" class="btn btn-outline-secondary px-3" id="btnScan" title="Scan barcode via kamera">
                    <i class="bi bi-camera me-1"></i>Scan
                </button>
            </div>
        </form>
    </div>

    {{-- ── Camera Scanner ── --}}
    <div id="cameraContainer" style="display:none;" class="mb-4">
        <div class="camera-container p-3 text-center">
            <div id="cameraReader" class="mx-auto" style="max-width:400px;"></div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="btnStopScan">
                <i class="bi bi-x-circle me-1"></i>Tutup Kamera
            </button>
        </div>
    </div>

    {{-- ── Results ── --}}
    @if ($search)
        @if ($asset)

            {{-- ── Detail Aset ── --}}
            <div class="detail-card mb-4">
                <div class="card-header">
                    <i class="bi bi-box-seam-fill text-primary me-2"></i>Detail Aset
                </div>
                <table class="table detail-table mb-0">
                    <tbody>
                        <tr>
                            <th>Kode Aset</th>
                            <td class="font-monospace fw-bold text-primary">{{ $asset->asset_code }}</td>
                        </tr>
                        <tr>
                            <th>Nama Aset</th>
                            <td class="fw-semibold">{{ $asset->name }}</td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>{{ $asset->category?->name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Merek / Model</th>
                            <td>{{ $asset->brand?->name ?: '—' }} {{ $asset->model ? '/ ' . $asset->model : '' }}</td>
                        </tr>
                        <tr>
                            <th>Serial Number</th>
                            <td class="font-monospace">{{ $asset->serial_number ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="{{ $asset->status->badgeClass() }} d-inline-flex align-items-center gap-1 px-2 py-1">
                                    <i class="bi {{ $asset->status->icon() }}"></i>
                                    {{ $asset->status->label() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Lokasi</th>
                            <td>{{ $asset->location?->name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>PIC (System)</th>
                            <td>{{ $asset->assignedUser?->name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Pengguna / Karyawan</th>
                            <td>{{ $asset->employee?->name ?: '—' }}
                                @if ($asset->employee && $asset->employee->department)
                                    <span class="text-muted small">({{ $asset->employee->department }})</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- ── Riwayat Mutasi ── --}}
            <div class="timeline-card">
                <div class="card-header">
                    <i class="bi bi-arrow-left-right text-primary me-2"></i>Riwayat Mutasi
                </div>
                <div class="card-body p-0">
                    @if ($mutations->count())
                        <div class="timeline">
                            @foreach ($mutations as $log)
                                <div class="timeline-item">
                                    <div class="timeline-bullet"></div>
                                    <div class="timeline-date">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $log->mutation_date ? $log->mutation_date->translatedFormat('d M Y') : $log->created_at->format('d M Y') }}
                                    </div>

                                    @if ($log->from_location_id || $log->to_location_id)
                                        <div class="timeline-label mt-1">Lokasi</div>
                                        <div class="timeline-change">
                                            <span class="text-danger text-decoration-line-through">{{ $log->fromLocation?->name ?? '-' }}</span>
                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                            <span class="text-success">{{ $log->toLocation?->name ?? '-' }}</span>
                                        </div>
                                    @endif

                                    @if ($log->from_assigned_to || $log->to_assigned_to)
                                        <div class="timeline-label mt-1">PIC (System)</div>
                                        <div class="timeline-change">
                                            <span class="text-danger text-decoration-line-through">{{ $log->fromAssignedUser?->name ?? '-' }}</span>
                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                            <span class="text-success">{{ $log->toAssignedUser?->name ?? '-' }}</span>
                                        </div>
                                    @endif

                                    @if ($log->from_employee_id || $log->to_employee_id)
                                        <div class="timeline-label mt-1">Karyawan</div>
                                        <div class="timeline-change">
                                            <span class="text-danger text-decoration-line-through">{{ $log->fromEmployee?->name ?? '-' }}</span>
                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                            <span class="text-success">{{ $log->toEmployee?->name ?? '-' }}</span>
                                        </div>
                                    @endif

                                    @if ($log->from_status || $log->to_status)
                                        <div class="timeline-label mt-1">Status</div>
                                        <div class="timeline-change">
                                            <span class="text-danger text-decoration-line-through">{{ $log->from_status ?? '-' }}</span>
                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                            <span class="text-success">{{ $log->to_status ?? '-' }}</span>
                                        </div>
                                    @endif

                                    @if ($log->notes)
                                        <div class="timeline-change text-muted mt-1 small">
                                            <i class="bi bi-chat-dots me-1"></i>{{ $log->notes }}
                                        </div>
                                    @endif

                                    <div class="timeline-actor mt-1">
                                        <i class="bi bi-person me-1"></i>{{ $log->performedBy?->name ?? 'System' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($mutations instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="p-3 border-top">
                                {{ $mutations->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox not-found-icon d-block mb-2"></i>
                            Belum ada riwayat mutasi untuk aset ini.
                        </div>
                    @endif
                </div>
            </div>

        @else

            {{-- ── Not Found ── --}}
            <div class="detail-card text-center py-5">
                <i class="bi bi-search-heart not-found-icon d-block mb-3"></i>
                <h5 class="text-muted">Aset Tidak Ditemukan</h5>
                <p class="text-muted small mb-0">
                    Tidak ada aset dengan kode atau serial number
                    <strong class="text-dark">&quot;{{ $search }}&quot;</strong>.
                </p>
                <p class="text-muted small">
                    Periksa kembali kode aset atau serial number yang dimasukkan.
                </p>
            </div>

        @endif
    @endif

    <div class="footer-text">
        &copy; {{ date('Y') }} {{ config('app.name', 'AssetMS') }}
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
        defer></script>
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
document.getElementById('btnScan')?.addEventListener('click', () => {
    const reader = new Html5Qrcode("cameraReader");
    document.getElementById('cameraContainer').style.display = '';
    reader.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 150 } },
        (decodedText) => {
            reader.stop().catch(() => {});
            document.getElementById('cameraContainer').style.display = 'none';
            document.querySelector('input[name="search"]').value = decodedText;
            document.querySelector('form').submit();
        }
    ).catch(err => {
        alert('Tidak dapat mengakses kamera: ' + (err.message || err));
        document.getElementById('cameraContainer').style.display = 'none';
    });
    document.getElementById('btnStopScan')?.addEventListener('click', () => {
        reader.stop().catch(() => {});
        document.getElementById('cameraContainer').style.display = 'none';
    });
});
</script>
</body>
</html>
