<?php include_once __DIR__ . '/../../components/admin_components/header-admin.php'  ?>


<div class="container py-4">

  <!-- 🔹 Summary Cards -->
  <div class="container py-2">
    <h2 class="mb-4 fw-bold">📋Document Requests</h2>

    <div class="row mb-4" id="statusSummary">
      <div class="col-md-3">
        <div class="card floating-card text-center">
          <div class="card-body">
            <h5 class="card-title text-primary">Pending</h5>
            <p class="card-text fs-4 fw-bold" id="pendingCount">0</p>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card floating-card text-center">
          <div class="card-body">
            <h5 class="card-title text-success">Approved</h5>
            <p class="card-text fs-4 fw-bold" id="approvedCount">0</p>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card floating-card text-center">
          <div class="card-body">
            <h5 class="card-title text-danger">Rejected</h5>
            <p class="card-text fs-4 fw-bold" id="rejectedCount">0</p>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card floating-card text-center">
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

    <div class="d-flex align-self-end">
      <button class="btn-elegant"
        data-bs-toggle="modal"
        data-bs-target="#documentTypeModal">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
        </svg>
        Document Type
      </button>

      <button class="btn-elegant" id="openNewRequestModal">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
        </svg>
        New Request
      </button>

    </div>
  </div>

  <!-- 🔹 DataTable -->
  <div class="table-container">
    <table id="requestsTable" class="table-striped table-bordered align-middle">
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
          <th>Request File</th>
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

<!-- PDF Preview Modal -->
<div class="modal fade" id="requestCopyModal" tabindex="-1" aria-labelledby="requestCopyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Request Copy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" style="height: 80vh;">
        <iframe id="requestCopyFrame" src="" style="width: 100%; height: 100%;" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Document Type Modal -->
<div class="modal fade" id="documentTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header type-modal-header text-white">
        <h5 class="modal-title">Manage Document Types</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0" id="documentTypeModalBody">

        <!-- Filters -->
        <div class="filters p-3">
          <select id="categoryFilter" class="form-select">
            <option value="">All Categories</option>
          </select>
        </div>

        <!-- Document types container -->
        <div class="row g-3 p-3" id="docTypeContainer">
          <div class="p-5 text-center text-muted">Loading...</div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- Edit Document Type Modal -->
<div class="modal fade" id="editDocumentTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered overflow-auto shadow-lg modal-border">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><strong>Edit Document Type</strong></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editDocumentTypeForm">
        <div class="modal-body">
          <input type="hidden" id="editDocumentId" name="document_type_id">
          <div class="mb-3">
            <label class="form-label">Document Name</label>
            <input type="text" class="form-control" id="editDocumentName" name="document_name">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="editDescription" name="description"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Requirements</label>
            <textarea class="form-control" id="editRequirements" name="requirements"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Fee</label>
            <input type="number" class="form-control" id="editFee" name="fee" step="0.01">
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" id="editCategory" name="category_id"></select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade " id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header type-modal-header text-white">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this document?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Document Type Modal -->
<div class="modal fade modal-border" id="addDocumentModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered shadow-lg">
    <div class="modal-content modern-modal">

      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Add New Document Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <form id="addDocumentForm" class="row g-3">

          <div class="col-md-6">
            <label class="form-label">Category</label>
            <select id="addCategory" name="category_id" class="form-select modern-input" required></select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Document Name</label>
            <input type="text" id="addDocumentName" name="document_name" class="form-control modern-input" required>
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea id="addDescription" name="description" class="form-control modern-input" rows="2"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Requirements</label>
            <textarea id="addRequirements" name="requirements" class="form-control modern-input" rows="2"></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Fee</label>
            <input type="number" step="0.01" id="addFee" name="fee" class="form-control modern-input" required>
          </div>

        </form>

      </div>

      <div class="modal-footer border-0 d-flex gap-2">
        <button class="btn btn-light modern-btn-cancel" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary modern-btn-save" id="saveDocumentBtn">Save</button>
      </div>

    </div>
  </div>
</div>

<!-- New Document Request Modal -->
<div class="modal fade" id="newRequestModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header type-modal-header text-white">
        <h5 class="modal-title">Create Document Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="newRequestForm">
        <div class="modal-body">

          <!-- Resident Name -->
          <div class="mb-3">
            <label class="form-label">Resident Name</label>
            <input type="text" class="form-control" name="requested_for" placeholder="Enter full name" required>
          </div>

          <!-- Document Type -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Document Type</label>
            <select id="requestDocType" name="document_type_id" class="form-select"></select>
          </div>

          <!-- Purpose -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Purpose</label>
            <textarea name="purpose" class="form-control" rows="2"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn type-modal-header text-white">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>








<?php include_once __DIR__ . '/../../components/admin_components/footer-admin.php' ?>