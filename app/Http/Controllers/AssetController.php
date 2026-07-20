<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\Brand;
use App\Models\Employee;
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
use Picqer\Barcode\BarcodeGeneratorSVG;

class AssetController extends Controller
{
    public const DEFAULT_COLUMNS = [
        'kode_aset', 'nama', 'kategori', 'lokasi', 'pic', 'karyawan',
        'merek_model', 'serial_number', 'mac', 'vendor', 'status',
    ];

    public const COLUMN_LABELS = [
        'kode_aset'     => 'Kode Aset',
        'nama'          => 'Nama Aset',
        'kategori'      => 'Kategori',
        'lokasi'        => 'Lokasi',
        'pic'           => 'PIC (System)',
        'karyawan'      => 'Pengguna / Karyawan',
        'merek_model'   => 'Merek / Model',
        'serial_number' => 'Serial Number',
        'mac'           => 'MAC Address',
        'vendor'        => 'Vendor',
        'status'        => 'Status',
    ];

    // =========================================================
    // INDEX — semua user authenticated dengan permission view
    // =========================================================

    public function index(Request $request): View
    {
        $this->authorize('asset.viewAny');

        $assets = Asset::with(['category', 'location', 'assignedUser', 'vendor', 'brand', 'employee'])
            ->search($request->input('search'))
            ->ofStatus($request->input('status'))
            ->ofCategory($request->integer('category_id') ?: null)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = AssetCategory::orderBy('name')->get();
        $statuses   = AssetStatus::cases();
        $columns    = $this->getUserColumns();

        return view('assets.index', compact('assets', 'categories', 'statuses', 'columns'));
    }

    private function getUserColumns(): array
    {
        return self::getUserColumnsStatic();
    }

    public static function getUserColumnsStatic(): array
    {
        $user = auth()->user();
        if (!$user) {
            return self::DEFAULT_COLUMNS;
        }
        $saved = $user->settings['asset_columns'] ?? null;
        if ($saved && is_array($saved)) {
            return $saved;
        }
        return self::DEFAULT_COLUMNS;
    }

    private static function getExportDefs(string $col, string $format): array
    {
        $map = [
            'kode_aset' => [
                'csv' => [['header' => 'Kode Aset', 'data' => fn($a) => $a->asset_code]],
                'pdf' => [['header' => 'Kode', 'data' => fn($a) => e($a->asset_code)]],
            ],
            'nama' => [
                'csv' => [['header' => 'Nama', 'data' => fn($a) => $a->name]],
                'pdf' => [['header' => 'Nama Aset', 'data' => fn($a) => e($a->name)]],
            ],
            'kategori' => [
                'csv' => [['header' => 'Kategori', 'data' => fn($a) => $a->category?->name ?? '']],
                'pdf' => [['header' => 'Kategori', 'data' => fn($a) => e($a->category?->name ?? '—')]],
            ],
            'lokasi' => [
                'csv' => [['header' => 'Lokasi', 'data' => fn($a) => $a->location?->name ?? '']],
                'pdf' => [['header' => 'Lokasi', 'data' => fn($a) => e($a->location?->name ?? '—')]],
            ],
            'pic' => [
                'csv' => [['header' => 'PIC (System)', 'data' => fn($a) => $a->assignedUser?->name ?? '']],
                'pdf' => [['header' => 'PIC', 'data' => fn($a) => e($a->assignedUser?->name ?? '—')]],
            ],
            'karyawan' => [
                'csv' => [['header' => 'Pengguna / Karyawan', 'data' => fn($a) => $a->employee?->name ?? '']],
                'pdf' => [['header' => 'Pengguna', 'data' => fn($a) => e($a->employee?->name ?? '—')]],
            ],
            'merek_model' => [
                'csv' => [
                    ['header' => 'Merek', 'data' => fn($a) => $a->brand?->name ?? ''],
                    ['header' => 'Model', 'data' => fn($a) => $a->model ?? ''],
                ],
                'pdf' => [
                    ['header' => 'Merek', 'data' => fn($a) => e($a->brand?->name ?? '—')],
                    ['header' => 'Model', 'data' => fn($a) => e($a->model ?? '—')],
                ],
            ],
            'serial_number' => [
                'csv' => [['header' => 'Serial Number', 'data' => fn($a) => $a->serial_number ?? '']],
                'pdf' => [['header' => 'Serial Number', 'data' => fn($a) => e($a->serial_number ?? '—')]],
            ],
            'mac' => [
                'csv' => [['header' => 'MAC Address', 'data' => fn($a) => $a->mac_address ?? '']],
                'pdf' => [['header' => 'MAC Address', 'data' => fn($a) => e($a->mac_address ?? '—')]],
            ],
            'vendor' => [
                'csv' => [['header' => 'Vendor', 'data' => fn($a) => $a->vendor?->name ?? '']],
                'pdf' => [['header' => 'Vendor', 'data' => fn($a) => e($a->vendor?->name ?? '—')]],
            ],
            'status' => [
                'csv' => [['header' => 'Status', 'data' => fn($a) => $a->status->label()]],
                'pdf' => [['header' => 'Status', 'data' => fn($a) => e($a->status->label())]],
            ],
        ];

        return $map[$col][$format] ?? [];
    }

