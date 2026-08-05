{{-- Kop surat untuk semua dokumen SOP --}}
<div style="border-bottom: 3px solid #0d6efd; padding-bottom: 8px; margin-bottom: 14px;">
    <table style="width:100%; border-collapse: collapse;">
        <tr>
            <td style="width:64px; vertical-align: middle;">
                <div style="width:52px; height:52px; background:#0d6efd; color:#fff; font-weight:bold; text-align:center; line-height:52px; font-size:20px; border-radius:6px;">
                    {{ strtoupper(substr(config('app.name', 'AssetMS'), 0, 1)) }}
                </div>
            </td>
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