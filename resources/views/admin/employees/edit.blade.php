@extends('layouts.app')

@section('title', 'Edit Pengguna — ' . $employee->name)

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.employees.index') }}" class="text-decoration-none text-muted">Manajemen Pengguna</a>
    </li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i>Edit Pengguna
        </h4>
        <p class="text-muted small mb-0 mt-1">Perbarui data pengguna: <strong>{{ $employee->name }}</strong></p>
    </div>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
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
            <div class="card-header bg-warning py-2 px-4">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-person-badge me-2"></i>Data Pengguna
                </h6>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.employees.update', $employee) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold small">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                       value="{{ old('name', $employee->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small">Email</label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                       value="{{ old('email', $employee->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold small">Telepon</label>
                                <input type="text"
                                       id="phone"
                                       name="phone"
                                       class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                       value="{{ old('phone', $employee->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department" class="form-label fw-semibold small">Divisi</label>
                                <input type="text"
                                       id="department"
                                       name="department"
                                       class="form-control {{ $errors->has('department') ? 'is-invalid' : '' }}"
                                       value="{{ old('department', $employee->department) }}">
                                @error('department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position" class="form-label fw-semibold small">Jabatan</label>
                                <input type="text"
                                       id="position"
                                       name="position"
                                       class="form-control {{ $errors->has('position') ? 'is-invalid' : '' }}"
                                       value="{{ old('position', $employee->position) }}">
                                @error('position')
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
                                          rows="3">{{ old('notes', $employee->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-floppy2 me-1"></i>Perbarui Pengguna
                        </button>
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
