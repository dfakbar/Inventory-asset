<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetLoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('loan.viewAny');

        $loans = AssetLoan::with(['asset:id,asset_code,name', 'createdBy:id,name'])
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
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('loans.index', compact('loans'));
    }

    public function create(): View
    {
        $this->authorize('loan.create');

        $assets = Asset::whereDoesntHave('activeLoans')
            ->orderBy('name')
            ->get(['id', 'asset_code', 'name']);

        return view('loans.create', compact('assets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('loan.create');

        $data = $request->validate([
            'asset_id'              => [
                'required', 'integer',
                Rule::exists('assets', 'id'),
                function ($attribute, $value, $fail) {
                    if (Asset::where('id', $value)->whereHas('activeLoans')->exists()) {
                        $fail('Aset sedang dipinjam dan belum dikembalikan.');
                    }
                },
            ],
            'borrower_name'         => ['required', 'string', 'max:200'],
            'borrower_email'        => ['nullable', 'email', 'max:150'],
            'loan_date'             => ['required', 'date'],
            'expected_return_date'  => ['nullable', 'date', 'after_or_equal:loan_date'],
            'notes'                 => ['nullable', 'string', 'max:3000'],
        ]);

        DB::beginTransaction();
        try {
            $data['created_by'] = auth()->id();
            $loan = AssetLoan::create($data);

            $asset = $loan->asset;
            $asset->update([
                'assigned_to'   => null,
                'status'        => AssetStatus::InUse,
                'mutation_date' => $data['loan_date'],
            ]);

            Log::info("Check-out aset {$asset->asset_code} kepada {$data['borrower_name']}.", ['loan_id' => $loan->id]);

            DB::commit();

            return redirect()
                ->route('loans.index')
                ->with('success', "Aset {$asset->asset_code} berhasil di-check-out kepada {$data['borrower_name']}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal check-out aset.', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal melakukan check-out aset. Silakan coba lagi.');
        }
    }

    public function show(AssetLoan $loan): View
    {
        $this->authorize('loan.viewAny');

        $loan->load(['asset', 'createdBy:id,name']);

        return view('loans.show', compact('loan'));
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
            $asset->update(['status' => AssetStatus::Spare]);

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
