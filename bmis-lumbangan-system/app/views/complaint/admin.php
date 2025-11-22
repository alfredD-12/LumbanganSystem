<?php 
// Set page variables for header
$pageTitle = 'Peace & Order Management';
$pageSubtitle = 'Manage and monitor barangay complaints and incidents';
$currentPage = 'admin_complaints';

include __DIR__ . '/../../components/admin_components/header-admin.php'; 
?>
<?php
/**
 * Complaint Index View (Admin)
 * Display list of all complaints with filters and statistics
 * Variables available: $complaints, $statistics, $statuses, $caseTypes
 */
$baseUrl = '/Lumbangan_BMIS/bmis-lumbangan-system/public';
// If the view is accessed directly (no controller), try to load data here
if (!isset($complaints) || !isset($statistics) || !isset($statuses) || !isset($caseTypes)) {
    $modelPath = __DIR__ . '/../../models/Complaint.php';
    if (file_exists($modelPath)) {
        require_once $modelPath;
        try {
            $complaintModel = new Complaint();
            $filters = [
                'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
                'status_id' => isset($_GET['status_id']) ? trim($_GET['status_id']) : '',
                'case_type_id' => isset($_GET['case_type_id']) ? trim($_GET['case_type_id']) : ''
            ];

            $complaints = $complaintModel->getAll($filters);
            $statistics = $complaintModel->getStatistics();
            $statuses = $complaintModel->getStatuses();
            $caseTypes = $complaintModel->getCaseTypes();
        } catch (Exception $e) {
            // Fallback to empty values to avoid warnings
            $complaints = $complaints ?? [];
            $statistics = $statistics ?? ['total'=>0,'pending'=>0,'investigating'=>0,'resolved'=>0];
            $statuses = $statuses ?? [];
            $caseTypes = $caseTypes ?? [];
        }
    } else {
        // Ensure variables exist to avoid warnings
        $complaints = $complaints ?? [];
        $statistics = $statistics ?? ['total'=>0,'pending'=>0,'investigating'=>0,'resolved'=>0];
        $statuses = $statuses ?? [];
        $caseTypes = $caseTypes ?? [];
    }
}
?>

<!-- Hidden data for JavaScript -->
<script type="application/json" id="statusesData"><?php echo json_encode($statuses); ?></script>

