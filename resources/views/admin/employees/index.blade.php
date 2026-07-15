@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Manajemen Pengguna</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-people text-primary me-2"></i>Manajemen Pengguna
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola data pengguna non-system (karyawan) untuk penugasan aset</p>
    </div>
    @can('employee.create')
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Pengguna
    </a>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center ps-3" style="width:55px">No</th>
                        <th style="min-width:180px">Nama</th>
                        <th style="min-width:180px">Email</th>
                        <th style="min-width:120px">Telepon</th>
                        <th style="min-width:120px">Divisi</th>
                        <th style="min-width:120px">Jabatan</th>
                        <th class="text-center" style="min-width:110px">Jumlah Aset</th>
                        <th class="text-center pe-3" style="width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="text-center text-muted small ps-3">
                                {{ $employees->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                          style="width:28px;height:28px;font-size:.75rem">
                                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                                    </span>
                                    <div class="fw-medium">{{ $employee->name }}</div>
                                </div>
                            </td>

                            <td class="small text-muted">
                                {{ $employee->email ?? '—' }}
                            </td>

                            <td class="small text-muted">
                                {{ $employee->phone ?? '—' }}
                            </td>

                            <td class="small text-muted">
                                {{ $employee->department ?? '—' }}
                            </td>

                            <td class="small text-muted">
                                {{ $employee->position ?? '—' }}
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $employee->assets_count > 0 ? 'bg-primary' : 'bg-secondary bg-opacity-25 text-secondary' }} px-3">
                                    {{ $employee->assets_count }} aset
                                </span>
                            </td>

                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    @can('employee.edit')
                                    <a href="{{ route('admin.employees.edit', $employee) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Pengguna">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('employee.delete')
                                    <form action="{{ route('admin.employees.destroy', $employee) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus pengguna \'{{ addslashes($employee->name) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                @if($employee->assets_count > 0)
                                                    disabled
                                                    title="Tidak dapat dihapus — masih digunakan {{ $employee->assets_count }} aset"
                                                @else
                                                    title="Hapus Pengguna"
                                                @endif>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">Belum ada data pengguna.</span><br>
                                <small>
                                    <a href="{{ route('admin.employees.create') }}">Tambah pengguna pertama</a>
                                    sekarang.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="d-flex justify-content-center py-3 border-top">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
