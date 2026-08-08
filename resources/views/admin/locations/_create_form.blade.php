@if ($errors->any())
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-12 col-lg-7 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-2 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-geo-alt me-2"></i>Detail Lokasi
                </h6>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.locations.store') }}" method="POST" id="locationCreateForm" novalidate>
                    @csrf

                    {{-- Nama Lokasi --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small">
                            Nama Lokasi <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}"
                               placeholder="Contoh: Ruang Server, Gudang Aset"
                               required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Departemen --}}
                    <div class="mb-3">
                        <label for="department" class="form-label fw-semibold small">Departemen</label>
                        <input type="text"
                               id="department"
                               name="department"
                               class="form-control {{ $errors->has('department') ? 'is-invalid' : '' }}"
                               value="{{ old('department') }}"
                               placeholder="Contoh: IT, Keuangan, HRD, Operasional">
                        <div class="form-text text-muted small">
                            <i class="bi bi-diagram-3 me-1"></i>Departemen atau divisi yang bertanggung jawab.
                        </div>
                        @error('department')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold small">Deskripsi</label>
                        <textarea id="description"
                                  name="description"
                                  class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                  rows="3"
                                  placeholder="Deskripsi singkat mengenai lokasi ini...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy2 me-1"></i>Simpan Lokasi
                        </button>
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">
                            <i class="bi bi-x-lg me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
