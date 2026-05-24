<?php
$pageTitle = 'Police Complaint Dashboard';
$pageSubtitle = 'Monitor and update resident blotter complaints';
$currentPage = 'admin_complaints';

$dashboardBase = defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '';
$updateActionUrl = $dashboardBase . '/index.php?action=police_updateComplaintStatus';
$detailsActionUrl = $dashboardBase . '/index.php?action=police_getComplaintDetails';
$policeTab = strtolower(trim((string) ($_GET['tab'] ?? 'dashboard')));
$searchTerm = trim((string) ($_GET['search'] ?? ''));
$statusFilter = trim((string) ($_GET['status_id'] ?? ''));
$caseTypeFilter = trim((string) ($_GET['case_type_id'] ?? ''));
$historySearchTerm = trim((string) ($_GET['history_search'] ?? ''));
$historyStatusFilter = trim((string) ($_GET['history_status_id'] ?? ''));
$historyFrom = trim((string) ($_GET['history_from'] ?? ''));
$historyTo = trim((string) ($_GET['history_to'] ?? ''));

if (!in_array($policeTab, ['dashboard', 'complaints', 'history', 'profile'], true)) {
    $policeTab = 'dashboard';
}

include __DIR__ . '/../../components/admin_components/header-admin.php';

if (!function_exists('police_status_badge_class')) {
    function police_status_badge_class($statusLabel)
    {
        $normalized = strtolower(trim((string) $statusLabel));
        if ($normalized === 'resolved') {
            return 'resolved';
        }

        if ($normalized === 'ongoing' || $normalized === 'investigating') {
            return 'ongoing';
        }

        return 'pending';
    }
}

$summaryComplaints = is_array($dashboardComplaints ?? null) ? $dashboardComplaints : ($complaints ?? []);
$totalComplaints = count($summaryComplaints);
$pendingComplaints = 0;
$ongoingComplaints = 0;
$resolvedComplaints = 0;

foreach ($summaryComplaints as $item) {
    $statusClass = police_status_badge_class((string) ($item['status_label'] ?? ''));
    if ($statusClass === 'resolved') {
        $resolvedComplaints++;
    } elseif ($statusClass === 'ongoing') {
        $ongoingComplaints++;
    } else {
        $pendingComplaints++;
    }
}

$recentComplaints = array_slice($complaints ?? [], 0, 10);
$viewAllUrl = $dashboardBase . '/index.php?page=dashboard_police&tab=complaints';
$refreshDashboardUrl = $dashboardBase . '/index.php?page=dashboard_police&tab=dashboard';

$statusOverview = [
    'Pending' => $pendingComplaints,
    'Ongoing' => $ongoingComplaints,
    'Resolved' => $resolvedComplaints,
];

$investigatingStatusId = null;
$resolvedStatusIds = [];
foreach ($availableStatuses as $status) {
    $statusLabel = strtolower(trim((string) ($status['label'] ?? '')));
    if ($statusLabel === 'investigating') {
        $investigatingStatusId = (int) ($status['id'] ?? 0);
    }
    if ($statusLabel === 'resolved' || (int) ($status['id'] ?? 0) === 3) {
        $resolvedStatusIds[] = (int) ($status['id'] ?? 0);
    }
}
?>

