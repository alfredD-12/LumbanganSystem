<?php include __DIR__ . '/../../components/header.php'; ?>



<main>
    <div class="container-fluid mt-4">
        <h1 class="h1 text-primary">Document Request</h1>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Requests</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-tab-pane" type="button" role="tab" aria-controls="approved-tab-pane" aria-selected="false">Approved</button>
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

                <h5 class="mt-2">Ongoing</h5>
                <div id="ongoingRequestsContainer" class="row mt-2">
                    <!-- Ongoing requests will be loaded here via AJAX -->
                    <p class="text-muted">Loading ongoing requests...</p>
                </div>




                <div class="modal modal-lg fade" id="newRequestModal" tabindex="-1" aria-labelledby="newRequestModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">    
                        <div class="modal-content overflow-auto">

                            <!--  FORM STARTS HERE -->
                            <form action="DocumentRequestController.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-header">
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
                                        <ul id="fileList" class="list-group mt-2"></ul>
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

    
                
            </div>
    
            <div class="tab-pane fade" id="approved-tab-pane" role="tabpanel" aria-labelledby="approved-tab">
                
                <h5 class="mt-2">Approved Requests</h5>
                <div id="approvedRequestsContainer" class="row mt-2">
                    <!-- Approved requests will be loaded here via AJAX -->
                    <p class="text-muted">Loading approved requests...</p>
                </div>

            </div>

            <div class="tab-pane fade" id="history-tab-pane" role="tabpanel" aria-labelledby="history-tab">
                
                <h5 class="mt-2">Request History</h5>
                <div id="requestHistoryContainer" class="row mt-2">
                    <!-- Request history will be loaded here via AJAX -->
                    <p class="text-muted">Loading request history...</p>
                </div>

            </div>
    
        </div>
    
    
    </div>
</main>


<?php include __DIR__ . '/../../components/footer.php'; ?>





                



