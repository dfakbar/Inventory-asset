<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\SopDocumentType;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\SopDocument;
use App\Services\SopDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(private SopDocumentService $documents)
    {
    }
    public function index(Request $request): View
    {
        $this->authorize('loan.viewAny');

        $query = AssetLoan::with(['asset:id,asset_code,name', 'createdBy:id,name', 'sopDocument:id,loan_id,document_number'])
            ->when($request->boolean('active_only'), fn ($q) => $q->whereNull('returned_at'))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where(function ($q) use ($term) {
                    $q->where('borrower_name', 'like', "%{$term}%")
                      ->orWhere('borrower_email', 'like', "%{$term}%")
                      ->orWhereHas('asset', fn ($q) => $q
                          ->where('asset_code', 'like', "%{$term}%")
                          ->orWhere('name', 'like', "%{$term}%")
                      );
                });
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('loan_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('loan_date', '<=', $request->date_to))
            ->latest();

        $loans = $this->paginateQuery($request, $query);

        return view('loans.index', compact('loans'));
    }

    public function create(Request $request): View
    {
        $this->authorize('loan.create');

        $assets = Asset::whereDoesntHave('activeLoans')
            ->orderBy('name')
            ->get(['id', 'asset_code', 'name']);

        if ($request->wantsJson()) {
            return view('loans._create_form', compact('assets'));
        }

        return view('loans.create', compact('assets'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('loan.create');

        $data = $request->validate([
            'asset_id'              => ['required', 'integer', Rule::exists('assets', 'id')],
            'borrower_name'         => ['required', 'string', 'max:200'],
            'borrower_email'        => ['nullable', 'email', 'max:150'],
            'loan_date'             => ['required', 'date'],
            'expected_return_date'  => ['nullable', 'date', 'after_or_equal:loan_date'],
            'notes'                 => ['nullable', 'string', 'max:3000'],
        ]);

        DB::beginTransaction();
        try {
            $asset = Asset::where('id', $data['asset_id'])->lockForUpdate()->firstOrFail();

            if ($asset->activeLoans()->exists()) {
                DB::rollBack();

                if ($request->wantsJson()) {
                    return response()->json([
                        'errors' => ['asset_id' => ['Aset sedang dipinjam dan belum dikembalikan.']],
                    ], 422);
                }

                return back()->withInput()->withErrors([
                    'asset_id' => 'Aset sedang dipinjam dan belum dikembalikan.',
                ]);
            }

            $data['created_by'] = auth()->id();
            $loan = AssetLoan::create($data);

            // Form peminjaman (4 TTD) diterbitkan otomatis dalam transaksi yang sama.
            $form = $this->buildLoanForm($loan);
            $this->documents->archivePdf($form);

            $asset = $loan->asset;
            $asset->update([
                'assigned_to'   => null,
                'status'        => AssetStatus::InUse,
                'mutation_date' => $data['loan_date'],
            ]);

            Log::info("Check-out aset {$asset->asset_code} kepada {$data['borrower_name']}.", ['loan_id' => $loan->id]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success'         => true,
                    'form_print_url'  => route('loans.form-print', $loan),
                    'form_pdf_url'    => route('loans.form-pdf', $loan),
                ]);
            }

            return redirect()
                ->route('loans.show', $loan)
                ->with('success', "Aset {$asset->asset_code} berhasil di-check-out kepada {$data['borrower_name']}. Form peminjaman {$form->document_number} telah diterbitkan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal check-out aset.', ['error' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Gagal melakukan check-out aset. Silakan coba lagi.'], 500);
            }

            return back()->withInput()->with('error', 'Gagal melakukan check-out aset. Silakan coba lagi.');
        }
    }

    public function show(AssetLoan $loan): View
    {
        $this->authorize('loan.viewAny');

        $loan->load(['asset', 'createdBy:id,name', 'sopDocument']);

        return view('loans.show', compact('loan'));
    }

    // =========================================================
    // FORM PEMINJAMAN (dokumen SOP, 4 TTD)
    // =========================================================

    public function printForm(AssetLoan $loan)
    {
        $this->authorize('loan.viewAny');

        $form = $this->resolveForm($loan);

        return $this->documents->renderPdf($form)->stream($form->document_number . '.pdf');
    }

    public function downloadForm(AssetLoan $loan)
    {
        $this->authorize('loan.viewAny');

        $form = $this->resolveForm($loan);

        if (! $form->pdf_path || ! Storage::disk('public')->exists($form->pdf_path)) {
            $this->documents->archivePdf($form->refresh());
        }

        return Storage::disk('public')->download($form->pdf_path, $form->document_number . '.pdf');
    }

    /**
     * Buatkan form susulan untuk peminjaman lama yang belum punya dokumen.
     */
    public function createForm(Request $request, AssetLoan $loan): RedirectResponse|JsonResponse
    {
        $this->authorize('loan.create');

        if ($loan->sopDocument()->exists()) {
            $message = "Form peminjaman {$loan->sopDocument->document_number} sudah ada.";

            if ($request->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }

            return back()->with('error', $message);
        }

        DB::beginTransaction();
        try {
            $form = $this->buildLoanForm($loan);
            $this->documents->archivePdf($form);

            DB::commit();

            session()->flash('success', "Form peminjaman {$form->document_number} berhasil dibuat.");

            if ($request->wantsJson()) {
                return response()->json([
                    'success'        => true,
                    'form_print_url' => route('loans.form-print', $loan),
                    'form_pdf_url'   => route('loans.form-pdf', $loan),
                ]);
            }

            return redirect()
                ->route('loans.show', $loan)
                ->with('success', "Form peminjaman {$form->document_number} berhasil dibuat.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal membuat form peminjaman untuk loan ID: {$loan->id}.", ['error' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Gagal membuat form peminjaman. Silakan coba lagi.'], 500);
            }

            return back()->with('error', 'Gagal membuat form peminjaman. Silakan coba lagi.');
        }
    }

    public function checkin(AssetLoan $loan): RedirectResponse
    {
        $this->authorize('loan.checkin');

        if ($loan->returned_at) {
            return back()->with('error', 'Aset ini sudah di-check-in sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $loan->update(['returned_at' => now()]);

            $asset = $loan->asset;
            $asset->update([
                'status'      => AssetStatus::Spare,
                'assigned_to' => auth()->id(),
            ]);

            Log::info("Check-in aset {$loan->asset->asset_code} dari {$loan->borrower_name}.", ['loan_id' => $loan->id]);

            DB::commit();

            return redirect()
                ->route('loans.index')
                ->with('success', "Aset {$loan->asset->asset_code} berhasil di-check-in dari {$loan->borrower_name}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal check-in aset.', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal melakukan check-in aset. Silakan coba lagi.');
        }
    }

    // =========================================================
    // Helpers
    // =========================================================

    /**
     * Bangun record dokumen form peminjaman untuk sebuah loan.
     * Dipanggil di dalam transaksi DB oleh store()/createForm().
     */
    private function buildLoanForm(AssetLoan $loan): SopDocument
    {
        $asset = $loan->asset;

        return SopDocument::create([
            'document_type'         => SopDocumentType::Peminjaman,
            'document_number'       => $this->documents->generateNumber(
                SopDocumentType::Peminjaman,
                $loan->loan_date?->format('Y-m-d')
            ),
            'asset_id'              => $asset?->id,
            'loan_id'               => $loan->id,
            'document_date'         => $loan->loan_date,
            'notes'                 => $loan->notes,
            'data'                  => [
                'loan_id'   => $loan->id,
                'asset_ids' => $asset ? [$asset->id] : [],
            ],
            'created_by'            => auth()->id(),
        ]);
    }

    private function resolveForm(AssetLoan $loan): SopDocument
    {
        $form = $loan->sopDocument()->first();

        abort_unless($form, 404, 'Form peminjaman belum dibuat untuk data ini.');

        return $form;
    }

    public function destroy(AssetLoan $loan): RedirectResponse
    {
        $this->authorize('loan.delete');

        DB::beginTransaction();
        try {
            $loan->delete();
            DB::commit();

            return redirect()
                ->route('loans.index')
                ->with('success', 'Data peminjaman berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal hapus data peminjaman.', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus data peminjaman.');
        }
    }
}
