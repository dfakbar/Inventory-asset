@extends('layouts.app')

@section('title', 'Detail Aset — ' . $asset->asset_code)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('assets.index') }}" class="text-decoration-none text-muted">Manajemen Aset</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $asset->asset_code }}</li>
@endsection

@section('content')

@include('assets._show_content')

@push('scripts')
<script>
    (() => {
        const typeRadios = document.querySelectorAll('input[name="labelType"]');
        const previewQR = document.getElementById('labelPreviewQR');
        const previewBarcode = document.getElementById('labelPreviewBarcode');
        const downloadBtn = document.getElementById('downloadLabelBtn');
        const assetCode = '{{ $asset->asset_code }}';

        function updateLabel() {
            const type = document.querySelector('input[name="labelType"]:checked')?.value || 'qr';
            previewQR.style.display = type === 'qr' ? 'block' : 'none';
            previewBarcode.style.display = type === 'barcode' ? 'block' : 'none';
        downloadBtn.href = type === 'qr'
            ? '{{ route('assets.qr-code', $asset) }}'
            : '{{ route('assets.barcode', $asset) }}';
        downloadBtn.download = assetCode + '-' + (type === 'qr' ? 'qr.svg' : 'barcode.png');
        }

        typeRadios.forEach(r => r.addEventListener('change', updateLabel));
        updateLabel();

        document.querySelectorAll('#printDropdown .dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const count = this.dataset.count;
                const type = document.querySelector('input[name="labelType"]:checked')?.value || 'qr';
                const url = '{{ route('assets.print-code', $asset) }}?type=' + type + '&count=' + count + '&print=1';
                window.open(url, '_blank', 'width=800,height=600');
            });
        });
    })();
</script>
@endpush

@endsection
