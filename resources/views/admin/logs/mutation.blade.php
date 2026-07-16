@extends('layouts.app')

@section('title', 'Log Mutasi Aset')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Log Mutasi Aset</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-arrow-left-right text-primary me-2"></i>Log Mutasi Aset
        </h4>
        <p class="text-muted small mb-0 mt-1">Riwayat perpindahan lokasi, penugasan, dan status aset</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <form method="GET" action="{{ route('admin.logs.mutation') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari kode/nama aset..." value="{{ request('search') }}">
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
                <button type="submit" class="btn btn-light btn-sm"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="{{ route('admin.logs.mutation') }}" class="btn btn-outline-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Tanggal Mutasi</th>
                        <th>Aset</th>
                        <th>Dilakukan Oleh</th>
                        <th>Lokasi</th>
                        <th>PIC (System)</th>
                        <th>Karyawan</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-muted small">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                            <td class="small text-nowrap">
                                {{ $log->mutation_date ? $log->mutation_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="small">
                                @if ($log->asset)
                                    <a href="{{ route('assets.show', $log->asset_id) }}" class="text-decoration-none">
                                        {{ $log->asset->asset_code }}
                                    </a>
                                @else
                                    <span class="text-muted">(dihapus)</span>
                                @endif
                            </td>
                            <td class="small">{{ $log->performedBy?->name ?? 'System' }}</td>
                            <td class="small">
                                @if ($log->from_location_id || $log->to_location_id)
                                    <span class="text-danger text-decoration-line-through">{{ $log->fromLocation?->name ?? '-' }}</span>
                                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                                    <span class="text-success">{{ $log->toLocation?->name ?? '-' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small">
                                @if ($log->from_assigned_to || $log->to_assigned_to)
                                    <span class="text-danger text-decoration-line-through">{{ $log->fromAssignedUser?->name ?? '-' }}</span>
                                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                                    <span class="text-success">{{ $log->toAssignedUser?->name ?? '-' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small">
                                @if ($log->from_employee_id || $log->to_employee_id)
                                    <span class="text-danger text-decoration-line-through">{{ $log->fromEmployee?->name ?? '-' }}</span>
                                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                                    <span class="text-success">{{ $log->toEmployee?->name ?? '-' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small">
                                @if ($log->from_status || $log->to_status)
                                    <span class="text-danger text-decoration-line-through">{{ $log->from_status ?? '-' }}</span>
                                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                                    <span class="text-success">{{ $log->to_status ?? '-' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $log->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada mutasi aset.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-center py-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
