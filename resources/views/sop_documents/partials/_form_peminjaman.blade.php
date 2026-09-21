{{-- Partial: Form Peminjaman — diterbitkan otomatis saat check-out, isi dikunci dari data loan --}}
@php
    $editLoan = null;
    if (! empty($data['loan_id'] ?? null)) {
        $editLoan = \App\Models\AssetLoan::with('asset')->find($data['loan_id']);
    } elseif (isset($document) && $document->loan_id) {
        $editLoan = $document->loan ?? \App\Models\AssetLoan::with('asset')->find($document->loan_id);
    }
    $editAsset = $assets->first() ?? $asset ?? $editLoan?->asset;
@endphp

<div class="alert alert-info py-2 small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    Form peminjaman diterbitkan otomatis dari data check-out di bawah ini. Untuk mengubah peminjam/aset,
    lakukan check-in lalu check-out ulang. Di sini Anda hanya dapat mengubah tanggal dokumen dan catatan.
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Aset</label>
        <input type="text" class="form-control" value="{{ $editAsset ? $editAsset->asset_code . ' — ' . $editAsset->name : '—' }}" disabled readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Pemohon (Peminjam)</label>
        <input type="text" class="form-control" value="{{ $editLoan?->borrower_name ?? '—' }}" disabled readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Pinjam</label>
        <input type="text" class="form-control" value="{{ $editLoan?->loan_date?->format('d/m/Y') ?? '—' }}" disabled readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Rencana Kembali</label>
        <input type="text" class="form-control" value="{{ $editLoan?->expected_return_date?->format('d/m/Y') ?? '—' }}" disabled readonly>
    </div>
</div>
