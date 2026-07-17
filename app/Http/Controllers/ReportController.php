<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('report.viewAny');

        return view('reports.index');
    }

    public function assetsPdf(Request $request)
    {
        $this->authorize('report.viewAny');

        $query = Asset::with(['category:id,name', 'location:id,name', 'vendor:id,name', 'brand:id,name'])
            ->when($request->category_id, fn ($q, $v) => $q->where('asset_category_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->location_id, fn ($q, $v) => $q->where('location_id', $v))
            ->orderBy('asset_code');

        $title = 'Laporan Seluruh Aset';
        if ($request->status) {
            $statusLabel = AssetStatus::tryFrom($request->status)?->label() ?? $request->status;
            $title = "Laporan Aset — Status: {$statusLabel}";
        }
        if ($request->category_id) {
            $cat = AssetCategory::find($request->category_id);
            $title .= $cat ? " — Kategori: {$cat->name}" : '';
        }

        $rows = '';
        $i = 0;
        $query->chunk(200, function ($assets) use (&$rows, &$i) {
            foreach ($assets as $asset) {
                $i++;
                $rows .= '<tr>'
                    . '<td class="text-center">' . $i . '</td>'
                    . '<td>' . e($asset->asset_code) . '</td>'
                    . '<td>' . e($asset->name) . '</td>'
                    . '<td>' . e($asset->category?->name ?? '—') . '</td>'
                    . '<td>' . e($asset->brand?->name ?? '—') . '</td>'
                    . '<td>' . e($asset->model ?? '—') . '</td>'
                    . '<td>' . e($asset->serial_number ?? '—') . '</td>'
                    . '<td>' . e($asset->location?->name ?? '—') . '</td>'
                    . '<td>' . e($asset->status->label()) . '</td>'
                    . '<td class="text-center">' . $asset->quantity . '</td>'
                    . '</tr>';
            }
        });

        $pdf = Pdf::loadView('reports.assets-pdf', compact('rows', 'title'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan-aset-' . now()->format('Ymd-His') . '.pdf');
    }

    public function categories()
    {
        $this->authorize('report.viewAny');

        $categories = AssetCategory::withCount('assets')->orderBy('name')->get();
        $totalAssets = Asset::count();

        $pdf = Pdf::loadView('reports.categories-pdf', compact('categories', 'totalAssets'));
        $pdf->setPaper('A4');

        return $pdf->download('laporan-kategori-' . now()->format('Ymd-His') . '.pdf');
    }
}
