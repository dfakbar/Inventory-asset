<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Label — {{ $asset->asset_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .label-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-start;
        }
        .label-item {
            width: {{ $type === 'barcode' ? '220px' : '180px' }};
            border: 1px dashed #ccc;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .label-item img {
            max-width: 100%;
            height: auto;
        }
        .label-item .code-text {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
            color: #333;
        }
        .label-item .asset-name {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .no-print { margin-bottom: 15px; }
        @media print {
            .no-print { display: none; }
            .label-item { border: 1px dashed #999; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding:8px 20px;font-size:14px;cursor:pointer;background:#0d6efd;color:#fff;border:none;border-radius:6px;">
        <i class="bi bi-printer"></i> Cetak
    </button>
    <a href="{{ route('assets.show', $asset) }}" style="padding:8px 20px;font-size:14px;margin-left:8px;background:#6c757d;color:#fff;text-decoration:none;border-radius:6px;">
        Kembali
    </a>
    <span style="margin-left:15px;color:#666;font-size:13px;">
        {{ $count }} label — {{ $type === 'qr' ? 'QR Code' : 'Barcode (Code 128)' }}
    </span>
</div>

<div class="label-grid">
    @for ($i = 0; $i < $count; $i++)
    <div class="label-item">
        @if ($type === 'qr')
            <img src="{{ route('assets.qr-code', $asset) }}" alt="QR {{ $asset->asset_code }}">
        @else
            <img src="{{ route('assets.barcode', $asset) }}" alt="Barcode {{ $asset->asset_code }}">
        @endif
        <div class="code-text">{{ $asset->asset_code }}</div>
        <div class="asset-name">{{ $asset->name }}</div>
    </div>
    @endfor
</div>

<script>
    // Auto-print jika ada parameter print=1
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.onload = function() { window.print(); };
    }
</script>
</body>
</html>
