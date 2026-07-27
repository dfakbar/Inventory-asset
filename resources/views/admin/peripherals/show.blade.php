@extends('layouts.app')

@section('title', $peripheral->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.peripherals.index') }}" class="text-decoration-none text-muted">Peripheral</a>
    </li>
    <li class="breadcrumb-item active">{{ $peripheral->name }}</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-mouse3 text-primary me-2"></i>{{ $peripheral->name }}
        </h4>
        <p class="text-muted small mb-0 mt-1">Detail dan riwayat mutasi stok peripheral</p>
    </div>
    <div class="d-flex gap-2">
        @can('peripheral.edit')
        <a href="{{ route('admin.peripherals.edit', $peripheral) }}" class="btn btn-warning">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endcan
        <a href="{{ route('admin.peripherals.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
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

@if ($errors->any())
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="row g-4">

    {{-- Info Card --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-info-circle me-2"></i>Informasi Peripheral
                </h6>

                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted small" style="width:110px">Merek</td>
                        <td class="fw-medium">{{ $peripheral->brand?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Model</td>
                        <td class="fw-medium">{{ $peripheral->model ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Lokasi</td>
                        <td class="fw-medium">{{ $peripheral->location?->name ?? '—' }}</td>
                    </tr>
                    @if ($peripheral->notes)
                    <tr>
                        <td class="text-muted small">Catatan</td>
                        <td class="fw-medium">{{ $peripheral->notes }}</td>
                    </tr>
                    @endif
                </table>

                <hr>

                <div class="text-center mb-3">
                    <div class="small text-muted mb-1">Stok Saat Ini</div>
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        @can('peripheral.issue')
                        <button type="button"
                                class="btn btn-outline-danger rounded-circle p-2"
                                style="width:44px;height:44px;font-size:1.25rem;font-weight:700;line-height:1"
                                data-bs-toggle="modal"
                                data-bs-target="#stockModal"
                                data-mode="subtract"
                                title="Kurangi Stok">
                            −
                        </button>
                        @endcan

                        <span class="display-6 fw-bold {{ $peripheral->current_stock > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $peripheral->current_stock }}
                        </span>

                        @can('peripheral.edit')
                        <button type="button"
                                class="btn btn-outline-success rounded-circle p-2"
                                style="width:44px;height:44px;font-size:1.25rem;font-weight:700;line-height:1"
                                data-bs-toggle="modal"
                                data-bs-target="#stockModal"
                                data-mode="add"
                                title="Tambah Stok">
                            +
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted">Total Stok</span>
                    <span class="badge bg-secondary px-3">{{ $peripheral->total_stock }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Terpakai</span>
                    <span class="badge bg-warning text-dark px-3">{{ $peripheral->total_stock - $peripheral->current_stock }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- History --}}
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white py-2 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Mutasi Stok
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th class="small">Tanggal</th>
                                <th class="small">Tipe</th>
                                <th class="small text-center">Jumlah</th>
                                <th class="small">Dilakukan Oleh</th>
                                <th class="small">Diterima</th>
                                <th class="small">Lokasi</th>
                                <th class="small">Catatan</th>
                                <th class="small text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($peripheral->issuances as $iss)
                                @php $isRestok = $iss->notes && str_starts_with($iss->notes, 'Restok:') @endphp
                                <tr>
                                    <td class="small text-nowrap">{{ $iss->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if ($isRestok)
                                            <span class="badge bg-info text-white">Restok</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Ambil</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($isRestok)
                                            <span class="badge bg-info text-white fs-6">+{{ $iss->quantity }}</span>
                                        @else
                                            <span class="badge bg-danger fs-6">-{{ $iss->quantity }}</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $iss->issuedBy?->name ?? 'System' }}</td>
                                    <td class="small">{{ $iss->employee?->name ?? '—' }}</td>
                                    <td class="small">{{ $iss->location?->name ?? '—' }}</td>
                                    <td class="small text-muted">{{ $iss->notes ? ($isRestok ? substr($iss->notes, 7) : $iss->notes) : '—' }}</td>
                                    <td class="text-center">
                                        @can('peripheral.issue')
                                        @if (!$isRestok)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editIssuanceModal"
                                                data-issuance-id="{{ $iss->id }}"
                                                data-employee-id="{{ $iss->employee_id }}"
                                                data-location-id="{{ $iss->location_id }}"
                                                data-notes="{{ $iss->notes }}"
                                                title="Koreksi Mutasi">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted small">
                                        Belum ada riwayat mutasi stok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Mutasi Stok (+ / -) --}}
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="stockForm" method="POST">
                @csrf
                <div class="modal-header" id="stockModalHeader">
                    <h6 class="modal-title fw-semibold" id="stockModalTitle">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        <span id="modalTitleText">Mutasi Stok</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Jumlah <span class="text-danger">*</span></label>
                        <input type="number"
                               name="quantity"
                               class="form-control"
                               value="1"
                               min="1"
                               max="9999"
                               id="modalQuantity"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Diterima Oleh (Karyawan)</label>
                        <select name="employee_id"
                                class="form-select">
                            <option value="">— Pilih Karyawan —</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department ?? '—' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Lokasi</label>
                        <select name="location_id"
                                class="form-select">
                            <option value="">— Pilih Lokasi —</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Catatan</label>
                        <textarea name="notes"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Opsional"></textarea>
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

{{-- Modal Koreksi Mutasi Issuance --}}
<div class="modal fade" id="editIssuanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editIssuanceForm" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header bg-primary text-white py-2 px-4">
                    <h6 class="modal-title fw-semibold">
                        <i class="bi bi-pencil me-2"></i>Koreksi Mutasi
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Diterima Oleh (Karyawan)</label>
                        <select name="employee_id" id="edit_employee_id" class="form-select">
                            <option value="">— Pilih Karyawan —</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department ?? '—' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Lokasi</label>
                        <select name="location_id" id="edit_location_id" class="form-select">
                            <option value="">— Pilih Lokasi —</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Catatan</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="2" placeholder="Opsional"></textarea>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
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
    const submitBtn = document.getElementById('modalSubmitBtn');
    const submitText = document.getElementById('modalSubmitText');
    const header = document.getElementById('stockModalHeader');
    const quantityInput = document.getElementById('modalQuantity');

    modal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        if (!btn) return;
        const mode = btn.getAttribute('data-mode');

        if (mode === 'add') {
            form.action = '{{ route("admin.peripherals.restock", $peripheral) }}';
            titleText.textContent = 'Tambah Stok';
            submitBtn.className = 'btn btn-info text-white';
            submitText.textContent = 'Tambah Stok';
            header.className = 'modal-header bg-info text-white py-2 px-4';
            quantityInput.max = 9999;
            quantityInput.value = 1;
        } else {
            form.action = '{{ route("admin.peripherals.issue", $peripheral) }}';
            titleText.textContent = 'Kurangi Stok';
            submitBtn.className = 'btn btn-danger';
            submitText.textContent = 'Kurangi Stok';
            header.className = 'modal-header bg-danger text-white py-2 px-4';

            const max = {{ $peripheral->current_stock }};
            quantityInput.max = max;

            if (max <= 0) {
                quantityInput.min = 0;
                quantityInput.value = 0;
                submitBtn.disabled = true;
            } else {
                quantityInput.min = 1;
                quantityInput.value = 1;
                submitBtn.disabled = false;
            }
        }
    });

})();
</script>

<script>
(() => {
    const modal = document.getElementById('editIssuanceModal');
    if (!modal) return;

    const form = document.getElementById('editIssuanceForm');
    const employeeSelect = document.getElementById('edit_employee_id');
    const locationSelect = document.getElementById('edit_location_id');
    const notesInput = document.getElementById('edit_notes');

    const baseRoute = '{{ route("admin.peripherals.issuances.update", [$peripheral, "__ID__"]) }}';

    modal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        if (!btn) return;

        const id = btn.getAttribute('data-issuance-id');
        form.action = baseRoute.replace('__ID__', id);

        employeeSelect.value = btn.getAttribute('data-employee-id') || '';
        locationSelect.value = btn.getAttribute('data-location-id') || '';
        notesInput.value = btn.getAttribute('data-notes') || '';
    });
})();
</script>
@endpush

@endsection
