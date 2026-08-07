@php
    $data  = $document->data ?? [];
    $log   = $document->mutationLog;
    $asset = $document->asset ?? $log?->asset;

    $assetIds = $data['asset_ids'] ?? ($asset ? [$asset->id] : []);
    $assets = \App\Models\Asset::whereIn('id', $assetIds)
        ->with(['category', 'brand', 'location', 'vendor', 'assignedUser', 'employee'])
        ->get();

    $logIds = $data['mutation_log_ids'] ?? ($log ? [$log->id] : []);
    $logs = \App\Models\AssetMutationLog::with([
            'asset:id,asset_code,name,model,asset_category_id,brand_id',
            'asset.category:id,name',
            'asset.brand:id,name',
            'fromLocation:id,name',
            'toLocation:id,name',
            'fromAssignedUser:id,name',
            'toAssignedUser:id,name',
            'fromEmployee:id,name',
            'toEmployee:id,name',
            'performedBy:id,name',
        ])
        ->whereIn('id', $logIds)
        ->get();

    $peripheralIds = $data['peripheral_ids'] ?? [];
    $peripherals = \App\Models\Peripheral::with(['brand:id,name', 'location:id,name'])
        ->whereIn('id', $peripheralIds)
        ->get();

    $location = null;
    if (! empty($data['location_id'])) {
        $location = \App\Models\Location::find($data['location_id']);
    }
    if (! $location) {
        $location = $assets->first()?->location
            ?? $peripherals->first()?->location;
    }
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-secondary">
            <i class="bi bi-eye me-2"></i>Pratinjau Dokumen
        </h6>
        <span class="small text-muted">
            Dibuat oleh: {{ $document->createdBy?->name ?? 'System' }} • {{ $document->created_at->format('d/m/Y H:i') }}
        </span>
    </div>
    <div class="card-body">
        <div class="border rounded p-4 bg-white" style="max-width: 210mm; margin: 0 auto;">
            @include('sop_documents.pdf.' . $document->document_type->value)
        </div>
    </div>
</div>
