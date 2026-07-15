<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Brand;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Picqer\Barcode\BarcodeGeneratorPNG;

class AssetController extends Controller
{
    // =========================================================
    // INDEX — semua user authenticated dengan permission view
    // =========================================================

    public function index(Request $request): View
    {
        $this->authorize('asset.viewAny');

        $assets = Asset::with(['category', 'location', 'assignedUser', 'vendor', 'brand'])
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
        $brands     = Brand::orderBy('name')->get();
        $vendors    = Vendor::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();
        $users      = User::orderBy('name')->get();
        $statuses   = AssetStatus::cases();

        return view('assets.create', compact('categories', 'brands', 'vendors', 'locations', 'users', 'statuses'));
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
                ->with('success', "Aset {$asset->asset_code} ({$asset->name}) berhasil ditambahkan.");

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

        $asset->load(['category', 'location', 'assignedUser', 'vendor', 'brand']);

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
        $brands     = Brand::orderBy('name')->get();
        $vendors    = Vendor::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();
        $users      = User::orderBy('name')->get();
        $statuses   = AssetStatus::cases();

        return view('assets.edit', compact('asset', 'categories', 'brands', 'vendors', 'locations', 'users', 'statuses'));
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
                ->with('success', "Aset {$asset->asset_code} berhasil diperbarui.");

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
                ->with('success', "Aset {$assetCode} ({$assetName}) berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus aset ID: {$asset->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus aset. Silakan coba lagi.');
        }
    }

    // =========================================================
    // EXPORT CSV
    // =========================================================

