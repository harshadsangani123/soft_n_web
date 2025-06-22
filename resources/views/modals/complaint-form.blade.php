<!-- New Complaint Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit New Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="complaint-form">
                    <div class="mb-3">
                        <label for="complaint-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="complaint-title" required>
                    </div>
                    <div class="mb-3">
                        <label for="complaint-description" class="form-label">Description</label>
                        <textarea class="form-control" id="complaint-description" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitComplaint()">Submit Complaint</button>
            </div>
        </div>
    </div>
</div>

