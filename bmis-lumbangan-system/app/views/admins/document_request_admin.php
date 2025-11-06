<?php include_once __DIR__ . '/../../components/admin_components/header-admin.php'  ?>


<div class="container py-5">
  <h2 class="mb-4">📋 All Document Requests</h2>

  <table id="requestsTable" class="table table-striped table-bordered">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Requester</th>
        <th>Document</th>
        <th>Requested For</th>
        <th>Relation</th>
        <th>Purpose</th>
        <th>Status</th>
        <th>Proof Upload</th>
        <th>Approval Date</th>
        <th>Remarks</th>
        <th>Request Date</th>
        <th>Action</th>
      </tr>
    </thead>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="statusForm">
        <div class="modal-header">
          <h5 class="modal-title" id="statusModalLabel">Update Request Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="request_id" name="request_id">
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
              <option value="">-- Select --</option>
              <option value="Approved">Approve</option>
              <option value="Rejected">Reject</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>


<?php include_once __DIR__ . '/../../components/footer.php'?>