    public static function getExportHeaders(string $format): array
    {
        $columns = self::getUserColumnsStatic();
        $headers = [];
        foreach ($columns as $col) {
            foreach (self::getExportDefs($col, $format) as $def) {
                $headers[] = $def['header'];
            }
        }
        return $headers;
    }

    public static function getExportRow(Asset $asset, string $format): array
    {
        $columns = self::getUserColumnsStatic();
        $row = [];
        foreach ($columns as $col) {
            foreach (self::getExportDefs($col, $format) as $def) {
                $row[] = $def['data']($asset);
            }
        }
        return $row;
    }

    public function saveColumns(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('asset.viewAny');

        $valid = $request->validate([
            'columns'   => 'required|array',
            'columns.*' => 'string|in:' . implode(',', self::DEFAULT_COLUMNS),
        ]);

        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings['asset_columns'] = $valid['columns'];
        $user->settings = $settings;
        $user->save();

        return response()->json(['success' => true]);
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
        $employees  = Employee::active()->orderBy('name')->get();
        $statuses   = AssetStatus::cases();

        return view('assets.create', compact('categories', 'brands', 'vendors', 'locations', 'users', 'employees', 'statuses'));
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

            $data['assigned_to'] = auth()->id();

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

        $asset->load(['category', 'location', 'assignedUser', 'vendor', 'brand', 'employee']);

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
        $employees  = Employee::active()->orderBy('name')->get();
        $statuses   = AssetStatus::cases();

        return view('assets.edit', compact('asset', 'categories', 'brands', 'vendors', 'locations', 'users', 'employees', 'statuses'));
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
                $data = array_intersect_key($data, array_flip(['location_id', 'mutation_date', 'status', 'employee_id', 'notes']));
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

        if (AssetLoan::where('asset_id', $asset->id)->whereNull('returned_at')->exists()) {
            return back()->with('error', "Aset {$asset->asset_code} sedang dipinjam dan tidak dapat dihapus.");
        }

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

        $responseHeaders = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $csvHeaders = self::getExportHeaders('csv');

        $callback = function () use ($request, $csvHeaders) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $csvHeaders);

