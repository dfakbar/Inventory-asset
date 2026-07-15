@extends('layouts.app')

@section('title', 'Peminjaman Aset')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Peminjaman Aset</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-arrow-left-right text-primary me-2"></i>Peminjaman Aset
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola check-out dan check-in aset</p>
    </div>
    @can('loan.create')
    <a href="{{ route('loans.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Check-Out Aset
    </a>
    @endcan
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <form method="GET" action="{{ route('loans.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari peminjam / aset..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ request('date_from') }}" placeholder="Dari tanggal">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ request('date_to') }}" placeholder="Sampai tanggal">
            </div>
            <div class="col-auto">
                <div class="form-check">
                    <input type="checkbox" id="active_only" name="active_only" value="1" class="form-check-input"
                        {{ request()->boolean('active_only') ? 'checked' : '' }}>
                    <label for="active_only" class="form-check-label small text-white-50">
                        Aktif saja
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-light btn-sm"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="{{ route('loans.index') }}" class="btn btn-outline-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Aset</th>
                        <th>Peminjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr>
                            <td class="text-muted small">{{ $loans->firstItem() + $loop->index }}</td>
                            <td>
                                <a href="{{ route('assets.show', $loan->asset_id) }}" class="text-decoration-none">
                                    <span class="font-monospace fw-semibold small text-primary">{{ $loan->asset?->asset_code }}</span>
                                    <br><span class="small">{{ $loan->asset?->name }}</span>
                                </a>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $loan->borrower_name }}</span>
                                @if ($loan->borrower_email)
                                    <br><small class="text-muted">{{ $loan->borrower_email }}</small>
                                @endif
                            </td>
                            <td>{{ $loan->loan_date->translatedFormat('d M Y') }}</td>
                            <td>
                                @if ($loan->returned_at)
                                    {{ $loan->returned_at->translatedFormat('d M Y') }}
                                @elseif ($loan->expected_return_date)
                                    <span class="{{ $loan->expected_return_date->isPast() ? 'text-danger fw-semibold' : '' }}">
                                        {{ $loan->expected_return_date->translatedFormat('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($loan->returned_at)
                                    <span class="badge bg-success">Sudah Kembali</span>
                                @elseif ($loan->expected_return_date && $loan->expected_return_date->isPast())
                                    <span class="badge bg-danger">Terlambat</span>
                                @else
                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-info text-white" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('loan.checkin')
                                        @if (!$loan->returned_at)
                                            <form action="{{ route('loans.checkin', $loan) }}" method="POST"
                                                  onsubmit="return confirm('Check-in aset {{ $loan->asset?->asset_code }} dari {{ $loan->borrower_name }}?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" title="Check-In">
                                                    <i class="bi bi-box-arrow-in-left"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2 opacity-30"></i>
                                <span class="fw-medium">Belum ada data peminjaman.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($loans->hasPages())
            <div class="d-flex justify-content-center py-3 border-top px-3">
                {{ $loans->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
