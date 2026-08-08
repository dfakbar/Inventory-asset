@extends('layouts.app')

@section('title', 'Tambah Merek')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.brands.index') }}" class="text-decoration-none text-muted">Manajemen Merek</a>
    </li>
    <li class="breadcrumb-item active">Tambah Merek</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-bookmark-star-fill text-primary me-2"></i>Tambah Merek
        </h4>
        <p class="text-muted small mb-0 mt-1">Tambahkan merek/produsen aset baru.</p>
    </div>
    <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@include('admin.brands._create_form')

@endsection
