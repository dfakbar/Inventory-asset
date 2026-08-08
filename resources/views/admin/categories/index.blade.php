@extends('layouts.app')

@section('title', 'Manajemen Kategori')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Manajemen Kategori</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-tags-fill text-primary me-2"></i>Manajemen Kategori
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola kategori/jenis aset</p>
    </div>
    @can('category.create')
    <button type="button" class="btn btn-primary js-open-create" data-create-url="{{ route('admin.categories.create') }}">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
    </button>
    @endcan
</div>

@include('partials._search_bar', [
    'route'       => route('admin.categories.index'),
    'label'       => 'Cari Kategori',
    'placeholder' => 'Cari nama kategori, singkatan, deskripsi...',
    'hint'        => 'Hasil pencarian untuk &ldquo;<strong>' . e(request('search')) . '</strong>&rdquo;. Pastikan kategori belum terdaftar sebelum menambah kategori baru.',
])

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center ps-3" style="width:55px">No</th>
                        <th style="min-width:160px">Nama Kategori</th>
                        <th style="min-width:120px">Singkatan</th>
                        <th style="min-width:220px">Deskripsi</th>
                        <th class="text-center" style="min-width:110px">Jumlah Aset</th>
                        <th class="text-center pe-3" style="width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="text-center text-muted small ps-3">
                                {{ $categories->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="fw-medium">{{ $category->name }}</div>
                            </td>

                            <td>
                                <span class="badge bg-secondary">{{ $category->abbreviation }}</span>
                            </td>

                            <td class="small text-muted">
                                {{ $category->description ? Str::limit($category->description, 60) : '—' }}
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $category->assets_count > 0 ? 'bg-primary' : 'bg-secondary bg-opacity-25 text-secondary' }} px-3">
                                    {{ $category->assets_count }} aset
                                </span>
                            </td>

                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    @can('category.edit')
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Kategori">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('category.delete')
                                    <form action="{{ route('admin.categories.destroy', $category) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus kategori \'{{ addslashes($category->name) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                @if($category->assets_count > 0)
                                                    disabled
                                                    title="Tidak dapat dihapus — masih digunakan {{ $category->assets_count }} aset"
                                                @else
                                                    title="Hapus Kategori"
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
                                <i class="bi bi-tags display-4 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">Belum ada data kategori.</span><br>
                                <small>
                                    <a href="{{ route('admin.categories.create') }}">Tambah kategori pertama</a>
                                    sekarang.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->total() > 15)
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 py-3 border-top">
                @include('partials._pagination_per_page', ['paginator' => $categories])
                @if ($categories->hasPages())
                    {{ $categories->links() }}
                @endif
            </div>
        @endif
    </div>
</div>

@endsection

{{-- ── Modal Tambah Kategori ── --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-tag-fill text-primary me-2"></i>Tambah Kategori
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="createModalBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('partials._create_modal_js', ['formId' => 'categoryCreateForm'])
@endpush
