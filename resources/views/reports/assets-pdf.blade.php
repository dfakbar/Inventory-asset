<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #333; }
        h1 { font-size: 14pt; text-align: center; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 8pt; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0d6efd; color: #fff; padding: 6px 4px; font-size: 7.5pt; text-align: left; }
        td { padding: 4px; border-bottom: 1px solid #dee2e6; font-size: 7.5pt; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .text-center { text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #999; padding: 10px 0; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="subtitle">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                @foreach ($pdfHeaders as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {!! $rowsHtml !!}
        </tbody>
    </table>

    <div class="footer">
        AssetMS — {{ config('app.name') }} | Laporan Aset
    </div>
</body>
</html>
