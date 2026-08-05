<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Mutasi Aset</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #212529; }
        .title { text-align:center; font-size:12.5pt; font-weight:bold; margin: 2px 0 4px; }
        .subtitle { text-align:center; font-size:8.5pt; color:#666; margin-bottom:12px; }
        .intro { font-size:8.5pt; text-align:justify; margin-bottom:12px; }
        table.detail { width:100%; border-collapse: collapse; margin-bottom:12px; }
        table.detail th { text-align:center; width:22%; background:#0d6efd; color:#fff; border:1px solid #0d6efd; padding:5px 8px; font-size:8.5pt; }
        table.detail td { border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        .section { font-weight:bold; font-size:9.5pt; color:#0d6efd; margin:10px 0 5px; }
        .sign-row { width:100%; margin-top:34px; }
        .sign-col { display:inline-block; width:49%; text-align:center; vertical-align:top; font-size:8.5pt; }
        .sign-line { border-bottom:1px solid #212529; height:32px; width:80%; margin:0 auto; }
        .sign-cap { margin-top:4px; font-weight:bold; }
        .footer { position: fixed; bottom: 0; width:100%; text-align:center; font-size:7pt; color:#999; padding:10px 0; border-top:1px solid #eee; }
    </style>
</head>
<body>
    @include('sop_documents.pdf._header')

    <div class="title">BERITA ACARA MUTASI ASET</div>
    <div class="subtitle">
        Nomor: {{ $document->document_number }}
        @if ($document->document_date) • {{ $document->document_date->translatedFormat('d F Y') }} @endif
    </div>

    <p class="intro">
        Pada hari ini, dilakukan penyerahan dan penataan/pemindahan aset IT sesuai ketentuan yang berlaku pada
        {{ config('app.name', 'AssetMS') }}. Berikut adalah rincian mutasi aset yang telah dicatat dalam sistem:
    </p>

    <div class="section">A. Identitas Aset</div>
    @foreach ($logs as $log)
        @php $asset = $log->asset; @endphp
        <table class="detail" @if (!$loop->first) style="page-break-inside: avoid; margin-top: 16px;" @endif>
            <tr>
                <th>Kode Aset</th><td>{{ $asset?->asset_code ?? '—' }}</td>
                <th>Nama Aset</th><td>{{ $asset?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Kategori</th><td>{{ $asset?->category?->name ?? '—' }}</td>
                <th>Merek / Model</th><td>{{ $asset?->brand?->name ?? '—' }} {{ $asset?->model ? '/ ' . $asset->model : '' }}</td>
            </tr>
        </table>

        <div class="section">B. Rincian Mutasi</div>
        <table class="detail">
            <tr>
                <th style="width:22%;">Aspek</th>
                <th style="width:37%;">Sebelum</th>
                <th style="width:38%;">Sesudah</th>
            </tr>
            <tr>
                <th>Lokasi</th>
                <td>{{ $log->fromLocation?->name ?? '—' }}</td>
                <td>{{ $log->toLocation?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>PIC (System)</th>
                <td>{{ $log->fromAssignedUser?->name ?? '—' }}</td>
                <td>{{ $log->toAssignedUser?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Pengguna / Karyawan</th>
                <td>{{ $log->fromEmployee?->name ?? '—' }}</td>
                <td>{{ $log->toEmployee?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $log->from_status ? (\App\Enums\AssetStatus::tryFrom($log->from_status)?->label() ?? $log->from_status) : '—' }}</td>
                <td>{{ $log->to_status ? (\App\Enums\AssetStatus::tryFrom($log->to_status)?->label() ?? $log->to_status) : '—' }}</td>
            </tr>
            <tr>
                <th>Tanggal Mutasi</th>
                <td colspan="2">{{ $log->mutation_date?->translatedFormat('d F Y') ?? '—' }}</td>
            </tr>
            <tr>
                <th>Dilakukan Oleh</th>
                <td colspan="2">{{ $log->performedBy?->name ?? 'System' }}</td>
            </tr>
        </table>
    @endforeach

    @if ($document->notes)
    <div class="section">C. Keterangan</div>
    <table class="detail">
        <tr><td>{{ $document->notes }}</td></tr>
    </table>
    @endif

    <div class="sign-row">
        <div class="sign-col">
            <div class="sign-cap">Pelaksana / Pembuat Berita Acara</div>
            <div class="sign-line"></div>
            <div>{{ $data['presenter'] ?? $document->createdBy?->name }}</div>
            <div style="color:#666;">(Tanda Tangan & Nama)</div>
        </div>
        <div class="sign-col">
            <div class="sign-cap">Saksi</div>
            <div class="sign-line"></div>
            <div>{{ $data['witness'] ?? '______________________' }}</div>
            <div style="color:#666;">(Tanda Tangan & Nama)</div>
        </div>
    </div>

    <div class="footer">
        {{ config('app.name', 'AssetMS') }} — Berita Acara Mutasi Aset {{ $document->document_number }}
    </div>
</body>
</html>