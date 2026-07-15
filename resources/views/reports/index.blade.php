@extends('layouts.app')

@section('title', 'Laporan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Laporan</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Laporan & Ekspor Data
        </h4>
        <p class="text-muted small mb-0 mt-1">Generate laporan PDF dan ekspor data aset</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="display-5 text-primary mb-3">
                    <i class="bi bi-filetype-csv"></i>
                </div>
                <h5 class="fw-bold">Ekspor CSV</h5>
                <p class="text-muted small">Download seluruh data aset dalam format CSV untuk dibuka di Excel.</p>
                <a href="{{ route('assets.export.csv', request()->query()) }}" class="btn btn-outline-primary w-100">
                    <i class="bi bi-download me-1"></i>Download CSV
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="display-5 text-success mb-3">
                    <i class="bi bi-file-earmark-pdf"></i>
                </div>
                <h5 class="fw-bold">Laporan Semua Aset</h5>
                <p class="text-muted small">Download PDF daftar aset dengan filter status, kategori, dan lokasi.</p>
                <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="collapse" data-bs-target="#reportFilter">
                    <i class="bi bi-funnel me-1"></i>Buat Laporan
                </button>
                <div class="collapse mt-3" id="reportFilter">
                    <form method="GET" action="{{ route('reports.assets-pdf') }}" target="_blank" class="text-start">
                        <div class="mb-2">
                            <label class="form-label small">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">— Semua —</option>
                                @foreach (\App\Enums\AssetStatus::cases() as $s)
                                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Kategori</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">— Semua —</option>
                                @foreach (\App\Models\AssetCategory::orderBy('name')->get() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Generate PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="display-5 text-warning mb-3">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
                <h5 class="fw-bold">Laporan Kategori</h5>
                <p class="text-muted small">Download PDF rekap jumlah aset per kategori.</p>
                <a href="{{ route('reports.categories-pdf') }}" target="_blank" class="btn btn-outline-warning w-100">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-upload me-2"></i>Import Data Aset (CSV)
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('assets.import.csv') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                        <div class="form-text small text-muted">
                            Format: CSV dengan header <code>Kode Aset,Nama,Kategori,Merek,Model,Serial Number,Lokasi,Vendor,Status,Tanggal Pembelian,Harga Pembelian,Jumlah,Catatan</code>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Import CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
