<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetMutationLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // ── 1. Statistik Utama (cache 5 menit) ────────────────────────────
        $stats = Cache::remember('dashboard.stats', 300, function () {
            return [
                'total_assets'    => Asset::count(),
                'in_use'          => Asset::where('status', AssetStatus::InUse->value)->count(),
                'spare'           => Asset::where('status', AssetStatus::Spare->value)->count(),
                'service'         => Asset::where('status', AssetStatus::Service->value)->count(),
                'broken'          => Asset::where('status', AssetStatus::Broken->value)->count(),
                'broken_check'    => Asset::where('status', AssetStatus::BrokenCheck->value)->count(),
                'disposal'        => Asset::where('status', AssetStatus::Disposal->value)->count(),
                'total_users'     => User::count(),
                'total_locations' => Location::count(),
                'total_value'     => Asset::whereNotNull('purchase_price')->sum('purchase_price'),
            ];
        });

        // Aset bermasalah: service + broken + broken-check
        $stats['problematic'] = $stats['service'] + $stats['broken'] + $stats['broken_check'];

        // ── 2. Data Grafik Distribusi Status (Doughnut) ────────────────────
        $statusChart = [
            'labels' => [],
            'data'   => [],
            'colors' => [],
        ];

        $colorMap = [
            AssetStatus::InUse->value       => '#198754', // success
            AssetStatus::Spare->value        => '#0dcaf0', // info
            AssetStatus::Service->value      => '#ffc107', // warning
            AssetStatus::Broken->value       => '#dc3545', // danger
            AssetStatus::Disposal->value     => '#6c757d', // secondary
            AssetStatus::BrokenCheck->value  => '#343a40', // dark
        ];

        $statKeys = [
            AssetStatus::InUse->value       => 'in_use',
            AssetStatus::Spare->value        => 'spare',
            AssetStatus::Service->value      => 'service',
            AssetStatus::Broken->value       => 'broken',
            AssetStatus::BrokenCheck->value  => 'broken_check',
            AssetStatus::Disposal->value     => 'disposal',
        ];

        foreach (AssetStatus::cases() as $status) {
            $count = $stats[$statKeys[$status->value]];
            if ($count > 0) {
                $statusChart['labels'][] = $status->label();
                $statusChart['data'][]   = $count;
                $statusChart['colors'][] = $colorMap[$status->value];
            }
        }

        // ── 3. Data Grafik Distribusi Kategori (Bar) ───────────────────────
        $categoryChart = Cache::remember('dashboard.category_chart', 300, function () {
            return Asset::select('asset_category_id', DB::raw('count(*) as total'))
                ->with('category:id,name,abbreviation')
                ->groupBy('asset_category_id')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->map(fn ($item) => [
                    'label' => $item->category?->abbreviation ?? $item->category?->name ?? 'N/A',
                    'total' => $item->total,
                ]);
        });

        // ── 4. Aset Terbaru ────────────────────────────────────────────────
        $latestAssets = Asset::with(['category', 'location', 'assignedUser'])
            ->latest()
            ->limit(5)
            ->get();

        // ── 5. Log Mutasi Terbaru ──────────────────────────────────────────
        $recentMutations = AssetMutationLog::with([
                'asset:id,asset_code,name',
                'performedBy:id,name',
                'fromLocation:id,name',
                'toLocation:id,name',
                'fromAssignedUser:id,name',
                'toAssignedUser:id,name',
            ])
            ->latest()
            ->limit(10)
            ->get();

        // ── 6. Trend Mutasi Per Bulan (6 bulan terakhir) ──────────────────
        $mutationTrend = Cache::remember('dashboard.mutation_trend', 300, function () {
            return AssetMutationLog::where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->get()
                ->groupBy(fn ($log) => $log->created_at->format('Y-m'))
                ->map(fn ($group) => (object) ['month' => $group->first()->created_at->format('Y-m'), 'total' => $group->count()])
                ->sortBy('month')
                ->values();
        });

        // Isi bulan yang tidak ada data dengan 0
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->locale('id')->translatedFormat('M Y');
            $found = $mutationTrend->firstWhere('month', $month);
            $trendData[] = [
                'label' => $label,
                'total' => $found ? $found->total : 0,
            ];
        }

        return view('dashboard', compact(
            'stats',
            'statusChart',
            'categoryChart',
            'latestAssets',
            'recentMutations',
            'trendData',
        ));
    }
}
