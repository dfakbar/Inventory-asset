<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMutationLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function track(Request $request): View
    {
        $search = $request->input('search');
        $asset = null;
        $mutations = collect();

        if ($search) {
            $asset = Asset::with(['category', 'location', 'assignedUser', 'vendor', 'brand', 'employee'])
                ->where('asset_code', $search)
                ->orWhere('serial_number', $search)
                ->first();

            if ($asset) {
                $mutations = AssetMutationLog::with([
                    'performedBy:id,name',
                    'fromLocation:id,name',
                    'toLocation:id,name',
                    'fromAssignedUser:id,name',
                    'toAssignedUser:id,name',
                    'fromEmployee:id,name',
                    'toEmployee:id,name',
                ])
                    ->where('asset_id', $asset->id)
                    ->latest()
                    ->paginate(20)
                    ->withQueryString();
            }
        }

        return view('public.track', compact('asset', 'mutations', 'search'));
    }
}