<style>
    .police-dashboard .panel {
        border: 1px solid #dce3ea;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .police-dashboard .panel-header {
        border-bottom: 1px solid #e5ebf1;
        padding: 1.25rem 1.5rem;
    }

    .police-dashboard .panel-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1.5rem 0;
        flex-wrap: wrap;
    }

    .police-dashboard .search-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: nowrap;
    }

    .police-dashboard .filter-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .police-dashboard .filter-form .form-select,
    .police-dashboard .filter-form .btn {
        height: 42px;
        font-size: 0.96rem;
        border-radius: 8px;
    }

    .police-dashboard .search-form .form-control {
        min-width: 240px;
        height: 42px;
        font-size: 0.98rem;
        border-radius: 8px;
        flex: 1 1 auto;
    }

    .police-dashboard h2 {
        color: #1f2937;
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
    }

    .police-dashboard .lead-text {
        margin: 0.35rem 0 0;
        color: #4b5563;
        font-size: 1rem;
    }

    .police-dashboard .panel-body {
        padding: 0;
    }

    .police-dashboard .table-wrap {
        overflow-x: auto;
    }

    .police-dashboard table {
        width: 100%;
        min-width: 1150px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .police-dashboard table.history-table {
        min-width: 1280px;
    }

    .police-dashboard thead th {
        background: #f6f9fc;
        color: #334155;
        font-size: 0.95rem;
        font-weight: 700;
        padding: 0.95rem 0.85rem;
        border-bottom: 1px solid #dbe4ee;
        white-space: nowrap;
    }

    .police-dashboard tbody td {
        padding: 0.95rem 0.85rem;
        vertical-align: top;
        border-bottom: 1px solid #edf2f7;
        color: #1f2937;
        font-size: 1rem;
    }

    .police-dashboard tbody tr:hover {
        background: #fafcff;
    }

    .police-dashboard .complaint-id {
        font-weight: 700;
        color: #1e3a5f;
    }

    .police-dashboard .desc {
        max-width: 320px;
        color: #374151;
        line-height: 1.45;
    }

    .police-dashboard .status-badge {
        display: inline-block;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        font-size: 0.9rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .police-dashboard .status-badge.pending {
        background: #fff7e6;
        color: #8a5a00;
        border-color: #f3cf7a;
    }

    .police-dashboard .status-badge.ongoing {
        background: #e8f1ff;
        color: #1f4f94;
        border-color: #9ec1ff;
    }

    .police-dashboard .status-badge.resolved {
        background: #eaf8ef;
        color: #1f7a3e;
        border-color: #a9dfb8;
    }

    .police-dashboard .status-form {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.55rem;
        min-width: 260px;
    }

    .police-dashboard .status-form select,
    .police-dashboard .status-form input,
    .police-dashboard .status-form button {
        height: 42px;
        font-size: 0.98rem;
        border-radius: 8px;
    }

    .police-dashboard .status-form select {
        min-width: 145px;
        border: 1px solid #cbd5e1;
        padding: 0 0.6rem;
    }

    .police-dashboard .status-form input {
        min-width: 190px;
        border: 1px solid #cbd5e1;
        padding: 0 0.75rem;
    }

    .police-dashboard .status-form button {
        background: #1e3a5f;
        color: #ffffff;
        border: 1px solid #1e3a5f;
        min-width: 76px;
        padding: 0 0.95rem;
        font-weight: 600;
    }

    .police-dashboard .status-form button:hover {
        background: #2b527f;
        border-color: #2b527f;
    }

    .police-dashboard .complaints-table .col-actions,
    .police-dashboard .complaints-table .col-details {
        vertical-align: middle;
        white-space: nowrap;
    }

    .police-dashboard .complaints-table .col-actions {
        min-width: 270px;
        text-align: right;
    }

    .police-dashboard .complaints-table .col-details {
        text-align: left;
        width: 96px;
    }

    .police-dashboard .complaints-table .police-view-details {
        height: 42px;
        min-width: 76px;
        padding: 0 0.95rem;
        border-radius: 8px;
        font-size: 0.98rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .police-dashboard .empty {
        text-align: center;
        color: #64748b;
        padding: 2rem 1rem;
        font-size: 1.06rem;
    }

    .police-dashboard .quick-actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .police-dashboard .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.9rem;
        margin-bottom: 1rem;
    }

    .police-dashboard .summary-card {
        border: 1px solid #dce3ea;
        border-radius: 12px;
        background: #ffffff;
        padding: 1rem 1.1rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        min-width: 0;
    }

    .police-dashboard .summary-card h3 {
        margin: 0;
        color: #1e3a5f;
        font-size: 1.9rem;
        font-weight: 700;
    }

    .police-dashboard .summary-card p {
        margin: 0.3rem 0 0;
        color: #5a6b82;
        font-size: 0.98rem;
        font-weight: 500;
    }

    .police-dashboard .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) clamp(300px, 32%, 420px);
        gap: 1rem;
        align-items: start;
    }

    .police-dashboard .dashboard-grid > * {
        min-width: 0;
    }

    .police-dashboard .panel,
    .police-dashboard .table-wrap {
        max-width: 100%;
        min-width: 0;
    }

    .police-dashboard .panel-table-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.7rem;
        padding: 1rem 1.5rem 0;
    }

    .police-dashboard .chart-panel-body {
        padding: 1rem 1.2rem 1.15rem;
    }

    .police-dashboard .chart-row {
        margin-bottom: 0.85rem;
    }

    .police-dashboard .chart-row:last-child {
        margin-bottom: 0;
    }

    .police-dashboard .chart-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.3rem;
        font-size: 0.93rem;
        color: #334155;
        font-weight: 600;
    }

    .police-dashboard .chart-track {
        width: 100%;
        height: 14px;
        background: #e5edf5;
        border-radius: 999px;
        overflow: hidden;
    }

    .police-dashboard .chart-fill {
        height: 100%;
        border-radius: 999px;
    }

    .police-dashboard .chart-fill.pending {
        background: #d4a017;
    }

    .police-dashboard .chart-fill.ongoing {
        background: #3b82f6;
    }

    .police-dashboard .chart-fill.resolved {
        background: #16a34a;
    }

    .police-dashboard .btn-police {
        background: #1e3a5f;
        border-color: #1e3a5f;
        color: #ffffff;
        font-weight: 600;
    }

    .police-dashboard .btn-police:hover {
        background: #2b527f;
        border-color: #2b527f;
        color: #ffffff;
    }

    .police-dashboard .profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
    }

    .police-dashboard .profile-card {
        border: 1px solid #dce3ea;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
    }

    .police-dashboard .profile-card-header {
        border-bottom: 1px solid #e8edf3;
        padding: 1rem 1.2rem;
    }

    .police-dashboard .profile-card-header h3 {
        margin: 0;
        font-size: 1.18rem;
        font-weight: 700;
        color: #1f2937;
    }

    .police-dashboard .profile-card-header p {
        margin: 0.35rem 0 0;
        color: #5b6674;
        font-size: 0.94rem;
    }

    .police-dashboard .profile-card-body {
        padding: 1rem 1.2rem 1.15rem;
    }

    .police-dashboard .info-list {
        display: grid;
        gap: 0.75rem;
    }

    .police-dashboard .info-item {
        background: #f8fbff;
        border: 1px solid #e4ebf3;
        border-radius: 8px;
        padding: 0.65rem 0.75rem;
    }

    .police-dashboard .info-item .label {
        display: block;
        color: #64748b;
        font-size: 0.84rem;
        margin-bottom: 0.2rem;
    }

    .police-dashboard .info-item .value {
        display: block;
        color: #1f2937;
        font-size: 1rem;
        font-weight: 600;
        word-break: break-word;
    }

    .police-dashboard .profile-form .form-label {
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.35rem;
    }

    .police-dashboard .profile-form .form-control {
        min-height: 42px;
        font-size: 0.98rem;
    }

    .police-dashboard .profile-form .form-text {
        font-size: 0.84rem;
        color: #64748b;
    }

    .police-dashboard .form-actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-top: 0.8rem;
    }

    .police-dashboard .profile-alert {
        display: none;
        border-radius: 8px;
        padding: 0.65rem 0.8rem;
        margin-bottom: 0.9rem;
        font-size: 0.93rem;
    }

    .police-dashboard .profile-alert.success {
        display: block;
        background: #eaf8ef;
        border: 1px solid #a8dfb8;
        color: #145a2b;
    }

    .police-dashboard .profile-alert.error {
        display: block;
        background: #fef2f2;
        border: 1px solid #f5b3b3;
        color: #9f1d1d;
    }

    .police-dashboard .details-grid,
    #policeComplaintModal .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
    }

    .police-dashboard .details-card,
    #policeComplaintModal .details-card {
        border: 1px solid #e5ebf1;
        border-radius: 12px;
        background: #f8fbff;
        padding: 1rem 1.1rem;
    }

    .police-dashboard .details-card h4,
    #policeComplaintModal .details-card h4 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.65rem;
        color: #1f2937;
    }

    .police-dashboard .details-list,
    #policeComplaintModal .details-list {
        display: grid;
        gap: 0.55rem;
    }

    .police-dashboard .details-item span,
    #policeComplaintModal .details-item span {
        display: block;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .police-dashboard .details-item strong,
    #policeComplaintModal .details-item strong {
        color: #1f2937;
        font-size: 0.98rem;
        font-weight: 600;
        word-break: break-word;
    }

    .police-dashboard .details-narrative,
    #policeComplaintModal .details-narrative {
        border: 1px solid #e5ebf1;
        border-radius: 12px;
        padding: 1rem;
        background: #ffffff;
        color: #1f2937;
        line-height: 1.55;
    }

    .police-dashboard .details-actions,
    #policeComplaintModal .details-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 0.8rem;
    }

    .police-dashboard .btn-outline-police,
    #policeComplaintModal .btn-outline-police {
        border: 1px solid #1e3a5f;
        color: #1e3a5f;
        background: transparent;
        font-weight: 600;
    }

    .police-dashboard .btn-outline-police:hover,
    #policeComplaintModal .btn-outline-police:hover {
        background: #1e3a5f;
        color: #ffffff;
    }

    #policeComplaintModal .modal-content {
        border-radius: 14px;
        border: 1px solid #dce3ea;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }

    #policeComplaintModal .modal-header {
        background: var(--primary-blue, #1e3a5f);
        color: #ffffff;
        border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        padding: 1rem 1.25rem;
    }

    #policeComplaintModal .modal-title {
        color: #ffffff;
        font-weight: 800;
        letter-spacing: 0.2px;
    }

    #policeComplaintModal .btn-close {
        filter: invert(1) grayscale(100%);
        opacity: 0.85;
    }

    #policeComplaintModal .btn-close:hover {
        opacity: 1;
    }

    #policeComplaintModal .modal-body {
        padding: 1.15rem 1.25rem 1.35rem;
        background: #ffffff;
    }

    @media (max-width: 768px) {
        .police-dashboard h2 {
            font-size: 1.35rem;
        }

        .police-dashboard .lead-text,
        .police-dashboard tbody td {
            font-size: 0.96rem;
        }

        .police-dashboard .summary-grid {
            grid-template-columns: 1fr 1fr;
        }

        .police-dashboard .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .police-dashboard .search-form {
            flex-wrap: wrap;
        }
    }
