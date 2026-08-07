@extends('layouts.app')

@section('title', 'Manajemen Peripheral')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Peripheral</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-mouse3 text-primary me-2"></i>Manajemen Peripheral
        </h4>
        <p class="text-muted small mb-0 mt-1">Kelola stok asesoris komputer (SSD, keyboard, mouse, dll.)</p>
    </div>
    @can('peripheral.create')
    <a href="{{ route('admin.peripherals.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Peripheral
    </a>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center ps-3" style="width:55px">No</th>
                        <th style="min-width:180px">Nama</th>
                        <th style="min-width:120px">Merek</th>
                        <th style="min-width:120px">Model</th>
                        <th style="min-width:120px">Lokasi</th>
                        <th class="text-center" style="min-width:80px">Total Stok</th>
                        <th class="text-center" style="min-width:140px">Stok</th>
                        <th class="text-center pe-3" style="width:90px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peripherals as $p)
                        <tr>
                            <td class="text-center text-muted small ps-3">
                                {{ $peripherals->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <a href="{{ route('admin.peripherals.show', $p) }}"
                                   class="text-decoration-none fw-medium">
                                    {{ $p->name }}
                                </a>
                            </td>

                            <td class="small text-muted">{{ $p->brand?->name ?? '—' }}</td>
                            <td class="small text-muted">{{ $p->model ?? '—' }}</td>
                            <td class="small text-muted">{{ $p->location?->name ?? '—' }}</td>

                            <td class="text-center">
                                <span class="badge bg-secondary px-3">{{ $p->total_stock }}</span>
                            </td>

                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    @can('peripheral.issue')
                                    @if ($p->current_stock > 0)
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center"
                                            style="width:28px;height:28px;font-size:1.1rem;font-weight:700;line-height:1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#stockModal"
                                            data-mode="subtract"
                                            data-id="{{ $p->id }}"
                                            data-name="{{ $p->name }}"
                                            data-stock="{{ $p->current_stock }}"
                                            title="Kurangi Stok">
                                        −
                                    </button>
                                    @else
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center"
                                            style="width:28px;height:28px;font-size:1.1rem;font-weight:700;line-height:1"
                                            disabled
                                            title="Stok habis">
                                        −
                                    </button>
                                    @endif
                                    @endcan

                                    <a href="{{ route('admin.peripherals.show', $p) }}"
                                       class="text-decoration-none fw-bold fs-5 mx-1
                                           {{ $p->current_stock > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $p->current_stock }}
                                    </a>

                                    @can('peripheral.edit')
                                    <button type="button"
                                            class="btn btn-outline-success btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center"
                                            style="width:28px;height:28px;font-size:1.1rem;font-weight:700;line-height:1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#stockModal"
                                            data-mode="add"
                                            data-id="{{ $p->id }}"
                                            data-name="{{ $p->name }}"
                                            data-stock="{{ $p->current_stock }}"
                                            title="Tambah Stok">
                                        +
                                    </button>
                                    @endcan
                                </div>
                            </td>

                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.peripherals.show', $p) }}"
                                       class="btn btn-sm btn-info text-white"
                                       title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('peripheral.edit')
                                    <a href="{{ route('admin.peripherals.edit', $p) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('peripheral.delete')
                                    <form action="{{ route('admin.peripherals.destroy', $p) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus peripheral \'{{ addslashes($p->name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus">
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
                                <i class="bi bi-mouse3 display-4 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">Belum ada peripheral.</span><br>
                                <small>
                                    <a href="{{ route('admin.peripherals.create') }}">Tambah peripheral pertama</a>
                                    sekarang.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($peripherals->total() > 15)
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 py-3 border-top">
                @include('partials._pagination_per_page', ['paginator' => $peripherals])
                @if ($peripherals->hasPages())
                    {{ $peripherals->links() }}
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Modal Mutasi Stok — / --}}
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="stockForm" method="POST">
                @csrf
                <div class="modal-header py-2 px-4" id="stockModalHeader">
                    <h6 class="modal-title fw-semibold" id="stockModalTitle">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        <span id="modalTitleText">Mutasi Stok</span>
                        <small class="text-muted ms-2" id="modalPeripheralName"></small>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" max="9999" id="modalQuantity" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Diterima Oleh (Karyawan)</label>
                        <select name="employee_id" class="form-select">
                            <option value="">— Pilih Karyawan —</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department ?? '—' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Lokasi</label>
                        <select name="location_id" class="form-select">
                            <option value="">— Pilih Lokasi —</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Opsional"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn" id="modalSubmitBtn">
                        <i class="bi bi-check-lg me-1"></i>
                        <span id="modalSubmitText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('stockModal');
    if (!modal) return;

    const form = document.getElementById('stockForm');
    const titleText = document.getElementById('modalTitleText');
    const pName = document.getElementById('modalPeripheralName');
    const submitBtn = document.getElementById('modalSubmitBtn');
    const submitText = document.getElementById('modalSubmitText');
    const header = document.getElementById('stockModalHeader');
    const quantityInput = document.getElementById('modalQuantity');

    const issueRoute = '{{ route("admin.peripherals.issue", "__ID__") }}';
    const restockRoute = '{{ route("admin.peripherals.restock", "__ID__") }}';

    modal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        if (!btn) return;

        const mode = btn.getAttribute('data-mode');
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const stock = parseInt(btn.getAttribute('data-stock'), 10);

        pName.textContent = name ? '(' + name + ')' : '';

        if (mode === 'add') {
            form.action = restockRoute.replace('__ID__', id);
            titleText.textContent = 'Tambah Stok';
            submitBtn.className = 'btn btn-info text-white';
            submitText.textContent = 'Tambah';
            header.className = 'modal-header bg-info text-white py-2 px-4';
            quantityInput.max = 9999;
            quantityInput.min = 1;
            quantityInput.value = 1;
            submitBtn.disabled = false;
        } else {
            form.action = issueRoute.replace('__ID__', id);
            titleText.textContent = 'Kurangi Stok';
            submitBtn.className = 'btn btn-danger';
            submitText.textContent = 'Kurangi';
            header.className = 'modal-header bg-danger text-white py-2 px-4';

            if (stock <= 0) {
                quantityInput.min = 0;
                quantityInput.value = 0;
                submitBtn.disabled = true;
            } else {
                quantityInput.max = stock;
                quantityInput.min = 1;
                quantityInput.value = 1;
                submitBtn.disabled = false;
            }
        }
    });
})();
</script>
@endpush

@endsection
