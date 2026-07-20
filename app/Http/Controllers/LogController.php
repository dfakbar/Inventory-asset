<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AssetMutationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LogController extends Controller
{
    public function assetLog(Request $request): View
    {
        $this->authorize('asset.viewAny');

        $logs = ActivityLog::with('user')
            ->where('model_type', 'App\Models\Asset')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where(function ($q) use ($term) {
                    $q->where('description', 'like', "%{$term}%")
                      ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $trashedCount = ActivityLog::onlyTrashed()
            ->where('model_type', 'App\Models\Asset')
            ->count();

        return view('admin.logs.asset', compact('logs', 'trashedCount'));
    }

    public function mutationLog(Request $request): View
    {
        $this->authorize('asset.viewAny');

        $logs = AssetMutationLog::with([
            'asset:id,asset_code,name',
            'performedBy:id,name',
            'fromLocation:id,name',
            'toLocation:id,name',
            'fromAssignedUser:id,name',
            'toAssignedUser:id,name',
            'fromEmployee:id,name',
            'toEmployee:id,name',
        ])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->whereHas('asset', fn ($q) => $q
                    ->where('asset_code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                );
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('mutation_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('mutation_date', '<=', $request->date_to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $trashedCount = AssetMutationLog::onlyTrashed()->count();

        return view('admin.logs.mutation', compact('logs', 'trashedCount'));
    }

    // ── Soft Delete ───────────────────────────────────────────

    public function destroyAssetLogs(Request $request): RedirectResponse
    {
        $this->authorize('log.delete');

        $valid = $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        DB::beginTransaction();
        try {
            $count = ActivityLog::where('model_type', 'App\Models\Asset')
                ->whereBetween('created_at', [$valid['date_from'] . ' 00:00:00', $valid['date_to'] . ' 23:59:59'])
                ->delete();

            DB::commit();

            return back()->with('success', "{$count} log aktivitas berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus log aktivitas.');
        }
    }

    public function destroyMutationLogs(Request $request): RedirectResponse
    {
        $this->authorize('log.delete');

        $valid = $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        DB::beginTransaction();
        try {
            $count = AssetMutationLog::whereBetween('mutation_date', [$valid['date_from'], $valid['date_to']])
                ->delete();

            DB::commit();

            return back()->with('success', "{$count} log mutasi berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus log mutasi.');
        }
    }

    // ── Restore ───────────────────────────────────────────────

    public function restoreAssetLogs(Request $request): RedirectResponse
    {
        $this->authorize('log.delete');

        $valid = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:activity_logs,id',
        ]);

        $count = ActivityLog::onlyTrashed()
            ->whereIn('id', $valid['ids'])
            ->restore();

        return back()->with('success', "{$count} log aktivitas berhasil dipulihkan.");
    }

    public function restoreMutationLogs(Request $request): RedirectResponse
    {
        $this->authorize('log.delete');

        $valid = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:asset_mutation_logs,id',
        ]);

        $count = AssetMutationLog::onlyTrashed()
            ->whereIn('id', $valid['ids'])
            ->restore();

        return back()->with('success', "{$count} log mutasi berhasil dipulihkan.");
    }

    // ── Trashed Views ─────────────────────────────────────────

    public function trashedAssetLogs(Request $request): View
    {
        $this->authorize('log.delete');

        $logs = ActivityLog::onlyTrashed()->with('user')
            ->where('model_type', 'App\Models\Asset')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where(function ($q) use ($term) {
                    $q->where('description', 'like', "%{$term}%")
                      ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.logs.trashed-asset', compact('logs'));
    }

    public function trashedMutationLogs(Request $request): View
    {
        $this->authorize('log.delete');

        $logs = AssetMutationLog::onlyTrashed()->with([
            'asset:id,asset_code,name',
            'performedBy:id,name',
        ])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->whereHas('asset', fn ($q) => $q
                    ->where('asset_code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                );
            })
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.logs.trashed-mutation', compact('logs'));
    }
}