</style>

<main class="main-content police-dashboard">
    <div class="container-xxl px-4 py-4">
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($policeTab === 'dashboard'): ?>
            <div class="quick-actions">
                <a href="<?php echo htmlspecialchars($viewAllUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-police">View All Complaints</a>
                <a href="<?php echo htmlspecialchars($refreshDashboardUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Refresh Data</a>
            </div>

            <div class="summary-grid">
                <div class="summary-card">
                    <h3><?php echo (int) $totalComplaints; ?></h3>
                    <p>Total Complaints</p>
                </div>
                <div class="summary-card">
                    <h3><?php echo (int) $pendingComplaints; ?></h3>
                    <p>Pending Complaints</p>
                </div>
                <div class="summary-card">
                    <h3><?php echo (int) $ongoingComplaints; ?></h3>
                    <p>Ongoing Complaints</p>
                </div>
                <div class="summary-card">
                    <h3><?php echo (int) $resolvedComplaints; ?></h3>
                    <p>Resolved Complaints</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <section class="panel">
                    <div class="panel-header">
                        <h2>Recent Complaints</h2>
                        <p class="lead-text">Latest complaint records submitted by residents.</p>
                    </div>
                    <div class="panel-table-top">
                        <small class="text-muted">Showing latest <?php echo count($recentComplaints); ?> complaint(s)</small>
                        <a href="<?php echo htmlspecialchars($viewAllUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-police">View All</a>
                    </div>
                    <div class="panel-body">
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Complaint ID</th>
                                        <th>Complainant Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentComplaints)): ?>
                                        <tr>
                                            <td colspan="5" class="empty">No complaint records found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentComplaints as $complaint): ?>
                                            <?php $statusClass = police_status_badge_class((string) ($complaint['status_label'] ?? 'Pending')); ?>
                                            <tr>
                                                <td class="complaint-id">#<?php echo (int) ($complaint['id'] ?? 0); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($complaint['complainant_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($complaint['blotter_type'] ?? ($complaint['case_type'] ?? 'N/A')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $statusClass; ?>">
                                                        <?php echo htmlspecialchars((string) ($complaint['status_label'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $createdAt = (string) ($complaint['created_at'] ?? '');
                                                    echo $createdAt !== ''
                                                        ? htmlspecialchars(date('M d, Y', strtotime($createdAt)), ENT_QUOTES, 'UTF-8')
                                                        : 'N/A';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Status Overview</h2>
                        <p class="lead-text">Distribution of complaint statuses.</p>
                    </div>
                    <div class="chart-panel-body">
                        <?php foreach ($statusOverview as $label => $value): ?>
                            <?php
                            $cssClass = strtolower($label);
                            $percent = $totalComplaints > 0 ? round(($value / $totalComplaints) * 100, 1) : 0;
                            ?>
                            <div class="chart-row">
                                <div class="chart-label">
                                    <span><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span><?php echo (int) $value; ?> (<?php echo htmlspecialchars((string) $percent, ENT_QUOTES, 'UTF-8'); ?>%)</span>
                                </div>
                                <div class="chart-track">
                                    <div class="chart-fill <?php echo htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'); ?>" style="width: <?php echo htmlspecialchars((string) $percent, ENT_QUOTES, 'UTF-8'); ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        <?php elseif ($policeTab === 'complaints'): ?>
            <section class="panel">
                <div class="panel-header">
                    <h2>All Resident Complaint Records</h2>
                    <p class="lead-text">All complaints filed by residents are listed here automatically. Update the status per complaint as needed.</p>
                </div>

                <div class="panel-toolbar">
                    <form class="search-form" method="get" action="<?php echo htmlspecialchars($dashboardBase . '/index.php', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="page" value="dashboard_police">
                        <input type="hidden" name="tab" value="complaints">
                        <input type="search" name="search" class="form-control" placeholder="Search complaints..." value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-outline-secondary">Search</button>
                    </form>
                </div>
                <?php if ($searchTerm !== ''): ?>
                    <div class="px-4 pb-2 text-muted" style="font-size:0.95rem;">
                        Showing <?php echo count($complaints ?? []); ?> result(s)
                    </div>
                <?php endif; ?>

                <div class="panel-body">
                    <div class="table-wrap">
                        <table class="complaints-table">
                            <thead>
                                <tr>
                                    <th>Complaint ID</th>
                                    <th>Complainant Name</th>
                                    <th>Complaint Type</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Date Submitted</th>
                                    <th class="col-actions">Update Status</th>
                                    <th class="col-details">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($complaints)): ?>
                                    <tr>
                                        <td colspan="8" class="empty">No complaint records found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($complaints as $complaint): ?>
                                        <?php
                                        $statusLabel = (string) ($complaint['status_label'] ?? 'Pending');
                                        $badgeClass = police_status_badge_class($statusLabel);
                                        $description = trim((string) ($complaint['narrative'] ?? 'No description provided.'));
                                        if (mb_strlen($description) > 140) {
                                            $description = mb_substr($description, 0, 140) . '...';
                                        }
                                        ?>
                                        <tr>
                                            <td class="complaint-id">#<?php echo (int) ($complaint['id'] ?? 0); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($complaint['complainant_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($complaint['blotter_type'] ?? ($complaint['case_type'] ?? 'N/A')), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="desc"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $badgeClass; ?>">
                                                    <?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $submittedAt = (string) ($complaint['created_at'] ?? '');
                                                echo $submittedAt !== ''
                                                    ? htmlspecialchars(date('M d, Y h:i A', strtotime($submittedAt)), ENT_QUOTES, 'UTF-8')
                                                    : 'N/A';
                                                ?>
                                            </td>
                                            <td class="col-actions">
                                                <form class="status-form" method="POST" action="<?php echo htmlspecialchars($updateActionUrl, ENT_QUOTES, 'UTF-8'); ?>" data-resolved-status-ids="<?php echo htmlspecialchars(json_encode($resolvedStatusIds), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo csrf_input(); ?>
                                                    <input type="hidden" name="id" value="<?php echo (int) ($complaint['id'] ?? 0); ?>">
                                                    <select name="status_id" required aria-label="Update complaint status">
                                                        <?php foreach ($availableStatuses as $status): ?>
                                                            <?php $statusId = (int) ($status['id'] ?? 0); ?>
                                                            <option value="<?php echo $statusId; ?>" <?php echo ((int) ($complaint['status_id'] ?? 0) === $statusId) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars((string) ($status['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="text" name="remarks" class="form-control resolution-remarks" placeholder="Resolution remarks required" aria-label="Resolution remarks required for complaint history" hidden>
                                                    <button type="submit">Update</button>
                                                </form>
                                            </td>
                                            <td class="col-details">
                                                <button type="button" class="btn btn-outline-secondary btn-sm police-view-details" data-id="<?php echo (int) ($complaint['id'] ?? 0); ?>">
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php elseif ($policeTab === 'history'): ?>
            <section class="panel">
                <div class="panel-header">
                    <h2>Complaint History</h2>
                    <p class="lead-text">Audit log of status updates made by police users on complaint records.</p>
                </div>

                <div class="panel-body">
                    <?php if (!empty($historyLoadError)): ?>
                        <div class="px-4 py-4">
                            <div class="alert alert-warning mb-0" role="alert">
                                <?php echo htmlspecialchars($historyLoadError, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    <?php else: ?>
                    <div class="panel-toolbar">
                        <form class="search-form" method="get" action="<?php echo htmlspecialchars($dashboardBase . '/index.php', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="page" value="dashboard_police">
                            <input type="hidden" name="tab" value="history">
                            <input type="search" name="history_search" class="form-control" placeholder="Search complaint ID or complainant..." value="<?php echo htmlspecialchars((string) ($historyFilters['search'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <select name="history_status_id" class="form-select">
                                <option value="">All Statuses</option>
                                <?php foreach ($allStatuses as $status): ?>
                                    <?php $statusId = (int) ($status['id'] ?? 0); ?>
                                    <option value="<?php echo $statusId; ?>" <?php echo ((string) ($historyFilters['status_id'] ?? '') === (string) $statusId) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string) ($status['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="history_from" class="form-control" value="<?php echo htmlspecialchars((string) ($historyFilters['from'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="date" name="history_to" class="form-control" value="<?php echo htmlspecialchars((string) ($historyFilters['to'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-outline-secondary">Filter</button>
                        </form>
                    </div>
                    <div class="table-wrap">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Complaint ID</th>
                                    <th>Complainant Name</th>
                                    <th>Previous Status</th>
                                    <th>New Status</th>
                                    <th>Updated By</th>
                                    <th>Date & Time Updated</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($historyRecords)): ?>
                                    <tr>
                                        <td colspan="7" class="empty">No complaint history records found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($historyRecords as $history): ?>
                                        <?php
                                        $previousStatus = (string) ($history['previous_status_label'] ?? 'N/A');
                                        $updatedStatus = (string) ($history['updated_status_label'] ?? 'N/A');
                                        ?>
                                        <tr>
                                            <td class="complaint-id">#<?php echo (int) ($history['complaint_id'] ?? 0); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($history['complainant_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo police_status_badge_class($previousStatus); ?>">
                                                    <?php echo htmlspecialchars($previousStatus, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo police_status_badge_class($updatedStatus); ?>">
                                                    <?php echo htmlspecialchars($updatedStatus, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars((string) ($history['updated_by_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php
                                                $updatedAt = (string) ($history['created_at'] ?? '');
                                                echo $updatedAt !== ''
                                                    ? htmlspecialchars(date('M d, Y h:i A', strtotime($updatedAt)), ENT_QUOTES, 'UTF-8')
                                                    : 'N/A';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars((string) ($history['remarks'] ?? 'No remarks'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <div class="profile-grid">
                <section class="profile-card">
                    <div class="profile-card-header">
                        <h3>Basic Information</h3>
                        <p>View-only account details for the currently logged in police user.</p>
                    </div>
                    <div class="profile-card-body">
                        <div id="profileBasicAlert" class="profile-alert"></div>
                        <div class="info-list" id="profileBasicInfo">
                            <div class="info-item">
                                <span class="label">Full Name</span>
                                <span class="value" id="profileViewFullName">Loading...</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Username</span>
                                <span class="value" id="profileViewUsername">Loading...</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Email Address</span>
                                <span class="value" id="profileViewEmail">Loading...</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Contact Number</span>
                                <span class="value" id="profileViewContact">Loading...</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Role</span>
                                <span class="value" id="profileViewRole">Police</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Date Created</span>
                                <span class="value" id="profileViewCreated">N/A</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Last Login</span>
                                <span class="value" id="profileViewLastLogin">N/A</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="profile-card">
                    <div class="profile-card-header">
                        <h3>Edit Profile</h3>
                        <p>Update your name, email address, and contact number.</p>
                    </div>
                    <div class="profile-card-body">
                        <div id="profileEditAlert" class="profile-alert"></div>
                        <form id="policeEditProfileForm" class="profile-form" method="post" action="<?php echo htmlspecialchars($dashboardBase . '/index.php?action=update_official_profile', ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo csrf_input(); ?>
                            <div class="mb-3">
                                <label class="form-label" for="editFullName">Full Name</label>
                                <input type="text" class="form-control" id="editFullName" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="editEmail">Email Address</label>
                                <input type="email" class="form-control" id="editEmail" name="email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="editContact">Contact Number</label>
                                <input type="text" class="form-control" id="editContact" name="contact_no">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-police">Save</button>
                                <button type="button" class="btn btn-outline-secondary" id="cancelProfileEdit">Cancel</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="profile-card">
                    <div class="profile-card-header">
                        <h3>Change Password</h3>
                        <p>Use a strong password with at least 8 characters.</p>
                    </div>
                    <div class="profile-card-body">
                        <div id="passwordAlert" class="profile-alert"></div>
                        <form id="policeChangePasswordForm" class="profile-form" method="post" action="<?php echo htmlspecialchars($dashboardBase . '/index.php?action=police_change_password', ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo csrf_input(); ?>
                            <div class="mb-3">
                                <label class="form-label" for="currentPassword">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="newPassword">New Password</label>
                                <input type="password" class="form-control" id="newPassword" name="new_password" minlength="8" required>
                                <div class="form-text">Minimum length: 8 characters.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="confirmPassword">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" minlength="8" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-police">Update Password</button>
                                <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="policeComplaintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complaint Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="policeComplaintAlert" class="alert alert-danger d-none"></div>
                <div class="details-grid" id="policeComplaintDetails"></div>
                <div class="mt-3">
                    <h4 class="mb-2">Incident Narrative</h4>
                    <div class="details-narrative" id="policeComplaintNarrative">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var activeTab = <?php echo json_encode($policeTab); ?>;
        var basePublic = <?php echo json_encode($dashboardBase); ?>;

        var menu = document.querySelector('.sidebar-menu');
        if (!menu) {
            return;
        }

        var items = [
            {
                key: 'dashboard',
                icon: 'fas fa-th-large',
                label: 'Dashboard',
                href: basePublic + '/index.php?page=dashboard_police&tab=dashboard'
            },
            {
                key: 'complaints',
                icon: 'fas fa-exclamation-circle',
                label: 'Complaints / Blotter',
                href: basePublic + '/index.php?page=dashboard_police&tab=complaints'
            },
            {
                key: 'history',
                icon: 'fas fa-history',
                label: 'Complaint History',
                href: basePublic + '/index.php?page=dashboard_police&tab=history'
            },
            {
                key: 'profile',
                icon: 'fas fa-user',
                label: 'Profile',
                href: basePublic + '/index.php?page=dashboard_police&tab=profile'
            }
        ];

        menu.innerHTML = items.map(function (item) {
            var activeClass = item.key === activeTab ? 'active' : '';
            return '<li>' +
                '<a href="' + item.href + '" class="' + activeClass + '" data-tooltip="' + item.label + '">' +
                '<i class="' + item.icon + '"></i>' +
                '<span>' + item.label + '</span>' +
                '</a>' +
                '</li>';
        }).join('');

        function showProfileAlert(el, type, message) {
            if (!el) return;
            el.className = 'profile-alert ' + type;
            el.textContent = message;
        }

        if (activeTab !== 'profile') {
            return;
        }

        var profileBasicAlert = document.getElementById('profileBasicAlert');
        var profileEditAlert = document.getElementById('profileEditAlert');
        var passwordAlert = document.getElementById('passwordAlert');
        var editForm = document.getElementById('policeEditProfileForm');
        var cancelBtn = document.getElementById('cancelProfileEdit');
        var passwordForm = document.getElementById('policeChangePasswordForm');

        var viewFullName = document.getElementById('profileViewFullName');
        var viewUsername = document.getElementById('profileViewUsername');
        var viewEmail = document.getElementById('profileViewEmail');
        var viewContact = document.getElementById('profileViewContact');
        var viewRole = document.getElementById('profileViewRole');
        var viewCreated = document.getElementById('profileViewCreated');
        var viewLastLogin = document.getElementById('profileViewLastLogin');

        var editFullName = document.getElementById('editFullName');
        var editEmail = document.getElementById('editEmail');
        var editContact = document.getElementById('editContact');

        var profileSnapshot = {
            full_name: '',
            email: '',
            contact_no: ''
        };

        function valueOrNA(val) {
            if (val === null || val === undefined) return 'N/A';
            var s = String(val).trim();
            return s === '' ? 'N/A' : s;
        }

        function bindProfile(profile) {
            profileSnapshot.full_name = profile.full_name || '';
            profileSnapshot.email = profile.email || '';
            profileSnapshot.contact_no = profile.contact_no || '';

            viewFullName.textContent = valueOrNA(profile.full_name);
            viewUsername.textContent = valueOrNA(profile.username);
            viewEmail.textContent = valueOrNA(profile.email);
            viewContact.textContent = valueOrNA(profile.contact_no);
            viewRole.textContent = valueOrNA(profile.role || 'Police');
            viewCreated.textContent = valueOrNA(profile.created_at);
            viewLastLogin.textContent = valueOrNA(profile.last_login_at);

            editFullName.value = profileSnapshot.full_name;
            editEmail.value = profileSnapshot.email;
            editContact.value = profileSnapshot.contact_no;
        }

        function loadProfile() {
            fetch(basePublic + '/index.php?action=get_official_profile', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (!json || json.success !== true || !json.data) {
                    showProfileAlert(profileBasicAlert, 'error', 'Unable to load profile information.');
                    return;
                }

                bindProfile(json.data);
            })
            .catch(function () {
                showProfileAlert(profileBasicAlert, 'error', 'Unable to load profile information.');
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                editFullName.value = profileSnapshot.full_name;
                editEmail.value = profileSnapshot.email;
                editContact.value = profileSnapshot.contact_no;
                if (profileEditAlert) {
                    profileEditAlert.className = 'profile-alert';
                    profileEditAlert.textContent = '';
                }
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', function (event) {
                event.preventDefault();

                var fd = new FormData(editForm);
                fetch(editForm.action, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (!json || json.success !== true) {
                        showProfileAlert(profileEditAlert, 'error', (json && json.message) ? json.message : 'Failed to save profile.');
                        return;
                    }

                    showProfileAlert(profileEditAlert, 'success', json.message || 'Profile updated successfully.');
                    loadProfile();
                })
                .catch(function () {
                    showProfileAlert(profileEditAlert, 'error', 'Failed to save profile.');
                });
            });
        }

        if (passwordForm) {
            passwordForm.addEventListener('submit', function (event) {
                event.preventDefault();

                var currentPw = (document.getElementById('currentPassword').value || '').trim();
                var newPw = (document.getElementById('newPassword').value || '').trim();
                var confirmPw = (document.getElementById('confirmPassword').value || '').trim();

                if (newPw.length < 8) {
                    showProfileAlert(passwordAlert, 'error', 'New password must be at least 8 characters long.');
                    return;
                }

                if (newPw !== confirmPw) {
                    showProfileAlert(passwordAlert, 'error', 'New password and confirm password do not match.');
                    return;
                }

                if (currentPw === newPw) {
                    showProfileAlert(passwordAlert, 'error', 'New password must be different from current password.');
                    return;
                }

                var fd = new FormData(passwordForm);
                fetch(passwordForm.action, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (!json || json.success !== true) {
                        showProfileAlert(passwordAlert, 'error', (json && json.message) ? json.message : 'Failed to update password.');
                        return;
                    }

                    showProfileAlert(passwordAlert, 'success', json.message || 'Password updated successfully.');
                    passwordForm.reset();
                })
                .catch(function () {
                    showProfileAlert(passwordAlert, 'error', 'Failed to update password.');
                });
            });
        }

        loadProfile();
    })();
</script>

<script>
    (function () {
        var basePublic = <?php echo json_encode($dashboardBase); ?>;
        var detailsUrl = <?php echo json_encode($detailsActionUrl); ?>;
        var updateStatusUrl = <?php echo json_encode($updateActionUrl); ?>;
        var modalEl = document.getElementById('policeComplaintModal');
        var detailsContainer = document.getElementById('policeComplaintDetails');
        var narrativeEl = document.getElementById('policeComplaintNarrative');
        var alertEl = document.getElementById('policeComplaintAlert');

        function showAlert(type, message) {
            if (!alertEl) return;
            alertEl.textContent = message;
            alertEl.classList.remove('d-none');
            alertEl.classList.remove('alert-danger', 'alert-success');
            alertEl.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
        }

        function clearAlert() {
            if (!alertEl) return;
            alertEl.textContent = '';
            alertEl.classList.add('d-none');
            alertEl.classList.remove('alert-danger', 'alert-success');
        }

        document.querySelectorAll('.status-form').forEach(function (form) {
            var statusSelect = form.querySelector('select[name="status_id"]');
            var remarksInput = form.querySelector('input[name="remarks"]');
            if (!statusSelect || !remarksInput) return;

            var resolvedIds = [];
            try {
                resolvedIds = JSON.parse(form.getAttribute('data-resolved-status-ids') || '[]').map(String);
            } catch (error) {
                resolvedIds = [];
            }

            function isResolvedSelection() {
                var selectedOption = statusSelect.options[statusSelect.selectedIndex];
                var selectedLabel = selectedOption ? selectedOption.textContent.trim().toLowerCase() : '';
                return resolvedIds.indexOf(String(statusSelect.value)) !== -1 || selectedLabel === 'resolved';
            }

            function syncRemarksRequirement() {
                var requiresRemarks = isResolvedSelection();
                remarksInput.hidden = !requiresRemarks;
                remarksInput.required = requiresRemarks;
                if (!requiresRemarks) {
                    remarksInput.value = '';
                }
                remarksInput.setAttribute(
                    'aria-label',
                    requiresRemarks ? 'Resolution remarks required for complaint history' : 'Remarks for complaint history'
                );
                remarksInput.setCustomValidity('');
            }

            statusSelect.addEventListener('change', syncRemarksRequirement);
            form.addEventListener('submit', function (event) {
                syncRemarksRequirement();
                if (isResolvedSelection() && remarksInput.value.trim() === '') {
                    event.preventDefault();
                    remarksInput.setCustomValidity('Resolution remarks are required when marking a complaint as Resolved.');
                    remarksInput.reportValidity();
                    remarksInput.focus();
                    return;
                }
                remarksInput.value = remarksInput.value.trim();
            });

            remarksInput.addEventListener('input', function () {
                remarksInput.setCustomValidity('');
            });

            syncRemarksRequirement();
        });

        function formatValue(value) {
            if (value === null || value === undefined || String(value).trim() === '') return 'N/A';
            return String(value);
        }

        function renderDetails(data) {
            var html = '';
            html += '<div class="details-card">' +
                '<h4>Complainant Information</h4>' +
                '<div class="details-list">' +
                '<div class="details-item"><span>Name</span><strong>' + formatValue(data.complainant_name) + '</strong></div>' +
                '<div class="details-item"><span>Type</span><strong>' + formatValue(data.complainant_type) + '</strong></div>' +
                '<div class="details-item"><span>Contact</span><strong>' + formatValue(data.complainant_contact) + '</strong></div>' +
                '<div class="details-item"><span>Gender</span><strong>' + formatValue(data.complainant_gender) + '</strong></div>' +
                '<div class="details-item"><span>Birthday</span><strong>' + formatValue(data.complainant_birthday) + '</strong></div>' +
                '<div class="details-item"><span>Address</span><strong>' + formatValue(data.complainant_address) + '</strong></div>' +
                '</div></div>';

            html += '<div class="details-card">' +
                '<h4>Offender Information</h4>' +
                '<div class="details-list">' +
                '<div class="details-item"><span>Name</span><strong>' + formatValue(data.offender_name) + '</strong></div>' +
                '<div class="details-item"><span>Type</span><strong>' + formatValue(data.offender_type) + '</strong></div>' +
                '<div class="details-item"><span>Gender</span><strong>' + formatValue(data.offender_gender) + '</strong></div>' +
                '<div class="details-item"><span>Address</span><strong>' + formatValue(data.offender_address) + '</strong></div>' +
                '<div class="details-item"><span>Description</span><strong>' + formatValue(data.offender_description) + '</strong></div>' +
                '</div></div>';

            html += '<div class="details-card">' +
                '<h4>Incident Details</h4>' +
                '<div class="details-list">' +
                '<div class="details-item"><span>Complaint ID</span><strong>#' + formatValue(data.id) + '</strong></div>' +
                '<div class="details-item"><span>Title</span><strong>' + formatValue(data.incident_title) + '</strong></div>' +
                '<div class="details-item"><span>Case Type</span><strong>' + formatValue(data.case_type) + '</strong></div>' +
                '<div class="details-item"><span>Status</span><strong>' + formatValue(data.status_label) + '</strong></div>' +
                '<div class="details-item"><span>Date</span><strong>' + formatValue(data.date_of_incident) + '</strong></div>' +
                '<div class="details-item"><span>Time</span><strong>' + formatValue(data.time_of_incident) + '</strong></div>' +
                '<div class="details-item"><span>Location</span><strong>' + formatValue(data.location) + '</strong></div>' +
                '<div class="details-item"><span>Blotter Type</span><strong>' + formatValue(data.blotter_type) + '</strong></div>' +
                '<div class="details-item"><span>Date Submitted</span><strong>' + formatValue(data.created_at) + '</strong></div>' +
                '<div class="details-item"><span>Forwarded to Police</span><strong>' + formatValue(data.forwarded_to_police_at) + '</strong></div>' +
                '</div></div>';

            detailsContainer.innerHTML = html;
            narrativeEl.textContent = formatValue(data.narrative);
        }

        function openModal() {
            if (!modalEl) return;
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        document.querySelectorAll('.police-view-details').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var complaintId = this.getAttribute('data-id');
                if (!complaintId) return;
                clearAlert();
                detailsContainer.innerHTML = '';
                narrativeEl.textContent = 'Loading...';

                fetch(detailsUrl + '&id=' + encodeURIComponent(complaintId), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (!json || json.success !== true || !json.data) {
                        showAlert('error', (json && json.message) ? json.message : 'Unable to load complaint details.');
                        return;
                    }
                    renderDetails(json.data);
                })
                .catch(function () {
                    showAlert('error', 'Unable to load complaint details.');
                });

                openModal();
            });
        });
    })();
</script>

<?php include __DIR__ . '/../../components/admin_components/footer-admin.php'; ?>
