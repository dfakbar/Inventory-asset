<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AssetMutationLog;
use Illuminate\Http\Request;
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

        return view('admin.logs.asset', compact('logs'));
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

        return view('admin.logs.mutation', compact('logs'));
    }
}
