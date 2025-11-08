<?php include_once __DIR__ . '/../../components/admin_components/header-admin.php'  ?>


<div class="container py-4">

  <!-- 🔹 Summary Cards -->
<div class="container py-5">
  <h2 class="mb-4">📋 All Document Requests</h2>

  <div class="row mb-4" id="statusSummary">
    <div class="col-md-3">
      <div class="card text-center shadow-sm border-primary">
        <div class="card-body">
          <h5 class="card-title text-primary">Pending</h5>
          <p class="card-text fs-4 fw-bold" id="pendingCount">0</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-center shadow-sm border-success">
        <div class="card-body">
          <h5 class="card-title text-success">Approved</h5>
          <p class="card-text fs-4 fw-bold" id="approvedCount">0</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-center shadow-sm border-danger">
        <div class="card-body">
          <h5 class="card-title text-danger">Rejected</h5>
          <p class="card-text fs-4 fw-bold" id="rejectedCount">0</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-center shadow-sm border-warning">
        <div class="card-body">
          <h5 class="card-title text-warning">Released</h5>
          <p class="card-text fs-4 fw-bold" id="releasedCount">0</p>
        </div>
      </div>
    </div>
  </div>
</div>


  <!-- 🔹 Header + Filter -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0">📋 Document Requests</h2>
    <div class="d-flex align-items-center gap-2">
      <label for="statusFilter" class="form-label mb-0 me-2">Filter by Status:</label>
      <select id="statusFilter" class="form-select form-select-sm w-auto">
        <option value="">All</option>
        <option value="Pending">Pending</option>
        <option value="Approved">Approved</option>
        <option value="Rejected">Rejected</option>
        <option value="Released">Released</option>
      </select>
    </div>
  </div>

  <!-- 🔹 DataTable -->
  <div class="table-container">
    <table id="requestsTable" class="table table-striped table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Requester</th>
          <th>Document</th>
          <th>Request Date</th>
          <th>Requested For</th>
          <th>Purpose</th>
          <th>Relation</th>
          <th>Requirements</th>
          <th>Proof Upload</th>
          <th>Approval Date</th>
          <th>Released Date</th>
          <th>Remarks</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- 🔹 Existing Status Modal -->
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
              <option value="Pending">Pending</option>
              <option value="Released">Released</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="submitUpdate" class="btn btn-primary">Save changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmUpdateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Confirm Update</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="confirmMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmUpdateBtn" class="btn btn-primary">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

<!-- 🔹 Proof Image Modal -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Proof Upload</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="proofImage" src="" alt="Proof Image" class="img-fluid rounded shadow-sm">
      </div>
    </div>
  </div>
</div>


<?php include_once __DIR__ . '/../../components/footer.php'?>