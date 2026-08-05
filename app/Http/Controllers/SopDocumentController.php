<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\SopDocumentType;
use App\Http\Requests\StoreSopDocumentRequest;
use App\Models\Asset;
use App\Models\AssetMutationLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Peripheral;
use App\Models\SopDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SopDocumentController extends Controller
{
    // =========================================================
    // INDEX
    // =========================================================

    public function index(Request $request): View
    {
        $this->authorize('document.viewAny');

        $documents = SopDocument::with(['asset:id,asset_code,name', 'recipientEmployee:id,name', 'createdBy:id,name'])
            ->when($request->filled('type'), fn ($q) => $q->where('document_type', $request->type))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where(function ($q) use ($term) {
                    $q->where('document_number', 'like', "%{$term}%")
                      ->orWhereHas('asset', fn ($q) => $q
                          ->where('asset_code', 'like', "%{$term}%")
                          ->orWhere('name', 'like', "%{$term}%")
                      )
                      ->orWhereHas('recipientEmployee', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('document_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('document_date', '<=', $request->date_to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $types = SopDocumentType::cases();

        return view('sop_documents.index', compact('documents', 'types'));
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function create(Request $request): View
    {
        $this->authorize('document.create');

        $types    = SopDocumentType::cases();
        $type     = SopDocumentType::tryFrom($request->input('type', SopDocumentType::Registrasi->value)) ?? SopDocumentType::Registrasi;
        $assets   = Asset::orderBy('asset_code')->get(['id', 'asset_code', 'name', 'serial_number', 'model']);
        $assets->load(['category:id,name', 'brand:id,name', 'location:id,name']);
        $employees = Employee::active()->orderBy('name')->get(['id', 'name', 'department']);
        $locations = Location::orderBy('name')->get(['id', 'name']);
        $statuses  = AssetStatus::cases();
        $peripherals = Peripheral::with(['brand:id,name', 'location:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'brand_id', 'model', 'location_id']);
        $mutationLogs = AssetMutationLog::with(['asset:id,asset_code,name'])
            ->orderByDesc('mutation_date')
            ->get(['id', 'asset_id', 'mutation_date']);

        $preselectedAssetIds = $request->has('asset_id')
            ? collect($request->input('asset_id'))->map(fn ($id) => (int) $id)->filter()->values()->all()
            : ($request->integer('asset_id') ? [$request->integer('asset_id')] : []);
        $preselectedLogIds = $request->has('mutation_log_id')
            ? collect($request->input('mutation_log_id'))->map(fn ($id) => (int) $id)->filter()->values()->all()
            : ($request->integer('mutation_log_id') ? [$request->integer('mutation_log_id')] : []);
        $preselectedPeripheralIds = $request->has('peripheral_id')
            ? collect($request->input('peripheral_id'))->map(fn ($id) => (int) $id)->filter()->values()->all()
            : ($request->integer('peripheral_id') ? [$request->integer('peripheral_id')] : []);

        return view('sop_documents.create', compact(
            'types',
            'type',
            'assets',
            'employees',
            'locations',
            'statuses',
            'peripherals',
            'mutationLogs',
            'preselectedAssetIds',
            'preselectedLogIds',
            'preselectedPeripheralIds'
        ));
    }

    // =========================================================
    // STORE
    // =========================================================

    public function store(StoreSopDocumentRequest $request): RedirectResponse
    {
        $valid = $request->validated();
        $type  = SopDocumentType::from($valid['document_type']);

        DB::beginTransaction();
        try {
            $documentNumber = $this->generateNumber($type);

            if ($type === SopDocumentType::BeritaAcara) {
                $mutationLogIds = $valid['mutation_log_ids'] ?? [];
                $firstLogId = $mutationLogIds[0] ?? null;
                $assetIds = AssetMutationLog::whereIn('id', $mutationLogIds)
                    ->pluck('asset_id')
                    ->unique()
                    ->values()
                    ->all();
                $firstAssetId = $assetIds[0] ?? null;
                $data = array_merge($valid['data'] ?? [], [
                    'mutation_log_ids' => $mutationLogIds,
                    'asset_ids'        => $assetIds,
                ]);
            } else {
                $assetIds = array_values(array_filter($valid['asset_ids'] ?? [], fn ($v) => $v !== null && $v !== ''));
                $firstAssetId = $assetIds[0] ?? null;
                $mutationLogIds = [];
                $data = array_merge($valid['data'] ?? [], ['asset_ids' => $assetIds]);

                if ($type === SopDocumentType::TandaTerima) {
                    $data['peripheral_ids'] = array_values(array_filter(
                        $valid['peripheral_ids'] ?? [],
                        fn ($v) => $v !== null && $v !== ''
                    ));
                }
            }

            $document = SopDocument::create([
                'document_type'         => $type,
                'document_number'       => $documentNumber,
                'asset_id'              => $firstAssetId,
                'mutation_log_id'       => $mutationLogIds[0] ?? null,
                'recipient_employee_id' => $valid['recipient_employee_id'] ?? null,
                'document_date'         => $valid['document_date'] ?? now()->toDateString(),
                'reason'                => $valid['reason'] ?? null,
                'notes'                 => $valid['notes'] ?? null,
                'data'                  => $data,
                'created_by'            => auth()->id(),
            ]);

            $this->storePdf($document);

            DB::commit();

            return redirect()
                ->route('documents.show', $document)
                ->with('success', "Dokumen {$document->document_number} berhasil dibuat.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat dokumen SOP.', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal membuat dokumen. Silakan coba lagi.');
        }
    }

    // =========================================================
    // SHOW
    // =========================================================

    public function show(SopDocument $document): View
    {
        $this->authorize('document.viewAny');

        $document->load([
            'asset.category', 'asset.brand', 'asset.vendor', 'asset.location', 'asset.assignedUser', 'asset.employee',
            'mutationLog.asset',
            'mutationLog.fromLocation:id,name',
            'mutationLog.toLocation:id,name',
            'mutationLog.fromAssignedUser:id,name',
            'mutationLog.toAssignedUser:id,name',
            'mutationLog.fromEmployee:id,name',
            'mutationLog.toEmployee:id,name',
            'mutationLog.performedBy:id,name',
            'recipientEmployee',
            'createdBy:id,name',
        ]);

        return view('sop_documents.show', compact('document'));
    }

    // =========================================================
    // PDF (unduh dari arsip)
    // =========================================================

    public function pdf(SopDocument $document)
    {
        $this->authorize('document.viewAny');

        if (! $document->pdf_path) {
            $this->storePdf($document);
        }

        return Storage::disk('public')->download($document->pdf_path, $document->document_number . '.pdf');
    }

    // =========================================================
    // PRINT (render PDF on-the-fly, tanpa menyimpan baru)
    // =========================================================

    public function print(SopDocument $document)
    {
        $this->authorize('document.viewAny');

        $viewData = $this->viewData($document);
        $pdf = Pdf::loadView($this->pdfView($document->document_type), $viewData);
        $pdf->setPaper('A4');

        return $pdf->stream($document->document_number . '.pdf');
    }

    // =========================================================
    // DESTROY
    // =========================================================

    public function destroy(SopDocument $document): RedirectResponse
    {
        $this->authorize('document.delete');

        DB::beginTransaction();
        try {
            if ($document->pdf_path) {
                Storage::disk('public')->delete($document->pdf_path);
            }
            $number = $document->document_number;
            $document->delete();
            DB::commit();

            return redirect()
                ->route('documents.index')
                ->with('success', "Dokumen {$number} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus dokumen SOP ID: {$document->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus dokumen. Silakan coba lagi.');
        }
    }

    // =========================================================
    // Helpers
    // =========================================================

    private function generateNumber(SopDocumentType $type): string
    {
        $year = now()->format('Y');
        $prefix = $type->prefix();

        $last = SopDocument::withTrashed()
            ->where('document_type', $type->value)
            ->where('document_number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('document_number')
            ->value('document_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $seq);
    }

    private function storePdf(SopDocument $document): void
    {
        $viewData = $this->viewData($document);
        $pdf = Pdf::loadView($this->pdfView($document->document_type), $viewData);
        $pdf->setPaper('A4');

        $relativePath = 'documents/' . $document->document_number . '.pdf';
        Storage::disk('public')->put($relativePath, $pdf->output());

        $document->update(['pdf_path' => $relativePath]);
    }

    private function pdfView(SopDocumentType $type): string
    {
        return match ($type) {
            SopDocumentType::Registrasi       => 'sop_documents.pdf.registrasi',
            SopDocumentType::TandaTerima      => 'sop_documents.pdf.tanda_terima',
            SopDocumentType::PermohonanMutasi => 'sop_documents.pdf.permohonan_mutasi',
            SopDocumentType::BeritaAcara      => 'sop_documents.pdf.berita_acara',
        };
    }

    /**
     * Data terkompilasi untuk merender template PDF / show.
     */
    private function viewData(SopDocument $document): array
    {
        $data  = $document->data ?? [];
        $log   = $document->mutationLog;
        $asset = $document->asset ?? $log?->asset;

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
            'peripherals' => $peripherals,
            'location' => $location,
        ];
    }
}