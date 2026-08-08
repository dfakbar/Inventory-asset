<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('category.viewAny');

        $query = AssetCategory::withCount('assets')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->input('search'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('abbreviation', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->orderBy('name');

        $categories = $this->paginateQuery($request, $query);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(Request $request): View
    {
        $this->authorize('category.create');

        if ($request->wantsJson()) {
            return view('admin.categories._create_form');
        }

        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('category.create');

        DB::beginTransaction();
        try {
            $category = AssetCategory::create($request->validated());
            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Kategori {$category->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat kategori.', ['error' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Gagal menyimpan kategori. Silakan coba lagi.'], 500);
            }

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
