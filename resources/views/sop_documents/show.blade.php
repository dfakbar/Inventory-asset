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
@endphp

@extends('layouts.app')

@section('title', $document->document_number . ' — ' . $document->document_type->label())

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('documents.index') }}" class="text-decoration-none text-muted">Dokumen SOP Aset</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $document->document_number }}</li>
@endsection

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi {{ $document->document_type->icon() }} text-primary me-2"></i>{{ $document->document_type->label() }}
        </h4>
        <p class="font-monospace text-muted mb-0 fs-6">{{ $document->document_number }}</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('documents.print', $document) }}" target="_blank" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i>Cetak / Print
        </a>
        <a href="{{ route('documents.pdf', $document) }}" class="btn btn-outline-primary">
            <i class="bi bi-download me-1"></i>Unduh PDF
        </a>
        @can('document.delete')
        <form action="{{ route('documents.destroy', $document) }}" method="POST"
              onsubmit="return confirm('Hapus dokumen {{ $document->document_number }}?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-trash me-1"></i>Hapus
            </button>
        </form>
        @endcan
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

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
@endsection