<?php include __DIR__ . '/../../components/resident_components/header-resident.php'; ?>
    <!-- Residents Complaint Page CSS -->
    <link rel="stylesheet" href="<?php echo rtrim(BASE_URL, '/'); ?>/assets/css/residents/residents.css?v=<?php echo time(); ?>">
<?php
/**
 * Resident Dashboard View
 * Public-facing complaint browsing interface
 * Variables available: $complaints, $statistics, $statuses, $caseTypes
 */
$baseUrl = '/Lumbangan_BMIS/public';
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
            $complaints = $complaints ?? [];
            $statistics = $statistics ?? ['total'=>0,'pending'=>0,'investigating'=>0,'resolved'=>0];
            $statuses = $statuses ?? [];
            $caseTypes = $caseTypes ?? [];
        }
    } else {
        $complaints = $complaints ?? [];
        $statistics = $statistics ?? ['total'=>0,'pending'=>0,'investigating'=>0,'resolved'=>0];
        $statuses = $statuses ?? [];
        $caseTypes = $caseTypes ?? [];
    }
}
?>
    <div class="min-vh-100 bg-light">
        <!-- Page content (header is provided by header-resident.php) -->

        <div class="container-xxl px-4 py-4">
            <!-- Statistics Summary -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card gradient-card-blue h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Total</p>
                                    <h3 class="mb-0"><?php echo $statistics['total'] ?? 0; ?></h3>
                                </div>
                                <i class="fas fa-file-alt icon-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card gradient-card-yellow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Pending</p>
                                    <h3 class="mb-0"><?php echo $statistics['pending'] ?? 0; ?></h3>
                                </div>
                                <i class="fas fa-clock icon-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card gradient-card-purple h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Investigating</p>
                                    <h3 class="mb-0"><?php echo $statistics['investigating'] ?? 0; ?></h3>
                                </div>
                                <i class="fas fa-search icon-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card gradient-card-green h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Resolved</p>
                                    <h3 class="mb-0"><?php echo $statistics['resolved'] ?? 0; ?></h3>
                                </div>
                                <i class="fas fa-check-circle icon-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search complaints..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <?php 
                                // Remove duplicates by tracking seen labels
                                $seenStatuses = [];
                                foreach ($statuses as $status): 
                                    $label = $status['label'];
                                    if (!in_array($label, $seenStatuses)):
                                        $seenStatuses[] = $label;
                                ?>
                                    <option value="<?php echo strtolower(htmlspecialchars($label)); ?>">
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Case Type</label>
                            <select class="form-select" id="caseFilter">
                                <option value="">All Cases</option>
                                <?php 
                                // Remove duplicates by tracking seen labels
                                $seenCaseTypes = [];
                                foreach ($caseTypes as $type): 
                                    $label = $type['label'];
                                    if (!in_array($label, $seenCaseTypes)):
                                        $seenCaseTypes[] = $label;
                                ?>
                                    <option value="<?php echo strtolower(htmlspecialchars($label)); ?>">
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complaints List -->
            <div class="complaints-list">
                <?php if (!empty($complaints)): ?>
                    <?php foreach ($complaints as $complaint): ?>
                        <div class="card incident-card mb-3" 
                             data-search="<?php echo strtolower(htmlspecialchars($complaint['incident_title'] . ' ' . $complaint['complainant_name'] . ' ' . $complaint['location'] . ' ' . ($complaint['offender_name'] ?? '') . ' ' . $complaint['blotter_type'])); ?>"
                             data-status="<?php echo strtolower(htmlspecialchars($complaint['status_label'])); ?>"
                             data-case="<?php echo strtolower(htmlspecialchars($complaint['case_type'])); ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($complaint['incident_title'] ?? 'Untitled'); ?></h5>
                                    <div>
                                        <span class="status-badge status-<?php echo strtolower($complaint['status_label']); ?> me-2">
                                            <?php if (strtolower($complaint['status_label']) === 'resolved' && !empty($complaint['updated_at'])): ?>
                                                <i class="fas fa-check-circle"></i> Resolved
                                                <small class="d-block"><?php echo date('M j, Y', strtotime($complaint['updated_at'])); ?></small>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($complaint['status_label']); ?>
                                            <?php endif; ?>
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
                                    <div class="col-12">
                                        <strong class="text-muted">Description:</strong>
                                        <p class="mb-0"><?php echo htmlspecialchars(substr($complaint['narrative'] ?? 'No description', 0, 200)) . '...'; ?></p>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <button class="btn btn-primary btn-sm view-details-btn" data-id="<?php echo $complaint['id']; ?>">
                                            <i class="fas fa-eye"></i> View Full Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <!-- No results message (hidden by default, shown by JS when filtering) -->
                    <div class="alert alert-warning" id="noResultsMessage" style="display: none;">
                        <i class="fas fa-search"></i> No complaints match your search criteria. Try different keywords or adjust filters.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No complaints available to display.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
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

<?php include_once __DIR__ . '/../../components/resident_components/footer-resident.php'?>