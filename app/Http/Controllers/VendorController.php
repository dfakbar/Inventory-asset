<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        $this->authorize('vendor.viewAny');

        $vendors = Vendor::orderBy('name')->paginate(15);

        return view('admin.vendors.index', compact('vendors'));
    }

    public function create(): View
    {
        $this->authorize('vendor.create');

        return view('admin.vendors.create');
    }

    public function store(StoreVendorRequest $request): RedirectResponse
    {
        $this->authorize('vendor.create');

        DB::beginTransaction();
        try {
            $vendor = Vendor::create($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.vendors.index')
                ->with('success', "Vendor {$vendor->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat vendor.', ['error' => $e->getMessage()]);

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
