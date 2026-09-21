<?php

namespace App\Services;

use App\Enums\SopDocumentType;
use App\Models\Asset;
use App\Models\AssetMutationLog;
use App\Models\Location;
use App\Models\Peripheral;
use App\Models\SopDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Logika bersama dokumen SOP: penomoran otomatis, kompilasi data view,
 * render PDF, dan pengarsipan PDF. Dipakai SopDocumentController
 * maupun LoanController (form peminjaman otomatis).
 */
class SopDocumentService
{
    public function generateNumber(SopDocumentType $type, ?string $documentDate = null): string
    {
        $date   = $documentDate ? Carbon::parse($documentDate) : now();
        $year   = $date->format('Y');
        $month  = $date->format('m');
        $prefix = $type->prefix();

        $maxSeq = SopDocument::withTrashed()
            ->where('document_type', $type->value)
            ->where('document_number', 'like', "{$prefix}-{$year}-{$month}-%")
            ->pluck('document_number')
            ->map(fn (string $n): int => (int) substr($n, strrpos($n, '-') + 1))
            ->max() ?? 0;

        return sprintf('%s-%s-%s-%04d', $prefix, $year, $month, $maxSeq + 1);
    }

    public function pdfView(SopDocumentType $type): string
    {
        return match ($type) {
            SopDocumentType::Registrasi       => 'sop_documents.pdf.registrasi',
            SopDocumentType::TandaTerima      => 'sop_documents.pdf.tanda_terima',
            SopDocumentType::PermohonanMutasi => 'sop_documents.pdf.permohonan_mutasi',
            SopDocumentType::BeritaAcara      => 'sop_documents.pdf.berita_acara',
            SopDocumentType::Peminjaman       => 'sop_documents.pdf.peminjaman',
        };
    }

    /**
     * Data terkompilasi untuk merender template PDF / show.
     */
    public function viewData(SopDocument $document): array
    {
        $data  = $document->data ?? [];
        $log   = $document->mutationLog;
        $asset = $document->asset ?? $log?->asset;

        // Dokumen peminjaman: aset + loan diambil dari relasi loan.
        $loan = $document->relationLoaded('loan') ? $document->loan : $document->loan()->first();
        if (! $asset && $loan?->asset) {
            $asset = $loan->asset;
        }

        $assetIds = $data['asset_ids'] ?? ($asset ? [$asset->id] : []);
        $assets = Asset::whereIn('id', $assetIds)
            ->with(['category', 'brand', 'location', 'vendor', 'assignedUser', 'employee'])
            ->get();

        $logIds = $data['mutation_log_ids'] ?? ($log ? [$log->id] : []);
        $logs = AssetMutationLog::with([
                'asset:id,asset_code,name,model,asset_category_id,brand_id',
                'asset.category:id,name',
                'asset.brand:id,name',
                'fromLocation:id,name',
                'toLocation:id,name',
                'fromAssignedUser:id,name',
                'toAssignedUser:id,name',
                'fromEmployee:id,name',
                'toEmployee:id,name',
                'performedBy:id,name',
            ])
            ->whereIn('id', $logIds)
            ->get();

        $peripheralIds = $data['peripheral_ids'] ?? [];
        $peripherals = Peripheral::with(['brand:id,name', 'location:id,name'])
            ->whereIn('id', $peripheralIds)
            ->get();

        $location = null;
        if (! empty($data['location_id'])) {
            $location = Location::find($data['location_id']);
        }
        if (! $location) {
            $location = $assets->first()?->location
                ?? $peripherals->first()?->location;
        }

        return [
            'document' => $document,
            'data'     => $data,
            'asset'    => $asset,
            'assets'   => $assets,
            'log'      => $log,
            'logs'     => $logs,
            'loan'     => $loan,
            'peripherals' => $peripherals,
            'location' => $location,
        ];
    }

    public function renderPdf(SopDocument $document): DomPdf
    {
        $pdf = Pdf::loadView($this->pdfView($document->document_type), $this->viewData($document));
        $pdf->setPaper('A4');

        return $pdf;
    }

    /**
     * Render + simpan PDF ke arsip storage (menimpa bila nomor sama).
     */
    public function archivePdf(SopDocument $document): void
    {
        $relativePath = 'documents/' . $document->document_number . '.pdf';
        Storage::disk('public')->put($relativePath, $this->renderPdf($document)->output());

        $document->update(['pdf_path' => $relativePath]);
    }
}
