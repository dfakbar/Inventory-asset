<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Registrasi Aset</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #212529; }
        .title { text-align:center; font-size:12.5pt; font-weight:bold; margin: 2px 0 4px; }
        .subtitle { text-align:center; font-size:8.5pt; color:#666; margin-bottom:12px; }
        table.detail { width:100%; border-collapse: collapse; margin-bottom:12px; }
        table.detail th { text-align:left; width:32%; background:#f1f3f5; border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        table.detail td { border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        .section { font-weight:bold; font-size:9.5pt; color:#0d6efd; margin:10px 0 5px; }
        .reason { border:1px solid #dee2e6; padding:8px; font-size:8.5pt; min-height:36px; margin-bottom:12px; }
        .sign-row { width:100%; margin-top:26px; }
        .sign-col { display:inline-block; width:33%; text-align:center; vertical-align:top; font-size:8.5pt; }
        .sign-line { border-bottom:1px solid #212529; height:30px; width:80%; margin:0 auto; }
        .sign-cap { margin-top:4px; font-weight:bold; }
        .footer { position: fixed; bottom: 0; width:100%; text-align:center; font-size:7pt; color:#999; padding:10px 0; border-top:1px solid #eee; }
    </style>
</head>
<body>
    @include('sop_documents.pdf._header')

    <div class="title">FORM REGISTRASI ASET</div>
    <div class="subtitle">
        Dokumen pencatatan kelengkapan data aset IT
        @if ($document->document_date) • {{ $document->document_date->translatedFormat('d F Y') }} @endif
    </div>

    <div class="section">A. Identitas Aset</div>
    @foreach ($assets as $asset)
        @if ($loop->first)
            <table class="detail">
        @else
            <table class="detail" style="page-break-inside: avoid; margin-top: 16px;">
        @endif
            <tr><th>Kode Aset</th><td>{{ $asset->asset_code }}</td><th>Kategori</th><td>{{ $asset->category?->name ?? '—' }}</td></tr>
            <tr><th>Nama Aset</th><td>{{ $asset->name }}</td><th>Status</th><td>{{ $asset->status?->label() ?? '—' }}</td></tr>
            <tr><th>Merek</th><td>{{ $asset->brand?->name ?? '—' }}</td><th>Vendor</th><td>{{ $asset->vendor?->name ?? '—' }}</td></tr>
            <tr><th>Model</th><td>{{ $asset->model ?? '—' }}</td><th>Jumlah</th><td>{{ $asset->quantity ?? 1 }} unit</td></tr>
            <tr><th>Serial Number</th><td>{{ $asset->serial_number ?? '—' }}</td><th>MAC Address</th><td>{{ $asset->mac_address ?? '—' }}</td></tr>
            <tr><th>Tanggal Pembelian</th><td>{{ $asset->purchase_date?->translatedFormat('d F Y') ?? '—' }}</td><th>Harga Pembelian</th><td>{{ $asset->purchase_price ? 'Rp ' . number_format($asset->purchase_price, 0, ',', '.') : '—' }}</td></tr>
        </table>
    @endforeach

    <div class="section">B. Penempatan</div>
    @foreach ($assets as $asset)
        @if ($loop->first)
            <table class="detail">
        @else
            <table class="detail" style="page-break-inside: avoid; margin-top: 16px;">
        @endif
            <tr><th>Kode Aset</th><td>{{ $asset->asset_code }}</td></tr>
            <tr><th>Lokasi</th><td>{{ $asset->location?->name ?? '—' }}</td></tr>
            <tr><th>PIC (System)</th><td>{{ $asset->assignedUser?->name ?? '—' }}</td></tr>
            <tr><th>Pengguna / Karyawan</th><td>
                {{ $asset->employee?->name ?? '—' }}
                @if ($asset->employee?->department) ({{ $asset->employee->department }}) @endif
            </td></tr>
        </table>
    @endforeach

    @if ($document->notes)
    <div class="section">C. Catatan</div>
    <div class="reason">{{ $document->notes }}</div>
    @endif

    <div class="sign-row">
        <div class="sign-col">
            <div class="sign-cap">Disiapkan Oleh</div>
            <div class="sign-line"></div>
            <div>{{ $asset->assignedUser?->name ?? $document->createdBy?->name }}</div>
            <div style="color:#666;">(PIC / Admin Aset)</div>
        </div>
        <div class="sign-col">
            <div class="sign-cap">Mengetahui</div>
            <div class="sign-line"></div>
            <div>______________________</div>
            <div style="color:#666;">(Atasan / Manajer)</div>
        </div>
        <div class="sign-col">
            <div class="sign-cap">Penerima / Pengguna</div>
            <div class="sign-line"></div>
            <div>{{ $asset->employee?->name ?? '______________________' }}</div>
            <div style="color:#666;">(Pengguna Karyawan)</div>
        </div>
    </div>

    <div class="footer">
        {{ config('app.name', 'AssetMS') }} — Form Registrasi Aset {{ $document->document_number }}
    </div>
</body>
</html>