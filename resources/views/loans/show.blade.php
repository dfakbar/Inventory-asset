@extends('layouts.app')

@section('title', 'Detail Peminjaman')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('loans.index') }}" class="text-decoration-none text-muted">Peminjaman</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
@endsection

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-file-text text-primary me-2"></i>Detail Peminjaman
        </h4>
    </div>
    <div class="d-flex gap-2">
        @can('loan.checkin')
            @if (!$loan->returned_at)
                <form action="{{ route('loans.checkin', $loan) }}" method="POST"
                      onsubmit="return confirm('Check-in aset {{ $loan->asset?->asset_code }}?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-left me-1"></i>Check-In Aset
                    </button>
                </form>
            @endif
        @endcan
        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle-fill me-2"></i>Informasi Peminjaman</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small" style="width:40%">Aset</th>
                            <td class="py-3 pe-3">
                                <a href="{{ route('assets.show', $loan->asset_id) }}" class="text-decoration-none">
                                    <span class="font-monospace fw-semibold">{{ $loan->asset?->asset_code }}</span>
                                    <br><span>{{ $loan->asset?->name }}</span>
                                </a>
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Peminjam</th>
                            <td class="py-3 pe-3">
                                <span class="fw-medium">{{ $loan->borrower_name }}</span>
                                @if ($loan->borrower_email)
                                    <br><small class="text-muted">{{ $loan->borrower_email }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Tanggal Pinjam</th>
                            <td class="py-3 pe-3">{{ $loan->loan_date->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Rencana Kembali</th>
                            <td class="py-3 pe-3">{{ $loan->expected_return_date?->translatedFormat('d F Y') ?: '—' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <th class="ps-3 py-3 text-muted fw-medium small">Tanggal Kembali</th>
                            <td class="py-3 pe-3">
                                @if ($loan->returned_at)
                                    {{ $loan->returned_at->translatedFormat('d F Y') }}
                                @else
                                    <span class="badge bg-warning text-dark">Belum Kembali</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 py-3 text-muted fw-medium small">Status</th>
                            <td class="py-3 pe-3">
                                @if ($loan->returned_at)
                                    <span class="badge bg-success">Sudah Kembali</span>
                                @elseif ($loan->expected_return_date && $loan->expected_return_date->isPast())
                                    <span class="badge bg-danger">Terlambat</span>
                                @else
                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-journal-text me-2"></i>Catatan</h6>
            </div>
            <div class="card-body">
                @if ($loan->notes)
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $loan->notes }}</p>
                @else
                    <p class="text-muted mb-0">Tidak ada catatan.</p>
                @endif
            </div>
        </div>
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 fw-semibold text-muted"><i class="bi bi-person me-2"></i>Petugas</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $loan->createdBy?->name ?: 'Sistem' }}</p>
                <small class="text-muted">{{ $loan->created_at->translatedFormat('d F Y H:i') }}</small>
            </div>
        </div>
    </div>
</div>
@endsection
