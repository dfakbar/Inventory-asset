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

@include('admin.peripherals._create_form', ['brands' => $brands, 'locations' => $locations])

@endsection
