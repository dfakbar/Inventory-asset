<div class="modal fade" id="columnSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-layout-three-columns me-2 text-primary"></i>Pengaturan Kolom
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Seret untuk mengurutkan, centang untuk menampilkan kolom.</p>
                <form id="columnSettingsForm">
                    @csrf
                    <ul id="columnSortable" class="list-group">
                        @php
                            $allColumns = \App\Http\Controllers\AssetController::DEFAULT_COLUMNS;
                            $savedColumns = $columns;
                        @endphp
                        @foreach ($allColumns as $col)
                            <li class="list-group-item d-flex align-items-center gap-2 py-2" data-column="{{ $col }}">
                                <i class="bi bi-grip-vertical text-muted cursor-move" style="cursor:grab"></i>
                                <input type="checkbox" class="form-check-input column-checkbox"
                                       value="{{ $col }}"
                                       {{ in_array($col, $savedColumns) ? 'checked' : '' }}>
                                <span class="small">{{ \App\Http\Controllers\AssetController::COLUMN_LABELS[$col] ?? ucfirst($col) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="resetColumnsBtn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Default
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="saveColumnsBtn">
                    <i class="bi bi-check-lg me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>
