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

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('loans.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="asset_id" class="form-label fw-semibold">Aset <span class="text-danger">*</span></label>
                    <select id="asset_id" name="asset_id"
                            class="form-select {{ $errors->has('asset_id') ? 'is-invalid' : '' }}"
                            data-searchable required>
                        <option value="" disabled {{ old('asset_id') === '' ? 'selected' : '' }}>-- Pilih Aset --</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                {{ $asset->asset_code }} — {{ $asset->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('asset_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="borrower_name" class="form-label fw-semibold">Nama Peminjam <span class="text-danger">*</span></label>
                    <input type="text" id="borrower_name" name="borrower_name"
                           class="form-control {{ $errors->has('borrower_name') ? 'is-invalid' : '' }}"
                           value="{{ old('borrower_name') }}" placeholder="Nama lengkap peminjam" required>
                    @error('borrower_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="borrower_email" class="form-label fw-semibold">Email Peminjam</label>
                    <input type="email" id="borrower_email" name="borrower_email"
                           class="form-control {{ $errors->has('borrower_email') ? 'is-invalid' : '' }}"
                           value="{{ old('borrower_email') }}" placeholder="opsional">
                    @error('borrower_email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="loan_date" class="form-label fw-semibold">Tanggal Pinjam <span class="text-danger">*</span></label>
                    <input type="date" id="loan_date" name="loan_date"
                           class="form-control {{ $errors->has('loan_date') ? 'is-invalid' : '' }}"
                           value="{{ old('loan_date', now()->format('Y-m-d')) }}" required>
                    @error('loan_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="expected_return_date" class="form-label fw-semibold">Tgl Rencana Kembali</label>
                    <input type="date" id="expected_return_date" name="expected_return_date"
                           class="form-control {{ $errors->has('expected_return_date') ? 'is-invalid' : '' }}"
                           value="{{ old('expected_return_date') }}">
                    @error('expected_return_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Catatan</label>
                    <textarea id="notes" name="notes" rows="2"
                              class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                              placeholder="Keterangan tambahan...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-box-arrow-right me-1"></i>Check-Out Aset
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
