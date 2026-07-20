@extends('layouts.app')

@section('title', 'Log Aktivitas Aset')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Log Aktivitas Aset</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clock-history text-primary me-2"></i>Log Aktivitas Aset
        </h4>
        <p class="text-muted small mb-0 mt-1">Riwayat pembuatan, perubahan, dan penghapusan aset</p>
    </div>
    <div class="d-flex gap-2">
        @can('log.delete')
        <a href="{{ route('admin.logs.asset.trashed') }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-trash me-1"></i>Log Terhapus
            @if ($trashedCount > 0)
                <span class="badge bg-danger ms-1">{{ $trashedCount }}</span>
            @endif
        </a>
        @endcan
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <form method="GET" action="{{ route('admin.logs.asset') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari aktivitas..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select form-select-sm">
                    <option value="">Semua Aksi</option>
                    <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
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
                <a href="{{ route('admin.logs.asset') }}" class="btn btn-outline-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        @can('log.delete')
        <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-end">
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteLogModal">
                <i class="bi bi-trash me-1"></i>Hapus Log Berdasarkan Rentang Tanggal
            </button>
        </div>
        @endcan

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-muted small">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                            <td class="small text-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="small">{{ $log->user?->name ?? 'System' }}</td>
                            <td>
                                @switch($log->action)
                                    @case('created')
                                        <span class="badge bg-success">Created</span>
                                        @break
                                    @case('updated')
                                        <span class="badge bg-warning text-dark">Updated</span>
                                        @break
                                    @case('deleted')
                                        <span class="badge bg-danger">Deleted</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $log->action }}</span>
                                @endswitch
                            </td>
                            <td class="small">{{ $log->description }}</td>
                            <td class="small text-muted font-monospace">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada aktivitas aset.
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

@can('log.delete')
{{-- Modal Hapus Log --}}
<div class="modal fade" id="deleteLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.logs.asset.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-semibold text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>Hapus Log Aktivitas
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Log yang dihapus akan dipindahkan ke trash dan otomatis terhapus permanen setelah 30 hari.
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Dari Tanggal</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Sampai Tanggal</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus log pada rentang tanggal tersebut?')">
                        <i class="bi bi-trash me-1"></i>Hapus Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
