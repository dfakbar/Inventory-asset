{{--
    Partial: assets/_form.blade.php
    Digunakan oleh create.blade.php dan edit.blade.php.
    Variabel $asset bisa null (create) atau instance Asset (edit).
--}}
@php
    $asset = $asset ?? null;
    $isMutationOnly = ! auth()->user()->can('asset.edit') && auth()->user()->can('asset.mutate');
@endphp

<div class="row g-3">

    {{-- ══════════════════════════════════
         KOLOM KIRI
    ══════════════════════════════════ --}}
    <div class="col-lg-6">

        {{-- Nama Aset --}}
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">
                Nama Aset <span class="text-danger">*</span>
            </label>
            <input type="text"
                   id="name"
                   name="name"
                   class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                   value="{{ old('name', $asset->name ?? '') }}"
                   placeholder="Contoh: Laptop Dell Latitude 5420"
                   {{ $isMutationOnly ? 'disabled' : 'required' }}>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Kategori Aset --}}
        <div class="mb-3">
            <label for="asset_category_id" class="form-label fw-semibold">
                Kategori <span class="text-danger">*</span>
            </label>
            <select id="asset_category_id"
                    name="asset_category_id"
                    class="form-select {{ $errors->has('asset_category_id') ? 'is-invalid' : '' }}"
                    data-searchable
                    {{ $isMutationOnly ? 'disabled' : 'required' }}>
                <option value="" disabled {{ old('asset_category_id', $asset->asset_category_id ?? '') === '' ? 'selected' : '' }}>
                    -- Pilih Kategori --
                </option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('asset_category_id', $asset->asset_category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                        @if ($category->abbreviation)
                            ({{ $category->abbreviation }})
                        @endif
                    </option>
                @endforeach
            </select>
            @error('asset_category_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Merek --}}
        <div class="mb-3">
            <label for="brand_id" class="form-label fw-semibold">Merek</label>
            <select id="brand_id"
                    name="brand_id"
                    class="form-select {{ $errors->has('brand_id') ? 'is-invalid' : '' }}"
                    data-searchable
                    {{ $isMutationOnly ? 'disabled' : '' }}>
                <option value="">-- Pilih Merek --</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}"
                        {{ old('brand_id', $asset->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
            @error('brand_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Vendor --}}
        <div class="mb-3">
            <label for="vendor_id" class="form-label fw-semibold">Vendor</label>
            <select id="vendor_id"
                    name="vendor_id"
                    class="form-select {{ $errors->has('vendor_id') ? 'is-invalid' : '' }}"
                    data-searchable
                    {{ $isMutationOnly ? 'disabled' : '' }}>
                <option value="">-- Pilih Vendor --</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}"
                        {{ old('vendor_id', $asset->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
            @error('vendor_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Model --}}
        <div class="mb-3">
            <label for="model" class="form-label fw-semibold">Model</label>
            <input type="text"
                   id="model"
                   name="model"
                   class="form-control {{ $errors->has('model') ? 'is-invalid' : '' }}"
                   value="{{ old('model', $asset->model ?? '') }}"
                   placeholder="Contoh: Latitude 5420, EliteBook 840"
                   {{ $isMutationOnly ? 'disabled' : '' }}>
            @error('model')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Serial Number --}}
        <div class="mb-3">
            <label for="serial_number" class="form-label fw-semibold">Serial Number</label>
            <input type="text"
                   id="serial_number"
                   name="serial_number"
                   class="form-control font-monospace {{ $errors->has('serial_number') ? 'is-invalid' : '' }}"
                   value="{{ old('serial_number', $asset->serial_number ?? '') }}"
                   placeholder="Nomor seri perangkat"
                   {{ $isMutationOnly ? 'disabled' : '' }}>
            @error('serial_number')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Status --}}
        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">
                Status <span class="text-danger">*</span>
            </label>
            <select id="status"
                    name="status"
                    class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}"
                    data-searchable
                    required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}"
                        {{ old('status', $asset->status->value ?? 'Spare') === $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Jumlah --}}
        <div class="mb-3">
            <label for="quantity" class="form-label fw-semibold">
                Jumlah <span class="text-danger">*</span>
            </label>
            <input type="number"
                   id="quantity"
                   name="quantity"
                   class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                   value="{{ old('quantity', $asset->quantity ?? 1) }}"
                   min="1"
                   max="9999"
                   {{ $isMutationOnly ? 'disabled' : 'required' }}>
            @error('quantity')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

    </div>{{-- /kolom kiri --}}

    {{-- ══════════════════════════════════
         KOLOM KANAN
    ══════════════════════════════════ --}}
    <div class="col-lg-6">

        {{-- Lokasi --}}
        <div class="mb-3">
            <label for="location_id" class="form-label fw-semibold">Lokasi</label>
            <select id="location_id"
                    name="location_id"
                    class="form-select {{ $errors->has('location_id') ? 'is-invalid' : '' }}"
                    data-searchable>
                <option value="">-- Pilih Lokasi --</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}"
                        {{ old('location_id', $asset->location_id ?? '') == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
            @error('location_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- PIC System (terkunci ke user yang login) --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">
                <i class="bi bi-person-fill me-1 text-muted"></i>PIC (System)
            </label>
            @php
                $assignedId   = auth()->id();
                $assignedName = auth()->user()->name;
            @endphp
            <input type="hidden" name="assigned_to" value="{{ $assignedId }}">
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="bi bi-person-lock text-secondary"></i>
                </span>
                <input type="text"
                       class="form-control bg-light text-secondary"
                       value="{{ $assignedName }}"
                       disabled>
                <span class="input-group-text bg-light">
                    <i class="bi bi-lock-fill text-muted small"></i>
                </span>
            </div>
            <div class="form-text text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                {{ $asset ? 'Penugasan PIC tidak dapat diubah.' : 'Aset otomatis ditugaskan kepada Anda.' }}
            </div>
        </div>

        {{-- Pengguna/Karyawan (Employee) — dapat dipilih --}}
        <div class="mb-3">
            <label for="employee_id" class="form-label fw-semibold">
                <i class="bi bi-person-badge me-1 text-muted"></i>Pengguna / Karyawan
            </label>
            <select id="employee_id"
                    name="employee_id"
                    class="form-select {{ $errors->has('employee_id') ? 'is-invalid' : '' }}"
                    data-searchable>
                <option value="">-- Pilih Pengguna --</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}"
                        {{ old('employee_id', $asset->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                        @if ($employee->department)
                            ({{ $employee->department }})
                        @endif
                    </option>
                @endforeach
            </select>
            @error('employee_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div class="form-text text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Pilih karyawan non-system yang menggunakan aset ini. Kelola data karyawan di
                <a href="{{ route('admin.employees.index') }}">Manajemen Pengguna</a>.
            </div>
        </div>

        {{-- Tanggal Pembelian --}}
        @if(auth()->user()->can('asset.manage_finances'))
        <div class="mb-3">
            <label for="purchase_date" class="form-label fw-semibold">Tanggal Pembelian</label>
            <input type="date"
                   id="purchase_date"
                   name="purchase_date"
                   class="form-control {{ $errors->has('purchase_date') ? 'is-invalid' : '' }}"
                   value="{{ old('purchase_date', $asset?->purchase_date?->format('Y-m-d') ?? '') }}"
                   {{ $isMutationOnly ? 'disabled' : '' }}>
            @error('purchase_date')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        @endif

        {{-- Tanggal Mutasi --}}
        <div class="mb-3">
            <label for="mutation_date" class="form-label fw-semibold">Tanggal Mutasi</label>
            <input type="date"
                   id="mutation_date"
                   name="mutation_date"
                   class="form-control {{ $errors->has('mutation_date') ? 'is-invalid' : '' }}"
                   value="{{ old('mutation_date', $asset?->mutation_date?->format('Y-m-d') ?? '') }}">
            <div class="form-text text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Tanggal aktual perpindahan/mutasi aset (bisa dikosongkan).
            </div>
            @error('mutation_date')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Harga Pembelian --}}
        @if(auth()->user()->can('asset.manage_finances'))
        <div class="mb-3">
            <label for="purchase_price" class="form-label fw-semibold">Harga Pembelian (Rp)</label>
            <div class="input-group {{ $errors->has('purchase_price') ? 'has-validation' : '' }}">
                <span class="input-group-text text-muted">Rp</span>
                <input type="number"
                       id="purchase_price"
                       name="purchase_price"
                       class="form-control {{ $errors->has('purchase_price') ? 'is-invalid' : '' }}"
                       value="{{ old('purchase_price', $asset->purchase_price ?? '') }}"
                       step="0.01"
                       min="0"
                       placeholder="0.00"
                       {{ $isMutationOnly ? 'disabled' : '' }}>
                @error('purchase_price')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @endif

        {{-- Catatan --}}
        <div class="mb-3">
            <label for="notes" class="form-label fw-semibold">Catatan</label>
            <textarea id="notes"
                      name="notes"
                      rows="3"
                      class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                      placeholder="Informasi tambahan mengenai aset ini..."
                       >{{ old('notes', $asset->notes ?? '') }}</textarea>
            @error('notes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Foto Aset --}}
        <div class="mb-3">
            <label for="image" class="form-label fw-semibold">Foto Aset</label>

            {{-- Preview foto yang sudah ada (mode edit) --}}
            @if ($asset && $asset->image)
                <div class="mb-2" id="current-image-wrapper">
                    <p class="small text-muted mb-1">
                        <i class="bi bi-image me-1"></i>Foto saat ini:
                    </p>
                    <img src="{{ asset('storage/' . $asset->image) }}"
                         alt="Foto {{ $asset->name }}"
                         class="img-thumbnail rounded"
                         style="max-height: 160px; max-width: 100%; object-fit: contain;">

                    {{-- Checkbox hapus foto --}}
                    <div class="form-check mt-2">
                        <input class="form-check-input"
                               type="checkbox"
                               name="remove_image"
                               id="remove_image"
                               value="1"
                               {{ old('remove_image') ? 'checked' : '' }}
                               {{ $isMutationOnly ? 'disabled' : '' }}>
                        <label class="form-check-label text-danger small" for="remove_image">
                            <i class="bi bi-trash me-1"></i>Hapus foto ini
                        </label>
                    </div>
                </div>
            @endif

            <input type="file"
                   id="image"
                   name="image"
                   class="form-control {{ $errors->has('image') ? 'is-invalid' : '' }}"
                   accept="image/jpg,image/jpeg,image/png,image/webp"
                   {{ $isMutationOnly ? 'disabled' : '' }}>
            <div class="form-text text-muted">
                <i class="bi bi-info-circle me-1"></i>Format: JPG, JPEG, PNG, WebP. Maks. 2 MB.
            </div>
            @error('image')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror

            {{-- Preview foto baru sebelum upload --}}
            <div id="new-image-preview" class="mt-2 d-none">
                <p class="small text-muted mb-1">
                    <i class="bi bi-eye me-1"></i>Preview foto baru:
                </p>
                <img id="new-image-preview-img"
                     src="#"
                     alt="Preview"
                     class="img-thumbnail rounded"
                     style="max-height: 160px; max-width: 100%; object-fit: contain;">
            </div>
        </div>

    </div>{{-- /kolom kanan --}}

</div>{{-- /row --}}

@push('scripts')
<script>
    (() => {
        // Live preview untuk foto yang baru dipilih
        const imageInput   = document.getElementById('image');
        const previewBox   = document.getElementById('new-image-preview');
        const previewImg   = document.getElementById('new-image-preview-img');

        if (imageInput) {
            imageInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        previewImg.src = e.target.result;
                        previewBox.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewBox.classList.add('d-none');
                    previewImg.src = '#';
                }
            });
        }
    })();
</script>
@endpush
