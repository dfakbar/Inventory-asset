<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assets = Asset::with(['category:id,name', 'location:id,name', 'brand:id,name', 'vendor:id,name', 'assignedUser:id,name', 'employee:id,name'])
            ->search($request->input('search'))
            ->ofStatus($request->input('status'))
            ->ofCategory($request->integer('category_id') ?: null)
            ->orderBy('asset_code')
            ->paginate(50);

        return response()->json($assets);
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['category', 'location', 'assignedUser', 'brand', 'vendor', 'activeLoans']);

        return response()->json($asset);
    }
}
