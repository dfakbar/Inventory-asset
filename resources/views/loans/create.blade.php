@extends('layouts.app')

@section('title', 'Check-Out Aset')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('loans.index') }}" class="text-decoration-none text-muted">Peminjaman</a></li>
    <li class="breadcrumb-item active" aria-current="page">Check-Out</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-box-arrow-right text-primary me-2"></i>Check-Out Aset
    </h4>
    <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@include('loans._create_form')

@endsection
