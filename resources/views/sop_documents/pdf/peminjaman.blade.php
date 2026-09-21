<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Peminjaman Aset</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #212529; }
        .title { text-align:center; font-size:12.5pt; font-weight:bold; margin: 2px 0 4px; }
        .subtitle { text-align:center; font-size:8.5pt; color:#666; margin-bottom:12px; }
        .intro { font-size:8.5pt; text-align:justify; margin-bottom:12px; }
        table.detail { width:100%; border-collapse: collapse; margin-bottom:12px; }
        table.detail th { text-align:left; width:32%; background:#f1f3f5; border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        table.detail td { border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        .sign-row { width:100%; margin-top:34px; }
        .sign-col { display:inline-block; width:24%; text-align:center; vertical-align:top; font-size:8.5pt; }
        .sign-line { border-bottom:1px solid #212529; height:48px; width:85%; margin:0 auto; }
        .sign-cap { margin-top:4px; font-weight:bold; }
        .footer { position: fixed; bottom: 0; width:100%; text-align:center; font-size:7pt; color:#999; padding:10px 0; border-top:1px solid #eee; }
    </style>
</head>
<body>
    @include('sop_documents.pdf._header')

    <div class="title">FORM PEMINJAMAN ASET</div>
    <div class="subtitle">
        <span class="font-monospace">{{ $document->document_number }}</span>
        @if ($document->document_date) — {{ $document->document_date->translatedFormat('d F Y') }} @endif
    </div>

    <p class="intro">
        Yang bertanda tangan di bawah ini menyatakan telah <strong>meminjam</strong> aset IT berikut
        dalam keadaan baik dan layak digunakan, serta bersedia mengembalikannya
        @if ($loan?->expected_return_date)
            paling lambat pada tanggal <strong>{{ $loan->expected_return_date->translatedFormat('d F Y') }}</strong>
        @else
            sesuai ketentuan yang berlaku
        @endif
        dan bertanggung jawab atas pemeliharaan serta keamanan aset tersebut selama masa peminjaman
        sesuai ketentuan yang berlaku pada {{ config('app.name', 'AssetMS') }}.
    </p>

    @php $formAsset = $assets->first() ?? $asset ?? $loan?->asset; @endphp

    <table class="detail">
        <tr><th colspan="2" style="background:#0d6efd; color:#fff;">A. ASET YANG DIPINJAM</th></tr>
        <tr><th>Kode Aset</th><td>{{ $formAsset?->asset_code ?? '—' }}</td></tr>
        <tr><th>Nama Aset</th><td>{{ $formAsset?->name ?? '—' }}</td></tr>
        <tr><th>Kategori</th><td>{{ $formAsset?->category?->name ?? '—' }}</td></tr>
        <tr><th>Merek / Model</th><td>{{ trim(($formAsset?->brand?->name ?? '') . ' ' . ($formAsset?->model ?? '')) ?: '—' }}</td></tr>
        <tr><th>Serial Number</th><td>{{ $formAsset?->serial_number ?? '—' }}</td></tr>
        <tr><th>Lokasi Aset</th><td>{{ $formAsset?->location?->name ?? $location?->name ?? '—' }}</td></tr>
    </table>

    <table class="detail">
        <tr><th colspan="2" style="background:#0d6efd; color:#fff;">B. DATA PEMINJAMAN</th></tr>
        <tr><th>Nama Pemohon</th><td>{{ $loan?->borrower_name ?? '—' }}</td></tr>
        @if ($loan?->borrower_email)
        <tr><th>Email Pemohon</th><td>{{ $loan->borrower_email }}</td></tr>
        @endif
        <tr><th>Tanggal Pinjam</th><td>{{ $loan?->loan_date?->translatedFormat('d F Y') ?? $document->document_date?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><th>Rencana Kembali</th><td>{{ $loan?->expected_return_date?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        @if ($document->notes)
        <tr><th>Catatan</th><td>{{ $document->notes }}</td></tr>
        @endif
    </table>

    <div class="sign-row">
        <div class="sign-col">
            <div class="sign-cap">Pemohon</div>
            <div class="sign-line"></div>
        </div>
        <div class="sign-col">
            <div class="sign-cap">Dept. Head Pemohon</div>
            <div class="sign-line"></div>
        </div>
        <div class="sign-col">
            <div class="sign-cap">Dept. Head IT</div>
            <div class="sign-line"></div>
        </div>
        <div class="sign-col">
            <div class="sign-cap">Penyerah</div>
            <div class="sign-line"></div>
        </div>
    </div>

    <div class="footer">
        {{ config('app.name', 'AssetMS') }} — Form Peminjaman Aset {{ $document->document_number }}
    </div>
</body>
</html>
