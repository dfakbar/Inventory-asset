{{-- Kop surat untuk semua dokumen SOP --}}
@php
    $logoPath = public_path('images/KOBINTILES.png');
    $logoData = is_file($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;
@endphp
<div style="border-bottom: 3px solid #0d6efd; padding-bottom: 8px; margin-bottom: 14px;">
    <table style="width:100%; border-collapse: collapse;">
        <tr>
            @if ($logoData)
            <td style="width:64px; vertical-align: middle;">
                <img src="{{ $logoData }}" alt="Logo" style="width:52px; height:52px; object-fit:contain;">
            </td>
            @endif
            <td style="vertical-align: middle;">
                <div style="font-size:14pt; font-weight:bold; color:#0d6efd;">{{ config('app.name', 'AssetMS') }}</div>
                <div style="font-size:8.5pt; color:#555;">Sistem Informasi Manajemen Aset Perusahaan</div>
            </td>
            <td style="text-align:right; vertical-align: middle; font-size:8.5pt; color:#333;">
                <strong>Nomor: {{ $document->document_number }}</strong>
            </td>
        </tr>
    </table>
</div>