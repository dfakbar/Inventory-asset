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
    <div class="col-12 col-lg-8 col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-2 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2"></i>Informasi Vendor
                </h6>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.vendors.store') }}" method="POST" id="vendorCreateForm" novalidate>
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold small">
                                Nama Vendor <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: PT. Maju Jaya, CV. Teknologi Solution"
                                   required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="contact_person" class="form-label fw-semibold small">Kontak Person</label>
                            <input type="text"
                                   id="contact_person"
                                   name="contact_person"
                                   class="form-control {{ $errors->has('contact_person') ? 'is-invalid' : '' }}"
                                   value="{{ old('contact_person') }}"
                                   placeholder="Nama PIC">
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold small">Telepon</label>
                            <input type="text"
                                   id="phone"
                                   name="phone"
                                   class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                   value="{{ old('phone') }}"
                                   placeholder="Contoh: 021-1234567, 0812xxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold small">Email</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   value="{{ old('email') }}"
                                   placeholder="vendor@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label fw-semibold small">Alamat</label>
                            <textarea id="address"
                                      name="address"
                                      class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                      rows="2"
                                      placeholder="Alamat lengkap vendor...">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold small">Deskripsi</label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                      rows="2"
                                      placeholder="Catatan tambahan mengenai vendor...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy2 me-1"></i>Simpan Vendor
                        </button>
                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">
                            <i class="bi bi-x-lg me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
