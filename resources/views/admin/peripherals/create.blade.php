@extends('layouts.app')

@section('title', 'Tambah Peripheral')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.peripherals.index') }}" class="text-decoration-none text-muted">Peripheral</a>
    </li>
    <li class="breadcrumb-item active">Tambah Peripheral</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah Peripheral
        </h4>
        <p class="text-muted small mb-0 mt-1">Tambahkan jenis peripheral / asesoris komputer baru.</p>
    </div>
    <a href="{{ route('admin.peripherals.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

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

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-2 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-mouse3 me-2"></i>Data Peripheral
                </h6>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.peripherals.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold small">
                                    Nama Peripheral <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                       value="{{ old('name') }}"
                                       placeholder="Contoh: SSD Samsung 870 EVO 500GB"
                                       required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="brand_id" class="form-label fw-semibold small">Merek</label>
                                <select id="brand_id"
                                        name="brand_id"
                                        class="form-select {{ $errors->has('brand_id') ? 'is-invalid' : '' }}"
                                        data-searchable>
                                    <option value="">— Pilih Merek —</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="model" class="form-label fw-semibold small">Model</label>
                                <input type="text"
                                       id="model"
                                       name="model"
                                       class="form-control {{ $errors->has('model') ? 'is-invalid' : '' }}"
                                       value="{{ old('model') }}"
                                       placeholder="Contoh: 870 EVO">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location_id" class="form-label fw-semibold small">Lokasi</label>
                                <select id="location_id"
                                        name="location_id"
                                        class="form-select {{ $errors->has('location_id') ? 'is-invalid' : '' }}"
                                        data-searchable>
                                    <option value="">— Pilih Lokasi —</option>
                                    @foreach ($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="total_stock" class="form-label fw-semibold small">
                                    Total Stok <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       id="total_stock"
                                       name="total_stock"
                                       class="form-control {{ $errors->has('total_stock') ? 'is-invalid' : '' }}"
                                       value="{{ old('total_stock') }}"
                                       min="1" max="9999"
                                       required>
                                @error('total_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label fw-semibold small">Catatan</label>
                                <textarea id="notes"
                                          name="notes"
                                          class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                                          rows="3"
                                          placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy2 me-1"></i>Simpan Peripheral
                        </button>
                        <a href="{{ route('admin.peripherals.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
