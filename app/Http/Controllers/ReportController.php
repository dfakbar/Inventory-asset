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

        $categories = AssetCategory::orderBy('name')->get();

        return view('reports.index', compact('categories'));
    }

    public function assetsPdf(Request $request)
    {
        $this->authorize('report.viewAny');

        $query = Asset::with(['category:id,name', 'location:id,name', 'vendor:id,name', 'brand:id,name', 'assignedUser:id,name', 'employee:id,name'])
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

        $pdfHeaders = AssetController::getExportHeaders('pdf');
        $rowsHtml = '';
        $i = 0;
        $query->chunk(200, function ($assets) use (&$rowsHtml, &$i) {
            foreach ($assets as $asset) {
                $i++;
                $rowsHtml .= '<tr>'
                    . '<td class="text-center">' . $i . '</td>';
                $rowData = AssetController::getExportRow($asset, 'pdf');
                foreach ($rowData as $cell) {
                    $rowsHtml .= '<td>' . $cell . '</td>';
                }
                $rowsHtml .= '</tr>';
            }
        });

        $pdf = Pdf::loadView('reports.assets-pdf', compact('pdfHeaders', 'rowsHtml', 'title'));
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
