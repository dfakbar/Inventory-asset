<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Permohonan Mutasi Aset</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #212529; }
        .title { text-align:center; font-size:12.5pt; font-weight:bold; margin: 2px 0 4px; }
        .subtitle { text-align:center; font-size:8.5pt; color:#666; margin-bottom:12px; }
        .intro { font-size:8.5pt; text-align:justify; margin-bottom:12px; }
        table.detail { width:100%; border-collapse: collapse; margin-bottom:12px; }
        table.detail th { text-align:left; width:32%; background:#f1f3f5; border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        table.detail td { border:1px solid #dee2e6; padding:5px 8px; font-size:8.5pt; }
        .section { font-weight:bold; font-size:9.5pt; color:#0d6efd; margin:10px 0 5px; }
        .reason { border:1px solid #dee2e6; padding:8px; font-size:8.5pt; min-height:40px; margin-bottom:12px; }
        .note { font-size:7.8pt; color:#666; margin-bottom:12px; }
        .sign-row { width:100%; margin-top:34px; }
        .sign-col { display:inline-block; width:49%; text-align:center; vertical-align:top; font-size:8.5pt; }
        .sign-line { border-bottom:1px solid #212529; height:32px; width:80%; margin:0 auto; }
        .sign-cap { margin-top:4px; font-weight:bold; }
        .footer { position: fixed; bottom: 0; width:100%; text-align:center; font-size:7pt; color:#999; padding:10px 0; border-top:1px solid #eee; }
    </style>
</head>
<body>
    @include('sop_documents.pdf._header')

    <div class="title">FORM PERMOHONAN MUTASI ASET</div>
    <div class="subtitle">
        @if ($document->document_date) {{ $document->document_date->translatedFormat('d F Y') }} @endif
    </div>

    <p class="intro">
        Yang bertanda tangan di bawah ini mengajukan permohonan mutasi (perpindahan lokasi / penugasan / perubahan status)
        terhadap aset IT sebagai berikut:
    </p>

    <div class="section">A. Data Aset (Kondisi Saat Ini)</div>
    @foreach ($assets as $asset)
        <table class="detail" @if (!$loop->first) style="page-break-inside: avoid; margin-top: 16px;" @endif>
            <tr><th>Kode Aset</th><td>{{ $asset->asset_code }}</td><th>Nama Aset</th><td>{{ $asset->name }}</td></tr>
            <tr><th>Kategori</th><td>{{ $asset->category?->name ?? '—' }}</td><th>Merek / Model</th><td>{{ $asset->brand?->name ?? '—' }} {{ $asset->model ? '/ ' . $asset->model : '' }}</td></tr>
            <tr><th>Lokasi Saat Ini</th><td>{{ $asset->location?->name ?? '—' }}</td><th>Status Saat Ini</th><td>{{ $asset->status?->label() ?? '—' }}</td></tr>
            <tr><th>Pengguna / Karyawan</th><td>{{ $asset->employee?->name ?? '—' }}</td><th>PIC (System)</th><td>{{ $asset->assignedUser?->name ?? '—' }}</td></tr>
        </table>
    @endforeach

    <div class="section">B. Usulan Mutasi</div>
    <table class="detail">
        <tr>
            <th>Lokasi Tujuan</th>
            <td>
                {{ !empty($data['target_location_id']) ? (\App\Models\Location::find($data['target_location_id'])?->name ?? '—') : 'Tetap (tidak berubah)' }}
            </td>
        </tr>
        <tr>
            <th>Pengguna / Karyawan Tujuan</th>
            <td>
                {{ !empty($data['target_employee_id']) ? (\App\Models\Employee::find($data['target_employee_id'])?->name ?? '—') : 'Tetap (tidak berubah)' }}
            </td>
        </tr>
        <tr>
            <th>Status Tujuan</th>
            <td>
                {{ !empty($data['target_status']) ? (\App\Enums\AssetStatus::tryFrom($data['target_status'])?->label() ?? '—') : 'Tetap (tidak berubah)' }}
            </td>
        </tr>
        <tr><th>Pemohon</th><td>{{ $data['requester_name'] ?? $document->createdBy?->name ?? '—' }}</td></tr>
        <tr><th>Tanggal Permohonan</th><td>{{ $document->document_date?->translatedFormat('d F Y') ?? '—' }}</td></tr>
    </table>

    <div class="section">C. Alasan Permohonan</div>
    <div class="reason">{{ $document->reason ?? $document->notes }}</div>

    <div class="note">
        Catatan: Dokumen ini merupakan usulan permohonan. Mutasi dianggap sah setelah disetujui oleh pejabat berwenang
        dan dicatat di Sistem Informasi Manajemen Aset.
    </div>

    <div class="sign-row">
        <div class="sign-col">
            <div class="sign-cap">Pemohon</div>
            <div class="sign-line"></div>
            <div>{{ $data['requester_name'] ?? $document->createdBy?->name }}</div>
            <div style="color:#666;">(Tanda Tangan & Nama)</div>
        </div>
        <div class="sign-col">
            <div class="sign-cap">Menyetujui</div>
            <div class="sign-line"></div>
            <div>______________________</div>
            <div style="color:#666;">(Pejabat Berwenang)</div>
        </div>
    </div>

    <div class="footer">
        {{ config('app.name', 'AssetMS') }} — Form Permohonan Mutasi Aset {{ $document->document_number }}
    </div>
</body>
</html>