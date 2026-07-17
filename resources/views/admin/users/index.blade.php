@extends('layouts.app')

@section('title', 'Manajemen User')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Manajemen User</li>
@endsection

@section('content')

{{-- ── Header ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-people-fill text-primary me-2"></i>Manajemen User
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola akun pengguna sistem</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i>Tambah User
    </a>
</div>

{{-- ── Table Card ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center ps-3" style="width:55px">No</th>
                        <th style="min-width:180px">Nama</th>
                        <th style="min-width:130px">Username</th>
                        <th style="min-width:200px">Email</th>
                        <th style="min-width:130px">Role</th>
                        <th class="text-center" style="min-width:90px">Status</th>
                        <th style="min-width:150px">Tgl Dibuat</th>
                        <th class="text-center pe-3" style="width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="text-center text-muted small ps-3">
                                {{ $users->firstItem() + $loop->index }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:34px;height:34px">
                                        <i class="bi bi-person-fill text-secondary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                        @if ($user->id === auth()->id())
                                            <div class="small text-muted">(Anda)</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted">{{ $user->username ?? '—' }}</td>
                            <td class="small text-muted">{{ $user->email }}</td>
                            <td>
                                <span class="{{ $user->role->badgeClass() }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($user->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $user->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit User">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Toggle Active (admin only) --}}
                                    @if(auth()->user()->isAdmin() && $user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $user->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                title="{{ $user->is_active ? 'Nonaktifkan User' : 'Aktifkan User' }}">
                                            <i class="bi {{ $user->is_active ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Hapus --}}
                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus user \'{{ addslashes($user->name) }}\'?\nTindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="{{ $user->id === auth()->id() ? 'Tidak dapat menghapus akun sendiri' : 'Hapus User' }}"
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">Belum ada data user.</span><br>
                                <small>
                                    <a href="{{ route('admin.users.create') }}">Tambah user pertama</a>
                                    sekarang.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="d-flex justify-content-center py-3 border-top px-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
