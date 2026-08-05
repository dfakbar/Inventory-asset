@extends('layouts.app')

@section('title', 'Dokumen SOP Aset')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dokumen SOP Aset</li>
@endsection

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-clipboard-check text-primary me-2"></i>Dokumen SOP Aset
        </h4>
        <p class="text-muted small mb-0">Arsip form registrasi, tanda terima, permohonan mutasi, dan berita acara mutasi aset.</p>
    </div>
    @can('document.create')
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Buat Dokumen
    </a>
    @endcan
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <form method="GET" action="{{ route('documents.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="type" class="form-select form-select-sm text-dark">
                    <option value="">— Semua Jenis —</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>
                            {{ $t->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari nomor/aset/penerima..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-light btn-sm"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-light btn-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nomor Dokumen</th>
                        <th>Jenis</th>
                        <th>Aset</th>
                        <th>Penerima</th>
                        <th>Tanggal</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr>
                            <td class="text-muted small">{{ $loop->iteration + ($documents->currentPage() - 1) * $documents->perPage() }}</td>
                            <td class="small font-monospace fw-semibold">{{ $doc->document_number }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi {{ $doc->document_type->icon() }} me-1"></i>{{ $doc->document_type->label() }}
                                </span>
                            </td>
                            <td class="small">
                                @if ($doc->asset)
                                    <a href="{{ route('assets.show', $doc->asset_id) }}" class="text-decoration-none">
                                        {{ $doc->asset->asset_code }}
                                    </a>
                                    <div class="text-muted small">{{ $doc->asset->name }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $doc->recipientEmployee?->name ?? '—' }}</td>
                            <td class="small text-nowrap">
                                {{ $doc->document_date ? $doc->document_date->format('d/m/Y') : '—' }}
                            </td>
                            <td class="small">{{ $doc->createdBy?->name ?? 'System' }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-primary" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('documents.pdf', $doc) }}" class="btn btn-sm btn-outline-success" title="Unduh PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @can('document.delete')
                                    <form action="{{ route('documents.destroy', $doc) }}" method="POST"
                                          onsubmit="return confirm('Hapus dokumen {{ $doc->document_number }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada dokumen SOP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-center py-3">
        {{ $documents->links() }}
    </div>
</div>
@endsection