            Asset::with(['category', 'brand', 'location', 'vendor', 'assignedUser', 'employee'])
                ->search($request->input('search'))
                ->ofStatus($request->input('status'))
                ->ofCategory($request->integer('category_id') ?: null)
                ->orderBy('asset_code')
                ->chunk(200, function ($assets) use ($handle) {
                    foreach ($assets as $asset) {
                        fputcsv($handle, self::getExportRow($asset, 'csv'));
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $responseHeaders);
    }

    // =========================================================
    // IMPORT CSV
    // =========================================================

    private const CSV_HEADERS = [
        'Kode Aset', 'Nama', 'Kategori', 'Merek', 'Model',
        'Serial Number', 'MAC Address', 'Lokasi', 'Vendor', 'Status',
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
            foreach (self::CSV_HEADERS as $name) {
                $key = array_search($name, $normalizedHeaders, true);
                $headerMap[$name] = $key !== false ? $key : null;
            }
        }

        $rowNumber = 1;
        $imported = 0;
        $errors = [];

        $categories = AssetCategory::pluck('id', 'name');
        $brands = [];
        $vendors = Vendor::pluck('id', 'name');
        $locations = Location::pluck('id', 'name');
        $existingSerials = Asset::whereNotNull('serial_number')->pluck('serial_number')->toArray();
        $importedSerials = [];

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $data = array_map('trim', $row);

                $col = fn (string $name): string =>
                    isset($headerMap[$name]) && $headerMap[$name] !== null
                        ? ($data[$headerMap[$name]] ?? '')
                        : '';

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

                $vendorId = null;
                $vendorName = $col('Vendor');
                if (!empty($vendorName)) {
                    if (!isset($vendors[$vendorName])) {
                        $vendors[$vendorName] = Vendor::firstOrCreate(['name' => $vendorName])->id;
                    }
                    $vendorId = $vendors[$vendorName];
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

                $serialNumber = $col('Serial Number') ?: null;
                if (!empty($serialNumber)) {
                    if (in_array($serialNumber, $existingSerials, true) || in_array($serialNumber, $importedSerials, true)) {
                        $errors[] = "Serial Number '{$serialNumber}' sudah digunakan (baris {$rowNumber}), dilewati.";
                        continue;
                    }
                    $importedSerials[] = $serialNumber;
                }

                $macAddress = $col('MAC Address') ?: null;
                if (!empty($macAddress)) {
                    if (!preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macAddress)) {
                        $errors[] = "Format MAC Address tidak valid: {$macAddress} (baris {$rowNumber}), dilewati.";
                        $macAddress = null;
                    }
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

                try {
                    DB::beginTransaction();

                    $assetData = [
                        'name'              => $col('Nama'),
                        'asset_category_id' => $categoryId,
                        'brand_id'          => $brandId,
                        'vendor_id'         => $vendorId,
                        'model'             => $col('Model') ?: null,
                        'serial_number'     => $serialNumber,
                        'mac_address'       => $macAddress,
                        'location_id'       => $locationId,
                        'assigned_to'       => auth()->id(),
                        'status'            => $status->value,
                        'quantity'          => $quantity,
                        'notes'             => $col('Catatan') ?: null,
                    ];

                    if ($purchaseDate) {
                        $assetData['purchase_date'] = $purchaseDate;
                    }
                    if ($harga !== null && $harga !== '' && is_numeric($harga)) {
                        $assetData['purchase_price'] = (float) $harga;
                    }

                    Asset::create($assetData);

                    DB::commit();
                    $imported++;
                } catch (\Throwable $e) {
                    DB::rollBack();
                    $errors[] = "Gagal memproses baris {$rowNumber}: {$e->getMessage()}";
                    Log::error('Gagal impor baris CSV.', ['row' => $rowNumber, 'error' => $e->getMessage()]);
                }
            }

            fclose($handle);

            $message = "Berhasil mengimpor {$imported} aset.";
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', array_slice($errors, 0, 5));
            }

            return redirect()
                ->route('assets.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            Log::error('Gagal impor CSV.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengimpor CSV. Silakan periksa format file dan coba lagi.');
        }
    }

    public function exportCsvTemplate()
    {
        $this->authorize('asset.create');

        $headers = self::CSV_HEADERS;
        $example = [
            'ASSET-001', 'Monitor Dell', 'Monitor', 'Dell', 'UltraSharp U2723QE',
            'SN-2026-001', '00:1A:2B:3C:4D:5E', 'Jakarta', 'PT Supplier', 'Spare',
            '2026-01-15', '5000000', '1', 'Catatan contoh',
        ];

        $callback = function () use ($headers, $example) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-aset.csv"',
        ]);
    }

    // =========================================================
    // QR CODE
    // =========================================================

    public function qrCode(Asset $asset)
    {
        $this->authorize('asset.viewAny');

        $renderer = new ImageRenderer(
            new RendererStyle(400, 2),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCode = $writer->writeString(route('public.track', ['search' => $asset->asset_code]));

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

        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode($asset->asset_code, $generator::TYPE_CODE_128, 2, 80);

        return response($barcode, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => "inline; filename=\"{$asset->asset_code}-barcode.svg\"",
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
        if ($count < 1 || $count > 4) {
            $count = 1;
        }

        if (!in_array($type, ['qr', 'barcode'])) {
            $type = 'qr';
        }

        return view('assets.print-code', compact('asset', 'type', 'count'));
    }
}
