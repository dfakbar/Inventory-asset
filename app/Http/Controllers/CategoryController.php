<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('category.viewAny');

        $categories = AssetCategory::withCount('assets')->orderBy('name')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('category.create');

        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('category.create');

        DB::beginTransaction();
        try {
            $category = AssetCategory::create($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Kategori {$category->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat kategori.', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal menyimpan kategori. Silakan coba lagi.');
        }
    }

    public function show(AssetCategory $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(AssetCategory $category): View
    {
        $this->authorize('category.edit');

        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, AssetCategory $category): RedirectResponse
    {
        $this->authorize('category.edit');

        DB::beginTransaction();
        try {
            $category->update($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Kategori {$category->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update kategori ID: {$category->id}.", ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal memperbarui kategori. Silakan coba lagi.');
        }
    }

    public function destroy(AssetCategory $category): RedirectResponse
    {
        $this->authorize('category.delete');

        if ($category->assets()->exists()) {
            return back()->with(
                'error',
                "Kategori {$category->name} tidak dapat dihapus karena masih digunakan oleh {$category->assets()->count()} aset."
            );
        }

        DB::beginTransaction();
        try {
            $name = $category->name;
            $category->delete();
            DB::commit();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Kategori {$name} berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus kategori ID: {$category->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus kategori. Silakan coba lagi.');
        }
    }
}
