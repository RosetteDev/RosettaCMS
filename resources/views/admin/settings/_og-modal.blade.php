    <div class="modal fade" id="ogImagePickerModal" tabindex="-1" aria-labelledby="ogImagePickerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ogImagePickerLabel">Choose an OG image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="search" id="og-image-picker-search" class="form-control mb-3" placeholder="Search images by name or alt text">
                    <div id="og-image-picker-grid" class="row g-3">
                        <div class="col-12 text-muted small">Loading images…</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ url('/admin/media') }}" class="btn btn-link me-auto">Manage media</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
