<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('location.viewAny');

        $query = Location::withCount('assets')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->input('search'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('department', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->orderBy('name');

        $locations = $this->paginateQuery($request, $query);

        return view('admin.locations.index', compact('locations'));
    }

    public function create(Request $request): View
    {
        $this->authorize('location.create');

        if ($request->wantsJson()) {
            return view('admin.locations._create_form');
        }

        return view('admin.locations.create');
    }

    public function store(StoreLocationRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('location.create');

        DB::beginTransaction();
        try {
            $location = Location::create($request->validated());
            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route('admin.locations.index')
                ->with('success', "Lokasi {$location->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat lokasi.', ['error' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Gagal menyimpan lokasi. Silakan coba lagi.'], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan lokasi. Silakan coba lagi.');
        }
    }

    public function show(Location $location): RedirectResponse
    {
        return redirect()->route('admin.locations.edit', $location);
    }

    public function edit(Location $location): View
    {
        $this->authorize('location.edit');

        return view('admin.locations.edit', compact('location'));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $this->authorize('location.edit');

        DB::beginTransaction();
        try {
            $location->update($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.locations.index')
                ->with('success', "Lokasi {$location->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update lokasi ID: {$location->id}.", ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal memperbarui lokasi. Silakan coba lagi.');
        }
    }

    public function destroy(Location $location): RedirectResponse
    {
        $this->authorize('location.delete');

        if ($location->assets()->exists()) {
            return back()->with(
                'error',
                "Lokasi {$location->name} tidak dapat dihapus karena masih digunakan oleh {$location->assets()->count()} aset."
            );
        }

        DB::beginTransaction();
        try {
            $name = $location->name;
            $location->delete();
            DB::commit();

            return redirect()
                ->route('admin.locations.index')
                ->with('success', "Lokasi {$name} berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus lokasi ID: {$location->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus lokasi. Silakan coba lagi.');
        }
    }
}
