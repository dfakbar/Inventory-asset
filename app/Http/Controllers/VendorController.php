<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('vendor.viewAny');

        $query = Vendor::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->input('search'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('contact_person', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('name');

        $vendors = $this->paginateQuery($request, $query);

        return view('admin.vendors.index', compact('vendors'));
    }

    public function create(Request $request): View
    {
        $this->authorize('vendor.create');

        if ($request->wantsJson()) {
            return view('admin.vendors._create_form');
        }

        return view('admin.vendors.create');
    }

    public function store(StoreVendorRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('vendor.create');

        DB::beginTransaction();
        try {
            $vendor = Vendor::create($request->validated());
            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route('admin.vendors.index')
                ->with('success', "Vendor {$vendor->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat vendor.', ['error' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Gagal menyimpan vendor. Silakan coba lagi.'], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan vendor. Silakan coba lagi.');
        }
    }

    public function show(Vendor $vendor): RedirectResponse
    {
        return redirect()->route('admin.vendors.edit', $vendor);
    }

    public function edit(Vendor $vendor): View
    {
        $this->authorize('vendor.edit');

        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('vendor.edit');

        DB::beginTransaction();
        try {
            $vendor->update($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.vendors.index')
                ->with('success', "Vendor {$vendor->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update vendor ID: {$vendor->id}.", ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal memperbarui vendor. Silakan coba lagi.');
        }
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $this->authorize('vendor.delete');

        DB::beginTransaction();
        try {
            $name = $vendor->name;
            $vendor->delete();
            DB::commit();

            return redirect()
                ->route('admin.vendors.index')
                ->with('success', "Vendor {$name} berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus vendor ID: {$vendor->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus vendor. Silakan coba lagi.');
        }
    }
}
