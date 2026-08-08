@extends('layouts.app')

@section('title', 'Tambah Lokasi')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.locations.index') }}" class="text-decoration-none text-muted">Manajemen Lokasi</a>
    </li>
    <li class="breadcrumb-item active">Tambah Lokasi</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-pin-map-fill text-primary me-2"></i>Tambah Lokasi
        </h4>
        <p class="text-muted small mb-0 mt-1">Tambahkan lokasi penyimpanan aset baru.</p>
    </div>
    <a href="{{ route('admin.locations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@include('admin.locations._create_form')

@endsection
