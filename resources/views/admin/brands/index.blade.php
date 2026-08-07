@extends('layouts.app')

@section('title', 'Manajemen Merek')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Manajemen Merek</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-bookmark-star-fill text-primary me-2"></i>Manajemen Merek
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola merek/produsen aset</p>
    </div>
    @can('brand.create')
    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Merek
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
                        <th style="min-width:200px">Nama Merek</th>
                        <th style="min-width:300px">Deskripsi</th>
                        <th class="text-center" style="min-width:110px">Jumlah Aset</th>
                        <th class="text-center pe-3" style="width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr>
                            <td class="text-center text-muted small ps-3">
                                {{ $brands->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="fw-medium">{{ $brand->name }}</div>
                            </td>

                            <td class="small text-muted">
                                {{ $brand->description ? Str::limit($brand->description, 80) : '—' }}
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $brand->assets_count > 0 ? 'bg-primary' : 'bg-secondary bg-opacity-25 text-secondary' }} px-3">
                                    {{ $brand->assets_count }} aset
                                </span>
                            </td>

                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    @can('brand.edit')
                                    <a href="{{ route('admin.brands.edit', $brand) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Merek">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('brand.delete')
                                    <form action="{{ route('admin.brands.destroy', $brand) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus merek \'{{ addslashes($brand->name) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                @if($brand->assets_count > 0)
                                                    disabled
                                                    title="Tidak dapat dihapus — masih digunakan {{ $brand->assets_count }} aset"
                                                @else
                                                    title="Hapus Merek"
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
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-bookmark-star display-4 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">Belum ada data merek.</span><br>
                                <small>
                                    <a href="{{ route('admin.brands.create') }}">Tambah merek pertama</a>
                                    sekarang.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($brands->total() > 15)
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 py-3 border-top">
                @include('partials._pagination_per_page', ['paginator' => $brands])
                @if ($brands->hasPages())
                    {{ $brands->links() }}
                @endif
            </div>
        @endif
    </div>
</div>

@endsection
