<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $this->authorize('brand.viewAny');

        $brands = Brand::withCount('assets')->orderBy('name')->paginate(15);

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        $this->authorize('brand.create');

        return view('admin.brands.create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $this->authorize('brand.create');

        DB::beginTransaction();
        try {
            $brand = Brand::create($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.brands.index')
                ->with('success', "Merek {$brand->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat merek.', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal menyimpan merek. Silakan coba lagi.');
        }
    }

    public function show(Brand $brand): RedirectResponse
    {
        return redirect()->route('admin.brands.edit', $brand);
    }

    public function edit(Brand $brand): View
    {
        $this->authorize('brand.edit');

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->authorize('brand.edit');

        DB::beginTransaction();
        try {
            $brand->update($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.brands.index')
                ->with('success', "Merek {$brand->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update merek ID: {$brand->id}.", ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal memperbarui merek. Silakan coba lagi.');
        }
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('brand.delete');

        if ($brand->assets()->exists()) {
            return back()->with(
                'error',
                "Merek {$brand->name} tidak dapat dihapus karena masih digunakan oleh {$brand->assets()->count()} aset."
            );
        }

        DB::beginTransaction();
        try {
            $name = $brand->name;
            $brand->delete();
            DB::commit();

            return redirect()
                ->route('admin.brands.index')
                ->with('success', "Merek {$name} berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus merek ID: {$brand->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus merek. Silakan coba lagi.');
        }
    }
}