<!-- Main Content Area -->
<main class="main-content">
    <div class="container-xxl px-4 py-4">
        <!-- Add New Complaint Button -->
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newIncidentModal">
                <i class="fas fa-plus"></i>
                Add New Complaint
            </button>
        </div>

        <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card gradient-card-blue h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Total Complaints</p>
                                    <h2 class="mb-0"><?php echo $statistics['total'] ?? 0; ?></h2>
                                </div>
                                <i class="fas fa-file-alt icon-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card gradient-card-yellow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Pending</p>
                                    <h2 class="mb-0"><?php echo $statistics['pending'] ?? 0; ?></h2>
                                </div>
                                <i class="fas fa-clock icon-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card gradient-card-purple h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Investigating</p>
                                    <h2 class="mb-0"><?php echo $statistics['investigating'] ?? 0; ?></h2>
                                </div>
                                <i class="fas fa-search icon-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card gradient-card-green h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Resolved</p>
                                    <h2 class="mb-0"><?php echo $statistics['resolved'] ?? 0; ?></h2>
                                </div>
                                <i class="fas fa-check-circle icon-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" name="search" placeholder="Search complaints..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" id="searchInput">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status_id" id="statusFilter">
                                    <option value="">All Status</option>
                                    <?php 
                                    // Remove duplicates by tracking seen labels
                                    $seenStatuses = [];
                                    foreach ($statuses as $status): 
                                        $label = $status['label'];
                                        $labelLower = strtolower($label);
                                        if (!in_array($label, $seenStatuses)):
                                            $seenStatuses[] = $label;
                                    ?>
                                        <option value="<?php echo $labelLower; ?>" <?php echo (isset($_GET['status']) && strtolower($_GET['status']) == $labelLower) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Case Type</label>
                                <select class="form-select" name="case_type_id" id="caseTypeFilter">
                                    <option value="">All Cases</option>
                                    <?php 
                                    // Remove duplicates by tracking seen labels
                                    $seenCaseTypes = [];
                                    foreach ($caseTypes as $caseType): 
                                        $label = $caseType['label'];
                                        $labelLower = strtolower($label);
                                        if (!in_array($label, $seenCaseTypes)):
                                            $seenCaseTypes[] = $label;
                                    ?>
                                        <option value="<?php echo $labelLower; ?>" <?php echo (isset($_GET['case_type']) && strtolower($_GET['case_type']) == $labelLower) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" id="applyFilter">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Results Info -->
            <?php if (!empty($_GET['search']) || !empty($_GET['status_id']) || !empty($_GET['case_type_id'])): ?>
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="fas fa-filter me-2"></i>
                        <strong>Filtered Results:</strong> 
                        Showing <?php echo count($complaints); ?> 
                        <?php echo count($complaints) === 1 ? 'complaint' : 'complaints'; ?>
                        <?php if (!empty($_GET['search'])): ?>
                            matching "<strong><?php echo htmlspecialchars($_GET['search']); ?></strong>"
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/index.php?page=complaints" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </a>
                </div>
            <?php endif; ?>

            <!-- Complaints List -->
            <div class="complaints-list">
                <?php if (!empty($complaints)): ?>
                    <?php foreach ($complaints as $complaint): ?>
                        <div class="card incident-card mb-3"
                             data-search="<?php echo strtolower(htmlspecialchars($complaint['incident_title'] . ' ' . $complaint['complainant_name'] . ' ' . $complaint['offender_name'] . ' ' . $complaint['location'] . ' ' . $complaint['narrative'])); ?>"
                             data-status="<?php echo strtolower(htmlspecialchars($complaint['status_label'])); ?>"
                             data-case="<?php echo strtolower(htmlspecialchars($complaint['case_type'])); ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($complaint['incident_title'] ?? 'Untitled Complaint'); ?></h5>
                                    <div>
                                        <span class="status-badge status-<?php echo strtolower($complaint['status_label']); ?> me-2">
                                            <?php echo htmlspecialchars($complaint['status_label']); ?>
                                        </span>
                                        <span class="status-badge case-<?php echo strtolower($complaint['case_type']); ?>">
                                            <?php echo htmlspecialchars($complaint['case_type']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <strong class="text-muted">Complainant:</strong> 
                                        <?php echo htmlspecialchars($complaint['complainant_name'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong class="text-muted">Offender:</strong> 
                                        <?php echo htmlspecialchars($complaint['offender_name'] ?? 'Unknown'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong class="text-muted">Date:</strong> 
                                        <?php echo date('M d, Y', strtotime($complaint['date_of_incident'])); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong class="text-muted">Location:</strong> 
                                        <?php echo htmlspecialchars($complaint['location'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong class="text-muted">Blotter Type:</strong> 
                                        <?php echo htmlspecialchars($complaint['blotter_type'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong class="text-muted">Offender Type:</strong> 
                                        <?php echo htmlspecialchars($complaint['offender_type'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted">Description:</strong>
                                        <p class="mb-0"><?php echo htmlspecialchars(substr($complaint['narrative'] ?? '', 0, 150)) . '...'; ?></p>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-primary btn-sm view-details-btn" data-id="<?php echo $complaint['id']; ?>">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                            <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $complaint['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $complaint['id']; ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <!-- No results message (hidden by default, shown by JS when filtering) -->
                    <div class="alert alert-warning" id="noResultsMessage" style="display: none;">
                        <i class="fas fa-search"></i> No complaints match your search criteria. Try different keywords.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No complaints found. Try adjusting your filters or add a new complaint.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add New Complaint Modal -->
    <div class="modal fade" id="newIncidentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="complaintForm" autocomplete="off">
                        <input type="hidden" name="id" id="complaintId">
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="mb-3">New Complaint Reported</h6>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Complaint Title <span class="text-danger">*</span></label>
                                <input type="text" name="incident_title" class="form-control" required autocomplete="off" placeholder="Enter complaint title">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Blotter Type <span class="text-danger">*</span></label>
                                <select name="blotter_type" class="form-select" required>
                                    <option value="">-- Select Blotter Type --</option>
                                    <option value="Complaint">Complaint</option>
                                    <option value="Incident">Incident</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Complainant Type <span class="text-danger">*</span></label>
                                <select name="complainant_type" class="form-select" required>
                                    <option value="">-- Select Complainant Type --</option>
                                    <option value="Resident">Resident</option>
                                    <option value="Non-Resident">Non-Resident</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <h6 class="mb-3">Complainant Information</h6>
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" name="complainant_name" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Contact Number</label>
                                                <input type="tel" name="complainant_contact" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                <select name="complainant_gender" class="form-select" required>
                                                    <option value="">Select Gender</option>
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Birthday</label>
                                                <input type="date" name="complainant_birthday" class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Address</label>
                                                <textarea name="complainant_address" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mb-3">Offender Information</h6>
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Offender Type</label>
                                                <select name="offender_type" class="form-select">
                                                    <option value="">-- Select --</option>
                                                    <option value="resident">Resident</option>
                                                    <option value="non-resident">Non-Resident</option>
                                                    <option value="unknown">Unknown</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Gender</label>
                                                <select name="offender_gender" class="form-select">
                                                    <option value="">-- Select --</option>
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="offender_name" class="form-control" placeholder="Enter offender's name if known">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Address</label>
                                                <textarea name="offender_address" class="form-control" rows="2" placeholder="Enter offender's address if known"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Description</label>
                                                <textarea name="offender_description" class="form-control" rows="3" placeholder="Enter physical description or any identifying details"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mb-3">Incident Details</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Case Type <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="case_type_id" id="criminalCase" value="1" required>
                                                        <label class="form-check-label" for="criminalCase">Criminal</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="case_type_id" id="civilCase" value="2">
                                                        <label class="form-check-label" for="civilCase">Civil</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="case_type_id" id="othersCase" value="3">
                                                        <label class="form-check-label" for="othersCase">Others</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Date of Incident <span class="text-danger">*</span></label>
                                                <input type="date" name="date_of_incident" class="form-control" required max="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Time of Incident <span class="text-danger">*</span></label>
                                                <input type="time" name="time_of_incident" class="form-control" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                                <input type="text" name="location" class="form-control" placeholder="Enter incident location" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Narrative <span class="text-danger">*</span></label>
                                                <textarea name="narrative" class="form-control" rows="4" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="complaintForm" class="btn btn-primary" id="submitBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
    </div>
</main>

<?php include __DIR__ . '/../../components/admin_components/footer-admin.php'; ?>
