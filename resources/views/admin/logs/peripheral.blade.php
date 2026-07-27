@extends('layouts.app')

@section('title', 'Log Peripheral')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Log Peripheral</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-mouse3 text-primary me-2"></i>Log Peripheral
        </h4>
        <p class="text-muted small mb-0 mt-1">Riwayat mutasi stok peripheral (pengambilan & penambahan stok)</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <form method="GET" action="{{ route('admin.logs.peripheral') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari peripheral, penerima, atau petugas..." value="{{ request('search') }}">
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
                <a href="{{ route('admin.logs.peripheral') }}" class="btn btn-outline-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Peripheral</th>
                        <th>Tipe</th>
                        <th class="text-center">Jumlah</th>
                        <th>Dilakukan Oleh</th>
                        <th>Diterima</th>
                        <th>Lokasi</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php $isRestok = $log->notes && str_starts_with($log->notes, 'Restok:') @endphp
                        <tr>
                            <td class="text-muted small">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                            <td class="small text-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="small">
                                <a href="{{ route('admin.peripherals.show', $log->peripheral_id) }}" class="text-decoration-none">
                                    {{ $log->peripheral?->name ?? '(dihapus)' }}
                                </a>
                            </td>
                            <td>
                                @if ($isRestok)
                                    <span class="badge bg-info text-white">Restok</span>
                                @else
                                    <span class="badge bg-warning text-dark">Ambil</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($isRestok)
                                    <span class="badge bg-info text-white fs-6">+{{ $log->quantity }}</span>
                                @else
                                    <span class="badge bg-danger fs-6">-{{ $log->quantity }}</span>
                                @endif
                            </td>
                            <td class="small">{{ $log->issuedBy?->name ?? 'System' }}</td>
                            <td class="small">{{ $log->employee?->name ?? '—' }}</td>
                            <td class="small">{{ $log->location?->name ?? '—' }}</td>
                            <td class="small text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $isRestok ? substr($log->notes, 7) : ($log->notes ?? '—') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada riwayat mutasi peripheral.
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
