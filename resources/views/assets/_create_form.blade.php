{{--
    Partial: assets/_create_form.blade.php
    Form tambah aset untuk modal (di-load via AJAX dari assets.create).
    Tombol submit berada di modal-footer dan menautkan ke form ini via atribut form="createAssetForm".
--}}
<form action="{{ route('assets.store') }}"
      method="POST"
      enctype="multipart/form-data"
      id="createAssetForm"
      novalidate>
    @csrf

    @include('assets._form', ['asset' => null])
</form>
