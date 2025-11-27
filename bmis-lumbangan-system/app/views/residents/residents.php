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
        require_once __DIR__ . '/../../helpers/session_helper.php';
        
        try {
            $complaintModel = new Complaint();
            $filters = [
                'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
                'status_id' => isset($_GET['status_id']) ? trim($_GET['status_id']) : '',
                'case_type_id' => isset($_GET['case_type_id']) ? trim($_GET['case_type_id']) : ''
            ];

            // Get all complaints and filter by current user
            $allComplaints = $complaintModel->getAll($filters);
            $currentUserId = getUserId();
            $currentUserName = getFullName();
            
            // Filter complaints to show only those filed by current user
            // Check user_id first (more accurate), fall back to name matching for legacy data
            $complaints = array_filter($allComplaints, function($complaint) use ($currentUserId, $currentUserName) {
                // If complaint has user_id, match by user_id
                if (!empty($complaint['user_id'])) {
                    return $complaint['user_id'] == $currentUserId;
                }
                // Otherwise fall back to name matching for old complaints without user_id
                return strtolower(trim($complaint['complainant_name'])) === strtolower(trim($currentUserName));
            });
            
            // Calculate statistics for current user only
            $statistics = [
                'total' => 0,
                'pending' => 0,
                'investigating' => 0,
                'resolved' => 0
            ];
            
            foreach ($complaints as $complaint) {
                $statistics['total']++;
                if ($complaint['status_id'] == 1) {
                    $statistics['pending']++;
                } elseif ($complaint['status_id'] == 2) {
                    $statistics['investigating']++;
                } elseif ($complaint['status_id'] == 3) {
                    $statistics['resolved']++;
                }
            }
            
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

            <!-- Add New Complaint Button -->
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
                    <i class="fas fa-plus"></i>
                    File New Complaint
                </button>
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

    <!-- Add New Complaint Modal -->
    <div class="modal fade" id="newComplaintModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">File New Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="residentComplaintForm" autocomplete="off">
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo getUserId(); ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Please fill in all required fields marked with <span class="text-danger">*</span>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Complaint Title <span class="text-danger">*</span></label>
                                <input type="text" name="incident_title" class="form-control" required placeholder="Enter complaint title">
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
                                    <option value="Resident" selected>Resident</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <h6 class="mb-3">Your Information</h6>
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" name="complainant_name" class="form-control" required readonly value="<?php echo htmlspecialchars(getFullName()); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Contact Number</label>
                                                <input type="tel" name="complainant_contact" id="complainant_contact" class="form-control" placeholder="Enter your contact number">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                 <select name="complainant_gender" id="complainant_gender" class="form-select" required>
                                                    <option value="">Select Gender</option>
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
                                                    <option value="other">Prefer Not to Say</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Birthday</label>
                                                <input type="date" name="complainant_birthday" id="complainant_birthday" class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Address</label>
                                                <textarea name="complainant_address" id="complainant_address" class="form-control" rows="2" placeholder="Enter your address"></textarea>
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
                                                <input type="date" name="date_of_incident" class="form-control" required>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Time of Incident <span class="text-danger">*</span></label>
                                                <input type="time" name="time_of_incident" class="form-control" required>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                                <input type="text" name="location" class="form-control" required placeholder="Where did the incident occur?">
                                            </div>
                                            
                                            <div class="col-12">
                                                <label class="form-label">Narrative/Description <span class="text-danger">*</span></label>
                                                <textarea name="narrative" class="form-control" rows="5" required placeholder="Describe what happened in detail..."></textarea>
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
                    <button type="submit" form="residentComplaintForm" class="btn btn-primary" id="submitComplaintBtn">
                        <i class="fas fa-paper-plane"></i> Submit Complaint
                    </button>
                </div>
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

    <script>
    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
        // Handle form submission
        document.getElementById('residentComplaintForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitComplaintBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            const formData = new FormData(this);

            fetch('/Lumbangan_BMIS/bmis-lumbangan-system/public/index.php?action=createComplaint', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Complaint filed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to file complaint'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while filing the complaint');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });
    </script>

<?php include_once __DIR__ . '/../../components/resident_components/footer-resident.php'?>