    public function exportCsv(Request $request)
    {
        $this->authorize('asset.viewAny');

        $filename = 'export-aset-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Aset', 'Nama', 'Kategori', 'Merek', 'Model',
                'Serial Number', 'Lokasi', 'Vendor', 'Status',
                'Tanggal Pembelian', 'Harga Pembelian', 'Jumlah', 'Catatan',
            ]);

            Asset::with(['category', 'brand', 'location', 'vendor'])
                ->search($request->input('search'))
                ->ofStatus($request->input('status'))
                ->ofCategory($request->integer('category_id') ?: null)
                ->orderBy('asset_code')
                ->chunk(200, function ($assets) use ($handle) {
                    foreach ($assets as $asset) {
                        fputcsv($handle, [
                            $asset->asset_code,
                            $asset->name,
                            $asset->category?->name ?? '',
                            $asset->brand?->name ?? '',
                            $asset->model ?? '',
                            $asset->serial_number ?? '',
                            $asset->location?->name ?? '',
                            $asset->vendor?->name ?? '',
                            $asset->status->label(),
                            $asset->purchase_date?->format('Y-m-d') ?? '',
                            $asset->purchase_price ?? '',
                            $asset->quantity,
                            $asset->notes ?? '',
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================
    // IMPORT CSV
    // =========================================================

    private const CSV_HEADERS = [
        'Kode Aset', 'Nama', 'Kategori', 'Merek', 'Model',
        'Serial Number', 'Lokasi', 'Vendor', 'Status',
        'Tanggal Pembelian', 'Harga Pembelian', 'Jumlah', 'Catatan',
    ];

    public function importCsv(Request $request): RedirectResponse
    {
        $this->authorize('asset.create');

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $headerRow = fgetcsv($handle);
        $headerMap = [];
        if (is_array($headerRow)) {
            $normalizedHeaders = array_map('trim', $headerRow);
            foreach (self::CSV_HEADERS as $index => $name) {
                $key = array_search($name, $normalizedHeaders, true);
                $headerMap[$name] = $key !== false ? $key : $index;
            }
        }

        $rowNumber = 1;
        $imported = 0;
        $errors = [];

        $categories = AssetCategory::pluck('id', 'name');
        $brands = [];
        $locations = Location::pluck('id', 'name');

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $data = array_map('trim', $row);

                $col = fn (string $name): string => $data[$headerMap[$name]] ?? '';

                if (empty($col('Nama'))) {
                    continue;
                }

                $categoryName = $col('Kategori');
                if (empty($categoryName)) {
                    $errors[] = "Kategori tidak boleh kosong (baris {$rowNumber}).";
                    continue;
                }
                $categoryId = $categories[$categoryName] ?? null;
                if (!$categoryId) {
                    $errors[] = "Kategori tidak ditemukan: {$categoryName} (baris {$rowNumber}).";
                    continue;
                }

                $brandId = null;
                $brandName = $col('Merek');
                if (!empty($brandName)) {
                    if (!isset($brands[$brandName])) {
                        $brands[$brandName] = Brand::firstOrCreate(['name' => $brandName])->id;
                    }
                    $brandId = $brands[$brandName];
                }

                $locationId = null;
                $locationName = $col('Lokasi');
                if (!empty($locationName)) {
                    $locationId = $locations[$locationName] ?? null;
                }

                $statusRaw = $col('Status');
                $status = AssetStatus::tryFrom($statusRaw);
                if (!$status) {
                    $errors[] = "Status '{$statusRaw}' tidak valid (baris {$rowNumber}), gunakan default Spare.";
                    $status = AssetStatus::Spare;
                }

                $purchaseDate = null;
                $purchaseDateRaw = $col('Tanggal Pembelian');
                if (!empty($purchaseDateRaw)) {
                    try {
                        $purchaseDate = Carbon::parse($purchaseDateRaw)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $errors[] = "Format tanggal pembelian tidak valid (baris {$rowNumber}), dilewati.";
                    }
                }

                $quantity = $col('Jumlah');
                if ($quantity !== '' && (!is_numeric($quantity) || (int) $quantity < 1 || (int) $quantity > 9999)) {
                    $errors[] = "Jumlah harus angka antara 1-9999 (baris {$rowNumber}), gunakan default 1.";
                    $quantity = 1;
                } else {
                    $quantity = $quantity !== '' ? (int) $quantity : 1;
                }

                $harga = $col('Harga Pembelian');
                if ($harga !== '' && (!is_numeric($harga) || (float) $harga < 0)) {
                    $errors[] = "Harga pembelian tidak valid (baris {$rowNumber}), dilewati.";
                    $harga = null;
                }

                $assetData = [
                    'name'              => $col('Nama'),
                    'asset_category_id' => $categoryId,
                    'brand_id'          => $brandId,
                    'model'             => $col('Model') ?: null,
                    'serial_number'     => $col('Serial Number') ?: null,
                    'location_id'       => $locationId,
                    'status'            => $status->value,
                    'quantity'          => $quantity,
                    'notes'             => $col('Catatan') ?: null,
                ];

                if ($purchaseDate) {
                    $assetData['purchase_date'] = $purchaseDate;
                }
                if (!empty($harga) && $harga !== null) {
                    $assetData['purchase_price'] = (float) $harga;
                }

                Asset::create($assetData);
                $imported++;
            }

            fclose($handle);
            DB::commit();

            $message = "Berhasil mengimpor {$imported} aset.";
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', array_slice($errors, 0, 5));
            }

            return redirect()
                ->route('assets.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }
            Log::error('Gagal impor CSV.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengimpor CSV. Silakan periksa format file dan coba lagi.');
        }
    }

    // =========================================================
    // QR CODE
    // =========================================================

    public function qrCode(Asset $asset)
    {
        $this->authorize('asset.viewAny');

        $url = route('assets.show', $asset);

        $renderer = new ImageRenderer(
            new RendererStyle(400, 2),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($url);

        return response($qrCode, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => "inline; filename=\"{$asset->asset_code}-qr.svg\"",
        ]);
    }

    // =========================================================
    // BARCODE
    // =========================================================

    public function barcode(Asset $asset)
    {
        $this->authorize('asset.viewAny');

        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($asset->asset_code, $generator::TYPE_CODE_128, 2, 80);

        return response($barcode, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => "inline; filename=\"{$asset->asset_code}-barcode.png\"",
        ]);
    }

    // =========================================================
    // PRINT QR / BARCODE
    // =========================================================

    public function printCode(Asset $asset)
    {
        $this->authorize('asset.viewAny');

        $type = request('type', 'qr');
        $count = (int) request('count', 1);
        if ($count < 1 || $count > 30) {
            $count = 1;
        }

        if (!in_array($type, ['qr', 'barcode'])) {
            $type = 'qr';
        }

        return view('assets.print-code', compact('asset', 'type', 'count'));
    }
}
