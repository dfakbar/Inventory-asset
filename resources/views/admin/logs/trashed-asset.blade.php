@extends('layouts.app')

@section('title', 'Log Aktivitas Terhapus')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.logs.asset') }}">Log Aktivitas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Log Terhapus</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-trash text-danger me-2"></i>Log Aktivitas Terhapus
        </h4>
        <p class="text-muted small mb-0 mt-1">Log yang dihapus akan otomatis terhapus permanen setelah 30 hari</p>
    </div>
    <a href="{{ route('admin.logs.asset') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <form id="restoreForm" method="POST" action="{{ route('admin.logs.asset.restore') }}">
            @csrf
            @method('PATCH')
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>#</th>
                            <th>Waktu Dihapus</th>
                            <th>Waktu Log</th>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $log->id }}" class="row-checkbox">
                                </td>
                                <td class="text-muted small">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                <td class="small text-nowrap">{{ $log->deleted_at->format('d/m/Y H:i') }}</td>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Tidak ada log yang terhapus.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->count() > 0)
            <div class="px-3 py-2 border-top bg-light d-flex justify-content-end">
                <button type="submit" class="btn btn-sm btn-success" id="restoreSelectedBtn" disabled>
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Pulihkan yang Dipilih
                </button>
            </div>
            @endif
        </form>
    </div>
    @if ($logs->hasPages())
    <div class="card-footer d-flex justify-content-center py-3">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    document.getElementById('restoreSelectedBtn').disabled = !document.querySelector('.row-checkbox:checked');
});
document.querySelectorAll('.row-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        document.getElementById('restoreSelectedBtn').disabled = !document.querySelector('.row-checkbox:checked');
    });
});
</script>
@endpush
