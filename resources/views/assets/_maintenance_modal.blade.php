{{-- Modal Tambah Maintenance / Upgrade Aset --}}
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="maintenanceForm" action="{{ route('assets.maintenances.store', $asset) }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-semibold" id="maintenanceModalLabel">
                        <i class="bi bi-tools me-2"></i>Tambah Maintenance / Upgrade Komponen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="action_type" class="form-label fw-semibold small">Jenis Aksi <span class="text-danger">*</span></label>
                        <select class="form-select" id="action_type" name="action_type" required>
                            <option value="addition">Penambahan / Upgrade (Tambah/Tingkatkan Kapasitas)</option>
                            <option value="reduction">Pengurangan / Pencopotan (Kurangi/Cabut Komponen)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="component_name" class="form-label fw-semibold small">Nama Komponen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="component_name" name="component_name" placeholder="Misal: RAM, SSD, Baterai, Layar" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="previous_spec" class="form-label fw-semibold small">Spesifikasi Lama <span class="text-muted">(Opsional)</span></label>
                            <input type="text" class="form-control" id="previous_spec" name="previous_spec" placeholder="Misal: 8GB DDR4">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_spec" class="form-label fw-semibold small">Spesifikasi Baru <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="new_spec" name="new_spec" placeholder="Misal: 16GB DDR4" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="maintenance_date" class="form-label fw-semibold small">Tanggal Maintenance <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="maintenance_date" name="maintenance_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cost" class="form-label fw-semibold small">Biaya (Rp) <span class="text-muted">(Opsional)</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="cost" name="cost" placeholder="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold small">Catatan / Keterangan <span class="text-muted">(Opsional)</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Catatan teknis, vendor, nomor nota, dll."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white btn-sm">
                        <i class="bi bi-save me-1"></i>Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
