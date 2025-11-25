<?php include_once __DIR__ . '/../../components/admin_components/header-admin.php'; ?>
<?php include_once __DIR__ . '/../../config/config.php'; ?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="fw-bold h1">Document Template Editor <?= $typeId ?></h1>

        <div>
            <!-- Create Template Button -->
            <button class="btn btn-primary me-2" id="createTemplate" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                <i class="fa fa-plus"></i> Create Template
            </button>

            <!-- Edit Template Button -->
            <button id="openTemplatesBtn" class="btn btn-warning">
                <i class="fa fa-edit"></i>Manage Templates
            </button>
        </div>
    </div>

    <p class="text-secondary"><i>Press Ctrl + Alt + P to insert placeholders</i></p>

    <div id="placeholderDropdownContainer" class="mb-3" style="position:absolute; display:none; z-index:9999;">
        <select id="placeholderDropdown" class="form-select">
            <option value="">Select Placeholder</option>
        </select>
    </div>


    <h3 class="h3" id="documentName"></h3>
    <textarea id="template_editor"><?= $template['template_html'] ?? "" ?></textarea>

    <div class="mt-3">
        <button class="btn btn-success" id="saveBtn">Save Template</button>
        <a href="<?php echo BASE_PUBLIC . 'index.php?page=admin_document_requests'; ?>" class="btn btn-secondary">Back</a>
    </div>

    <!-- Create Template Modal -->
    <div class="modal fade" id="createTemplateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Document Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label">Search Document Type</label>
                    <input type="text" id="searchDocumentType" class="form-control mb-2" placeholder="Search by name or ID...">

                    <label class="form-label">Select Document Type</label>
                    <select id="documentTypeSelect" class="form-select" size="6">
                        <!-- Loaded via AJAX -->

                    </select>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" id="confirmCreateTemplate">Create</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Save Template Confirmation Modal -->
    <div class="modal fade" id="confirmSaveTemplateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Confirm Save</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p id="confirmSaveMessage">Are you sure you want to save this template?</p>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="confirmSaveTemplateBtn" class="btn btn-success">Yes, Save</button>
                </div>

            </div>
        </div>
    </div>
    <!-- ManageTemplate Modal -->
    <div class="modal fade" id="templatesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Select a Template</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="templatesListContainer">
                    <div id="templatesList" class="template-grid"></div>
                </div>

            </div>
        </div>
    </div>



</div>


<?php include_once __DIR__ . '/../../components/admin_components/footer-admin.php'; ?>