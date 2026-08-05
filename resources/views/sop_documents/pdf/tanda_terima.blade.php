<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Tanda Terima Aset</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #212529; }
        .title { text-align:center; font-size:12.5pt; font-weight:bold; margin: 2px 0 4px; }
        .subtitle { text-align:center; font-size:8.5pt; color:#666; margin-bottom:12px; }
        .intro { font-size:8.5pt; text-align:justify; margin-bottom:12px; }
        table.detail { width:100%; border-collapse: collapse; margin-bottom:12px; }
        table.detail th { text-align:left; width:32%; background:#f1f3f5; border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        table.detail td { border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        .asset-separator { margin-top: 8px; margin-bottom: 8px; border-top: 1px dashed #ccc; }
        .sign-row { width:100%; margin-top:34px; }
        .sign-col { display:inline-block; width:49%; text-align:center; vertical-align:top; font-size:8.5pt; }
        .sign-line { border-bottom:1px solid #212529; height:32px; width:80%; margin:0 auto; }
        .sign-cap { margin-top:4px; font-weight:bold; }
        .footer { position: fixed; bottom: 0; width:100%; text-align:center; font-size:7pt; color:#999; padding:10px 0; border-top:1px solid #eee; }
    </style>
</head>
<body>
    @include('sop_documents.pdf._header')

    <div class="title">FORM TANDA TERIMA ASET &amp; PERIPHERAL</div>
    <div class="subtitle">
        @if ($document->document_date) {{ $document->document_date->translatedFormat('d F Y') }} @endif
    </div>

    <p class="intro">
        Yang bertanda tangan di bawah ini menyatakan bahwa telah <strong>menerima</strong> aset IT dan/atau peripheral berikut
        dalam keadaan baik dan layak digunakan, serta bersedia bertanggung jawab atas pemeliharaan dan keamanan aset tersebut
        sesuai ketentuan yang berlaku pada {{ config('app.name', 'AssetMS') }}.
    </p>

    @if ($assets->isNotEmpty())
        <table class="detail">
            <tr><th colspan="2" style="background:#0d6efd; color:#fff;">A. ASET</th></tr>
        </table>
        @foreach ($assets as $index => $asset)
            @if ($index > 0)
                <div class="asset-separator"></div>
            @endif
            <table class="detail">
                <tr><th>Kode Aset</th><td>{{ $asset->asset_code }}</td></tr>
                <tr><th>Nama Aset</th><td>{{ $asset->name }}</td></tr>
                <tr><th>Serial Number</th><td>{{ $asset->serial_number ?? '—' }}</td></tr>
            </table>
        @endforeach
    @endif

    @if ($peripherals->isNotEmpty())
        @if ($assets->isNotEmpty())
            <div class="asset-separator"></div>
        @endif
        <table class="detail">
            <tr><th colspan="2" style="background:#0d6efd; color:#fff;">B. PERIPHERAL</th></tr>
        </table>
        @foreach ($peripherals as $index => $peripheral)
            @if ($index > 0)
                <div class="asset-separator"></div>
            @endif
            <table class="detail">
                <tr><th>Nama Peripheral</th><td>{{ $peripheral->name }}</td></tr>
                <tr><th>Model</th><td>{{ $peripheral->model ?? '—' }}</td></tr>
            </table>
        @endforeach
    @endif

    <table class="detail">
        <tr><th>Lokasi Penempatan</th><td>{{ $location?->name ?? '—' }}</td></tr>
        <tr><th>Penerima</th><td>
            {{ $document->recipientEmployee?->name ?? '—' }}
            @if ($document->recipientEmployee?->department) ({{ $document->recipientEmployee->department }}) @endif
        </td></tr>
        <tr><th>Pemberi</th><td>{{ $data['giver_name'] ?? $document->createdBy?->name ?? '—' }}</td></tr>
        @if (!empty($data['purpose']))
        <tr><th>Tujuan / Keperluan</th><td>{{ $data['purpose'] }}</td></tr>
        @endif
        @if ($document->notes)
        <tr><th>Catatan</th><td>{{ $document->notes }}</td></tr>
        @endif
    </table>

    <div class="sign-row">
        <div class="sign-col">
            <div class="sign-cap">Penerima</div>
            <div class="sign-line"></div>
            <div>{{ $document->recipientEmployee?->name ?? '______________________' }}</div>
            <div style="color:#666;">(Nama Lengkap & Tanda Tangan)</div>
        </div>
        <div class="sign-col">
            <div class="sign-cap">Penyerah</div>
            <div class="sign-line"></div>
            <div>{{ $data['giver_name'] ?? $document->createdBy?->name }}</div>
            <div style="color:#666;">(PIC / Admin Aset)</div>
        </div>
    </div>

    <div class="footer">
        {{ config('app.name', 'AssetMS') }} — Form Tanda Terima Aset &amp; Peripheral {{ $document->document_number }}
    </div>
</body>
</html>