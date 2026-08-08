@extends('layouts.app')

@section('title', 'Tambah Vendor')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.vendors.index') }}" class="text-decoration-none text-muted">Manajemen Vendor</a>
    </li>
    <li class="breadcrumb-item active">Tambah Vendor</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-truck text-primary me-2"></i>Tambah Vendor
        </h4>
        <p class="text-muted small mb-0 mt-1">Tambahkan vendor/supplier aset baru.</p>
    </div>
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@include('admin.vendors._create_form')

@endsection
