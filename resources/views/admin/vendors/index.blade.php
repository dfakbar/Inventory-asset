@extends('layouts.app')

@section('title', 'Manajemen Vendor')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item active" aria-current="page">Manajemen Vendor</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-truck text-primary me-2"></i>Manajemen Vendor
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola vendor/supplier aset</p>
    </div>
    @can('vendor.create')
    <button type="button" class="btn btn-primary js-open-create" data-create-url="{{ route('admin.vendors.create') }}">
        <i class="bi bi-plus-lg me-1"></i>Tambah Vendor
    </button>
    @endcan
</div>

@include('partials._search_bar', [
    'route'       => route('admin.vendors.index'),
    'label'       => 'Cari Vendor',
    'placeholder' => 'Cari nama vendor, kontak person, telepon, email...',
    'hint'        => 'Hasil pencarian untuk &ldquo;<strong>' . e(request('search')) . '</strong>&rdquo;. Pastikan vendor belum terdaftar sebelum menambah vendor baru.',
])

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center ps-3" style="width:55px">No</th>
                        <th style="min-width:160px">Nama Vendor</th>
                        <th style="min-width:140px">Kontak Person</th>
                        <th style="min-width:130px">Telepon</th>
                        <th style="min-width:180px">Email</th>
                        <th class="text-center pe-3" style="width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendors as $vendor)
                        <tr>
                            <td class="text-center text-muted small ps-3">
                                {{ $vendors->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="fw-medium">{{ $vendor->name }}</div>
                            </td>

                            <td class="small">
                                {{ $vendor->contact_person ?: '—' }}
                            </td>

                            <td class="small">
                                @if ($vendor->phone)
                                    <a href="tel:{{ $vendor->phone }}" class="text-decoration-none">{{ $vendor->phone }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="small">
                                @if ($vendor->email)
                                    <a href="mailto:{{ $vendor->email }}" class="text-decoration-none">{{ $vendor->email }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    @can('vendor.edit')
                                    <a href="{{ route('admin.vendors.edit', $vendor) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Vendor">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('vendor.delete')
                                    <form action="{{ route('admin.vendors.destroy', $vendor) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus vendor \'{{ addslashes($vendor->name) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus Vendor">
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
                                <i class="bi bi-truck display-4 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">Belum ada data vendor.</span><br>
                                <small>
                                    <a href="{{ route('admin.vendors.create') }}">Tambah vendor pertama</a>
                                    sekarang.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vendors->total() > 15)
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 py-3 border-top">
                @include('partials._pagination_per_page', ['paginator' => $vendors])
                @if ($vendors->hasPages())
                    {{ $vendors->links() }}
                @endif
            </div>
        @endif
    </div>
</div>

{{-- ── Modal Tambah Vendor ── --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-truck text-primary me-2"></i>Tambah Vendor
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

@endsection

@push('scripts')
    @include('partials._create_modal_js', ['formId' => 'vendorCreateForm'])
@endpush
