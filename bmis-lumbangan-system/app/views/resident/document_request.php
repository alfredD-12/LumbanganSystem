<?php
include __DIR__ . '/../../components/resident_components/header-resident.php';
?>




<main>
    <div class="container-fluid mt-4">
        <h1 class="h1 header-color fw-semibold pb-2">Document Request</h1>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Requests</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-tab-pane" type="button" role="tab" aria-controls="approved-tab-pane" aria-selected="false">Approved</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="released-tab" data-bs-toggle="tab" data-bs-target="#released-tab-pane" type="button" role="tab" aria-controls="released-tab-pane" aria-selected="false">Released</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-tab-pane" type="button" role="tab" aria-controls="rejected-tab-pane" aria-selected="false">Rejected</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-tab-pane" type="button" role="tab" aria-controls="history-tab-pane" aria-selected="false">Request History</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab">
                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#newRequestModal">
                    <i class="fa fa-plus"></i> New Request
                </button>

                <h5 class="mt-4 mb-5 fw-semibold">Ongoing</h5>
                <div id="ongoingRequestsContainer" class="row mt-2">
                    <!-- Ongoing requests will be loaded here via AJAX -->
                    <p class="text-muted">Loading ongoing requests...</p>
                </div>










            </div>

            <div class="tab-pane fade" id="approved-tab-pane" role="tabpanel" aria-labelledby="approved-tab">

                <h5 class="mt-2 mb-5 fw-semibold">Approved Requests</h5>
                <div id="approvedRequestsContainer" class="row mt-2">
                    <!-- Approved requests will be loaded here via AJAX -->
                    <p class="text-muted">Loading approved requests...</p>
                </div>

            </div>

            <div class="tab-pane fade" id="released-tab-pane" role="tabpanel" aria-labelledby="released-tab">

                <h5 class="mt-2 mb-5 fw-semibold">Released Requests</h5>
                <div id="releasedRequestsContainer" class="row mt-2">
                    <!-- Released requests will be loaded here via AJAX -->
                    <p class="text-muted">Loading released requests...</p>
                </div>

            </div>

            <div class="tab-pane fade" id="rejected-tab-pane" role="tabpanel" aria-labelledby="rejected-tab">

                <h5 class="mt-2 mb-5 fw-semibold">Rejected Requests</h5>
                <div id="rejectedRequestsContainer" class="row mt-2">
                    <!-- Rejected requests will be loaded here via AJAX -->
                    <p class="text-muted">Loading rejected requests...</p>
                </div>

            </div>

            <div class="tab-pane fade" id="history-tab-pane" role="tabpanel" aria-labelledby="history-tab">

                <h5 class="mt-2 mb-5 fw-semibold">Request History</h5>
                <div class="mb-3 d-flex gap-2 align-items-center">
                    <label for="historyFilterStatus" class="mb-0">Status</label>
                    <select id="historyFilterStatus" class="form-select form-select-md" style="width:180px">
                        <option value="">All</option>
                        <option value="Approved">Approved</option>
                        <option value="Released">Released</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                    <input id="historyFilterKeyword" class="form-control form-control-md" style="width:260px" placeholder="Search document or purpose">
                    <button id="historyApplyFilter" type="button" class="btn btn-md btn-primary">Filter</button>
                    <button id="historyClearFilter" type="button" class="btn btn-md btn-secondary">Clear</button>
                </div>

                <div id="requestHistoryContainer" class="row mt-2">
                    <!-- Request history will be loaded here via AJAX -->
                    <p class="text-muted">Loading request history...</p>
                </div>

            </div>

        </div>

        <!-- Modals moved out of hidden tab panes so Bootstrap can show them reliably -->
        <div>
            <div class="modal modal-lg fade" id="newRequestModal" tabindex="-1" aria-labelledby="newRequestModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content overflow-auto">

                        <!--  FORM STARTS HERE -->
                        <form action="DocumentRequestController.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-header modal-header-color">
                                <h5 class="modal-title" id="newRequestModalLabel">New Document Request</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <h3 class="h3">Name: Name of the user requesting based on user_id</h3>
                                <!--  your form fields are all here -->
                                <div class="mb-3">
                                    <label for="document_type_id" class="form-label">Document Type</label>
                                    <select class="form-select" id="document_type_id" name="document_type_id" required>
                                        <option value="">-- Select Document Type --</option>
                                        <?php if (!empty($documentTypes)): ?>
                                            <?php foreach ($documentTypes as $row): ?>
                                                <option value="<?= htmlspecialchars($row['document_type_id']) ?>">
                                                    <?= htmlspecialchars($row['document_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option disabled>No document types found</option>
                                        <?php endif; ?>
                                    </select>
                                    <p class="mt-1">Requirements:</p>
                                    <ul class="mt-1" id="requirementList"></ul>

                                </div>


                                <div class="mb-3">
                                    <label for="purpose" class="form-label">Purpose</label>
                                    <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Enter purpose of request..." required></textarea>
                                </div>

                                <!--  File Upload (NEW) -->
                                <div class="mb-3">
                                    <label for="proof_upload" class="form-label">Upload Proof Documents</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        id="proof_upload"
                                        name="proof_upload[]"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        multiple>
                                    <small class="text-muted">You may upload multiple files (images or PDFs) as proof.</small>
                                    <div class="d-flex gap-2 mt-2 align-items-start">
                                        <ul id="fileList" class="list-group flex-grow-1"></ul>
                                        <div class="camera-actions ms-2">
                                            <button type="button" id="openCameraBtn" class="btn btn-outline-secondary btn-md mb-2">Take Photo</button>
                                            <div id="cameraContainer" class="d-none border rounded p-2 bg-light">
                                                <video id="cameraPreview" autoplay playsinline style="width:200px; max-width:100%; border-radius:4px; background:#000"></video>
                                                <div class="mt-2 d-flex gap-2">
                                                    <button type="button" id="capturePhotoBtn" class="btn btn-sm btn-primary">Capture</button>
                                                    <button type="button" id="closeCameraBtn" class="btn btn-sm btn-secondary">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="forSomeone" name="forSomeone">
                                    <label class="form-check-label" for="forSomeone">
                                        Requesting for someone else
                                    </label>
                                </div>

                                <div id="someoneFields" class="d-none">
                                    <div class="mb-3">
                                        <label for="requested_for" class="form-label">Requested For</label>
                                        <input type="text" class="form-control" id="requested_for" name="requested_for" placeholder="Enter the person's full name">
                                    </div>

                                    <div class="mb-3">
                                        <label for="relation_to_requestee" class="form-label">Relation to Requestee</label>
                                        <input type="text" class="form-control" id="relation_to_requestee" name="relation_to_requestee" placeholder="e.g., Mother, Father, Friend">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
                            </div>
                        </form>
                        <!--  FORM ENDS HERE -->

                    </div>
                </div>
            </div>

            <!-- View Request Modal -->
            <div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header modal-header-color">
                            <h5 class="modal-title" id="viewRequestModalLabel">Request Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="viewRequestBody">
                            <!-- Filled by JS -->
                            <p class="text-muted">Loading...</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Request Modal -->
            <div class="modal fade" id="editRequestModal" tabindex="-1" aria-labelledby="editRequestModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content overflow-auto">
                        <form id="editRequestForm" enctype="multipart/form-data">
                            <div class="modal-header modal-header-color">
                                <h5 class="modal-title" id="editRequestModalLabel">Edit Document Request</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="edit_request_id" name="request_id">

                                <div class="mb-3">
                                    <label for="edit_document_type_id" class="form-label">Document Type</label>
                                    <select class="form-select" id="edit_document_type_id" name="document_type_id" required>
                                        <option value="">-- Select Document Type --</option>
                                        <?php if (!empty($documentTypes)): ?>
                                            <?php foreach ($documentTypes as $dt): ?>
                                                <option value="<?php echo $dt['document_type_id']; ?>"><?php echo htmlspecialchars($dt['document_name']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <p class="mt-1">Requirements:</p>
                                    <ul class="mt-1" id="editRequirementList"></ul>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_purpose" class="form-label">Purpose</label>
                                    <textarea class="form-control" id="edit_purpose" name="purpose" rows="3" required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_proof_upload" class="form-label">Upload Proof Documents (add more)</label>
                                    <input type="file" class="form-control" id="edit_proof_upload" name="proof_upload[]" accept=".jpg,.jpeg,.png,.pdf" multiple>
                                    <small class="text-muted">Existing files are listed below. You may add additional files to append.</small>
                                    <div class="d-flex gap-2 mt-2 align-items-start">
                                        <ul id="editFileList" class="list-group flex-grow-1"></ul>
                                        <div class="camera-actions ms-2">
                                            <button type="button" id="openEditCameraBtn" class="btn btn-outline-secondary btn-sm mb-2">Take Photo</button>
                                            <div id="editCameraContainer" class="d-none border rounded p-2 bg-light">
                                                <video id="editCameraPreview" autoplay playsinline style="width:200px; max-width:100%; border-radius:4px; background:#000"></video>
                                                <div class="mt-2 d-flex gap-2">
                                                    <button type="button" id="captureEditPhotoBtn" class="btn btn-sm btn-primary">Capture</button>
                                                    <button type="button" id="closeEditCameraBtn" class="btn btn-sm btn-secondary">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="edit_forSomeone" name="forSomeone">
                                    <label class="form-check-label" for="edit_forSomeone">Requesting for someone else</label>
                                </div>

                                <div id="editSomeoneFields" class="d-none">
                                    <div class="mb-3">
                                        <label for="edit_requested_for" class="form-label">Requested For</label>
                                        <input type="text" class="form-control" id="edit_requested_for" name="requested_for">
                                    </div>

                                    <div class="mb-3">
                                        <label for="edit_relation_to_requestee" class="form-label">Relation to Requestee</label>
                                        <input type="text" class="form-control" id="edit_relation_to_requestee" name="relation_to_requestee">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>


<?php include_once __DIR__ . '/../../components/resident_components/footer-resident.php' ?>