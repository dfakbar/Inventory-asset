<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssetController extends Controller
{
    // =========================================================
    // INDEX — semua user authenticated dengan permission view
    // =========================================================

    public function index(Request $request): View
    {
        $this->authorize('asset.viewAny');

        $assets = Asset::with(['category', 'location', 'assignedUser'])
            ->search($request->input('search'))
            ->ofStatus($request->input('status'))
            ->ofCategory($request->integer('category_id') ?: null)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = AssetCategory::orderBy('name')->get();
        $statuses   = AssetStatus::cases();

        return view('assets.index', compact('assets', 'categories', 'statuses'));
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function create(): View
    {
        $this->authorize('asset.create');

        $categories = AssetCategory::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();
        $users      = User::orderBy('name')->get();
        $statuses   = AssetStatus::cases();

        return view('assets.create', compact('categories', 'locations', 'users', 'statuses'));
    }

    // =========================================================
    // STORE
    // =========================================================

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $this->authorize('asset.create');

        DB::beginTransaction();
        try {
            $data = $request->safe()->except('image');

            if (! auth()->user()->can('asset.manage_finances')) {
                unset($data['purchase_date']);
                unset($data['purchase_price']);
            }

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('assets/images', 'public');
            }

            /** @var Asset $asset */
            $asset = Asset::create($data);

            DB::commit();

            return redirect()
                ->route('assets.show', $asset)
                ->with('success', "Aset <strong>{$asset->asset_code}</strong> ({$asset->name}) berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan aset.', ['error' => $e->getMessage()]);

            return back()->withInput()
                ->with('error', 'Gagal menyimpan aset. Silakan coba lagi.');
        }
    }

    // =========================================================
    // SHOW
    // =========================================================

    public function show(Asset $asset): View
    {
        $this->authorize('asset.viewAny');

        $asset->load(['category', 'location', 'assignedUser']);

        return view('assets.show', compact('asset'));
    }

    // =========================================================
    // EDIT
    // =========================================================

    public function edit(Asset $asset): View
    {
        if (! auth()->user()->can('asset.edit') && ! auth()->user()->can('asset.mutate')) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit aset ini.');
        }

        $categories = AssetCategory::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();
        $users      = User::orderBy('name')->get();
        $statuses   = AssetStatus::cases();

        return view('assets.edit', compact('asset', 'categories', 'locations', 'users', 'statuses'));
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        if (! auth()->user()->can('asset.edit') && ! auth()->user()->can('asset.mutate')) {
            abort(403, 'Anda tidak memiliki akses untuk memperbarui aset ini.');
        }

        DB::beginTransaction();
        try {
            $data = $request->safe()->except(['image', 'remove_image']);

            // Jika user tidak memiliki akses finansial, jangan ubah purchase_date dan purchase_price
            if (! auth()->user()->can('asset.manage_finances')) {
                unset($data['purchase_date']);
                unset($data['purchase_price']);
            }

            // Jika user HANYA memiliki akses mutasi (tanpa edit umum), batasi field yang boleh diperbarui
            if (! auth()->user()->can('asset.edit') && auth()->user()->can('asset.mutate')) {
                $data = array_intersect_key($data, array_flip(['location_id', 'mutation_date', 'status', 'assigned_to']));
            }

            if ($request->boolean('remove_image') && $asset->image) {
                Storage::disk('public')->delete($asset->image);
                $data['image'] = null;
            }

            if ($request->hasFile('image')) {
                if ($asset->image) {
                    Storage::disk('public')->delete($asset->image);
                }
                $data['image'] = $request->file('image')->store('assets/images', 'public');
            }

            $asset->update($data);
            DB::commit();

            return redirect()
                ->route('assets.show', $asset)
                ->with('success', "Aset <strong>{$asset->asset_code}</strong> berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update aset ID: {$asset->id}.", ['error' => $e->getMessage()]);

            return back()->withInput()
                ->with('error', 'Gagal memperbarui aset. Silakan coba lagi.');
        }
    }

    // =========================================================
    // DESTROY
    // =========================================================

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('asset.delete');

        DB::beginTransaction();
        try {
            $assetCode = $asset->asset_code;
            $assetName = $asset->name;
            $asset->delete();
            DB::commit();

            return redirect()
                ->route('assets.index')
                ->with('success', "Aset <strong>{$assetCode}</strong> ({$assetName}) berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus aset ID: {$asset->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus aset. Silakan coba lagi.');
        }
    }
}
