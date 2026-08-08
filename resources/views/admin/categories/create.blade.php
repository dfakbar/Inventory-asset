@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Admin</li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-muted">Manajemen Kategori</a>
    </li>
    <li class="breadcrumb-item active">Tambah Kategori</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-tag-fill text-primary me-2"></i>Tambah Kategori
        </h4>
        <p class="text-muted small mb-0 mt-1">Tambahkan kategori/jenis aset baru.</p>
    </div>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@include('admin.categories._create_form')

@endsection
