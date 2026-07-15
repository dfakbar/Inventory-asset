<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kategori Aset</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #333; }
        h1 { font-size: 14pt; text-align: center; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 8pt; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0d6efd; color: #fff; padding: 8px 6px; text-align: left; }
        td { padding: 6px; border-bottom: 1px solid #dee2e6; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #999; padding: 10px 0; }
    </style>
</head>
<body>
    <h1>Laporan Jumlah Aset per Kategori</h1>
    <p class="subtitle">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th class="text-center">Jumlah Aset</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $cat)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $cat->name }} @if($cat->abbreviation)({{ $cat->abbreviation }})@endif</td>
                    <td class="text-center">{{ $cat->assets_count }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-end">Total</td>
                <td class="text-center">{{ $totalAssets }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        AssetMS — {{ config('app.name') }}
    </div>
</body>
</html>
