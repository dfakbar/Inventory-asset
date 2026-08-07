@extends('layouts.app')

@section('title', 'Manajemen Lokasi')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Manajemen Lokasi</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-geo-alt-fill text-primary me-2"></i>Manajemen Lokasi
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola lokasi & departemen penyimpanan aset</p>
    </div>
    @can('location.create')
    <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Lokasi
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
                        <th style="min-width:160px">Nama Lokasi</th>
                        <th style="min-width:150px">Departemen</th>
                        <th style="min-width:220px">Deskripsi</th>
                        <th class="text-center" style="min-width:110px">Jumlah Aset</th>
                        <th class="text-center pe-3" style="width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        <tr>
                            <td class="text-center text-muted small ps-3">
                                {{ $locations->firstItem() + $loop->index }}
                            </td>

                            {{-- Nama --}}
                            <td>
                                <div class="fw-medium">{{ $location->name }}</div>
                            </td>

                            {{-- Departemen --}}
                            <td>
                                @if ($location->department)
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-diagram-3 me-1"></i>{{ $location->department }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">—</span>
                                @endif
                            </td>

                            {{-- Deskripsi --}}
                            <td class="small text-muted">
                                {{ $location->description ? Str::limit($location->description, 60) : '—' }}
                            </td>

                            {{-- Jumlah Aset --}}
                            <td class="text-center">
                                <span class="badge {{ $location->assets_count > 0 ? 'bg-primary' : 'bg-secondary bg-opacity-25 text-secondary' }} px-3">
                                    {{ $location->assets_count }} aset
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    @can('location.edit')
                                    <a href="{{ route('admin.locations.edit', $location) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Lokasi">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('location.delete')
                                    <form action="{{ route('admin.locations.destroy', $location) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus lokasi \'{{ addslashes($location->name) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                @if($location->assets_count > 0)
                                                    disabled
                                                    title="Tidak dapat dihapus — masih digunakan {{ $location->assets_count }} aset"
                                                @else
                                                    title="Hapus Lokasi"
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-geo display-4 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">Belum ada data lokasi.</span><br>
                                <small>
                                    <a href="{{ route('admin.locations.create') }}">Tambah lokasi pertama</a>
                                    sekarang.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($locations->total() > 15)
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 py-3 border-top">
                @include('partials._pagination_per_page', ['paginator' => $locations])
                @if ($locations->hasPages())
                    {{ $locations->links() }}
                @endif
            </div>
        @endif
    </div>
</div>

@endsection
