<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/csrf_helper.php';

$displayName = getFullName();
$role = $_SESSION['role'] ?? 'RHU';
$basePublic = rtrim(BASE_PUBLIC, '/');
$lastUpdated = date('M d, Y g:i A');
$privacyMode = $privacyMode ?? 'full';
$filters = $filters ?? [];

$trendLabels = array_map(static fn($row) => !empty($row['month_key']) ? date('M Y', strtotime($row['month_key'] . '-01')) : 'N/A', $monthlyTrend);
$trendApproved = array_map(static fn($row) => (int) ($row['approved_count'] ?? 0), $monthlyTrend);
$trendHighRisk = array_map(static fn($row) => (int) ($row['high_risk'] ?? 0), $monthlyTrend);
$purokLabels = array_map(static fn($row) => (string) ($row['purok_name'] ?? 'Unassigned'), $purokBreakdown);
$purokHighRisk = array_map(static fn($row) => (int) ($row['high_risk'] ?? 0), $purokBreakdown);

function rhu_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rhu_selected($actual, $expected)
{
    return (string) $actual === (string) $expected ? 'selected' : '';
}

function rhu_name(array $row, $privacyMode)
{
    if ($privacyMode === 'anonymized') {
        return 'Resident #' . (int) ($row['person_id'] ?? $row['id'] ?? 0);
    }

    return trim((string) ($row['resident_name'] ?? 'Resident'));
}

function rhu_query_url($basePublic, array $filters, $privacyMode, $action)
{
    $query = array_filter(array_merge($filters, [
        'privacy' => $privacyMode,
        'action' => $action,
    ]), static fn($value) => $value !== '' && $value !== null);

    return rtrim($basePublic, '/') . '/index.php?' . http_build_query($query);
}

function rhu_risk_class($level)
{
    $level = strtolower((string) $level);
    if ($level === 'critical') return 'critical';
    if ($level === 'high') return 'high';
    if ($level === 'moderate') return 'moderate';
    return 'low';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo rhu_h(csrf_token()); ?>">
    <meta name="csrf-field" content="<?php echo rhu_h(csrf_field_name()); ?>">
    <title>RHU Analytics - Barangay Lumbangan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php render_favicon(); ?>
    <style>
        :root {
            --ink: #1f2937;
            --muted: #667085;
            --line: #e5e7eb;
            --panel: #ffffff;
            --page: #f6f8fb;
            --navy: #1e3a5f;
            --blue: #2c5282;
            --green: #15803d;
            --green-soft: #ecfdf3;
            --amber: #b54708;
            --amber-soft: #fffaeb;
            --red: #b42318;
            --red-soft: #fef3f2;
            --shadow: 0 8px 22px rgba(16, 24, 40, .06);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--page);
            color: var(--ink);
            font-family: "Poppins", Arial, sans-serif;
        }

        button, input, select, textarea { font: inherit; }

        .rhu-page {
            max-width: 1220px;
            margin: 0 auto;
            padding: 28px 22px 42px;
        }

        .rhu-header {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 18px;
            margin-bottom: 22px;
        }

        .rhu-logo {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: var(--panel);
            border: 1px solid var(--line);
            display: grid;
            place-items: center;
            box-shadow: var(--shadow);
        }

        .rhu-logo img {
            width: 46px;
            height: 46px;
            object-fit: contain;
        }

        .rhu-title h1 {
            margin: 0;
            font-size: clamp(1.35rem, 3vw, 2rem);
            font-weight: 700;
            line-height: 1.18;
        }

        .rhu-title p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: .92rem;
        }

        .rhu-account {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: flex-end;
        }

        .rhu-account-text {
            text-align: right;
        }

        .rhu-account-name {
            font-size: .92rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .rhu-account-role {
            margin-top: 2px;
            color: var(--muted);
            font-size: .78rem;
        }

        .icon-button {
            width: 40px;
            height: 40px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--panel);
            color: var(--muted);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: .2s;
        }

        .icon-button:hover {
            border-color: #cbd5e1;
            color: var(--navy);
            background: #f8fafc;
        }

        .rhu-panel,
        .rhu-kpi,
        .rhu-risk-item {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .rhu-filter-panel {
            padding: 16px;
            margin-bottom: 18px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: .76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .field input,
        .field select {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            color: var(--ink);
            padding: 8px 10px;
            outline: none;
        }

        .field input:focus,
        .field select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(44, 82, 130, .1);
        }

        .filter-actions {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }

        .btn {
            min-height: 40px;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 8px 13px;
            font-weight: 700;
            font-size: .86rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--navy);
            color: #fff;
        }

        .btn-secondary {
            background: #fff;
            color: var(--ink);
            border-color: var(--line);
        }

        .btn-secondary:hover {
            background: #f8fafc;
        }

        .rhu-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .rhu-chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: var(--panel);
            color: var(--muted);
            font-size: .78rem;
            font-weight: 600;
            padding: 7px 11px;
        }

        .rhu-kpis {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .rhu-kpi {
            min-height: 118px;
            padding: 18px;
        }

        .rhu-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .rhu-kpi-label {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .rhu-kpi-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            background: #eef4ff;
            color: var(--blue);
        }

        .rhu-kpi.alert .rhu-kpi-icon { background: var(--red-soft); color: var(--red); }
        .rhu-kpi.good .rhu-kpi-icon { background: var(--green-soft); color: var(--green); }

        .rhu-kpi-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .rhu-kpi-note {
            margin-top: 8px;
            color: var(--muted);
            font-size: .8rem;
        }

        .rhu-risk-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .rhu-risk-item {
            padding: 14px 16px;
        }

        .rhu-risk-label {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 600;
        }

        .rhu-risk-value {
            margin-top: 8px;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .rhu-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
            gap: 18px;
            margin-bottom: 18px;
        }

        .rhu-panel {
            padding: 18px;
        }

        .rhu-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 16px;
        }

        .rhu-panel-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .rhu-panel-subtitle {
            margin-top: 4px;
            color: var(--muted);
            font-size: .82rem;
        }

        .rhu-panel-badge {
            border-radius: 999px;
            background: #f2f4f7;
            color: var(--muted);
            font-size: .76rem;
            font-weight: 700;
            padding: 6px 10px;
            white-space: nowrap;
        }

        .rhu-chart {
            height: 300px;
        }

        .rhu-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 300px;
            overflow: auto;
        }

        .rhu-list-item {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fcfcfd;
        }

        .rhu-list-title {
            font-size: .9rem;
            font-weight: 700;
        }

        .rhu-list-meta {
            margin-top: 3px;
            color: var(--muted);
            font-size: .78rem;
            line-height: 1.45;
        }

        .rhu-list-count {
            min-width: 40px;
            height: 40px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: #f2f4f7;
            font-weight: 700;
        }

        .rhu-table-wrap {
            overflow-x: auto;
        }

        .rhu-table {
            width: 100%;
            min-width: 920px;
            border-collapse: collapse;
        }

        .rhu-table th {
            padding: 12px 10px;
            color: var(--muted);
            font-size: .74rem;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid var(--line);
        }

        .rhu-table td {
            padding: 13px 10px;
            font-size: .88rem;
            border-bottom: 1px solid #eef0f3;
            vertical-align: middle;
        }

        .rhu-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            background: var(--green-soft);
            color: var(--green);
            font-size: .74rem;
            font-weight: 700;
            padding: 5px 9px;
            white-space: nowrap;
        }

        .rhu-pill.moderate { background: var(--amber-soft); color: var(--amber); }
        .rhu-pill.high,
        .rhu-pill.critical { background: var(--red-soft); color: var(--red); }

        .status-pill {
            border-radius: 999px;
            background: #f2f4f7;
            color: var(--muted);
            font-size: .74rem;
            font-weight: 700;
            padding: 5px 9px;
            white-space: nowrap;
        }

        .row-actions {
            display: flex;
            gap: 8px;
        }

        .tiny-button {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            color: var(--navy);
            padding: 6px 9px;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
        }

        .rhu-empty {
            color: var(--muted);
            padding: 18px 4px;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .42);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 20;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-card {
            width: min(920px, 100%);
            max-height: calc(100vh - 36px);
            overflow: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .22);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
        }

        .modal-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .modal-body {
            padding: 18px 20px 20px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .detail-box {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px;
            background: #fcfcfd;
        }

        .detail-label {
            color: var(--muted);
            font-size: .74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .detail-value {
            margin-top: 6px;
            font-size: .95rem;
            font-weight: 600;
        }

        .section-title {
            margin: 18px 0 10px;
            font-size: .95rem;
            font-weight: 700;
        }

        .detail-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 16px;
            color: var(--muted);
            font-size: .86rem;
        }

        .referral-form {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
        }

        .referral-grid {
            display: grid;
            grid-template-columns: 220px 1fr auto;
            gap: 10px;
            align-items: end;
        }

        .referral-grid textarea {
            min-height: 40px;
            resize: vertical;
        }

        .toast {
            position: fixed;
            right: 18px;
            bottom: 18px;
            background: var(--ink);
            color: #fff;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: .86rem;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(8px);
            transition: .2s;
            z-index: 30;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .rhu-logout-form { display: none; }

        @media print {
            body { background: #fff; }
            .rhu-header, .rhu-filter-panel, .rhu-toolbar, .row-actions, .modal-backdrop, .rhu-logout-form { display: none !important; }
            .rhu-page { max-width: none; padding: 0; }
            .rhu-panel, .rhu-kpi, .rhu-risk-item { box-shadow: none; break-inside: avoid; }
            .rhu-grid, .rhu-kpis, .rhu-risk-summary { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 1100px) {
            .filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .rhu-kpis, .rhu-risk-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .rhu-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 720px) {
            .rhu-page { padding: 18px 14px 30px; }
            .rhu-header { grid-template-columns: auto 1fr; }
            .rhu-account { grid-column: 1 / -1; justify-content: space-between; }
            .rhu-account-text { text-align: left; }
            .filter-grid, .rhu-kpis, .rhu-risk-summary, .detail-grid, .detail-list, .referral-grid { grid-template-columns: 1fr; }
            .rhu-toolbar { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <main class="rhu-page">
        <header class="rhu-header">
            <div class="rhu-logo"><img src="<?php echo rhu_h(BASE_URL . 'uploads/BMISlogo.png'); ?>" alt="BMIS Logo"></div>
            <div class="rhu-title">
                <h1>RHU Analytics</h1>
                <p>Barangay Lumbangan Health Monitoring</p>
            </div>
            <div class="rhu-account">
                <div class="rhu-account-text">
                    <div class="rhu-account-name"><?php echo rhu_h($displayName); ?></div>
                    <div class="rhu-account-role"><?php echo rhu_h($role); ?></div>
                </div>
                <button class="icon-button" type="button" data-rhu-logout title="Logout" aria-label="Logout">
                    <i class="fas fa-right-from-bracket"></i>
                </button>
            </div>
        </header>

        <form class="rhu-panel rhu-filter-panel" method="get" action="<?php echo rhu_h($basePublic . '/index.php'); ?>">
            <input type="hidden" name="page" value="dashboard_rhu">
            <div class="filter-grid">
                <div class="field">
                    <label for="date_from">From</label>
                    <input id="date_from" type="date" name="date_from" value="<?php echo rhu_h($filters['date_from'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label for="date_to">To</label>
                    <input id="date_to" type="date" name="date_to" value="<?php echo rhu_h($filters['date_to'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label for="purok_id">Purok</label>
                    <select id="purok_id" name="purok_id">
                        <option value="">All</option>
                        <?php foreach ($puroks as $purok): ?>
                            <option value="<?php echo rhu_h($purok['id']); ?>" <?php echo rhu_selected($filters['purok_id'] ?? '', $purok['id']); ?>>
                                <?php echo rhu_h($purok['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="age_group">Age Group</label>
                    <select id="age_group" name="age_group">
                        <option value="">All</option>
                        <option value="0-17" <?php echo rhu_selected($filters['age_group'] ?? '', '0-17'); ?>>0-17</option>
                        <option value="18-59" <?php echo rhu_selected($filters['age_group'] ?? '', '18-59'); ?>>18-59</option>
                        <option value="60+" <?php echo rhu_selected($filters['age_group'] ?? '', '60+'); ?>>60+</option>
                    </select>
                </div>
                <div class="field">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex">
                        <option value="">All</option>
                        <option value="M" <?php echo rhu_selected($filters['sex'] ?? '', 'M'); ?>>Male</option>
                        <option value="F" <?php echo rhu_selected($filters['sex'] ?? '', 'F'); ?>>Female</option>
                    </select>
                </div>
                <div class="field">
                    <label for="risk_type">Risk Type</label>
                    <select id="risk_type" name="risk_type">
                        <option value="">All</option>
                        <option value="high_risk" <?php echo rhu_selected($filters['risk_type'] ?? '', 'high_risk'); ?>>Any Risk</option>
                        <option value="raised_bp" <?php echo rhu_selected($filters['risk_type'] ?? '', 'raised_bp'); ?>>Raised BP</option>
                        <option value="diabetes" <?php echo rhu_selected($filters['risk_type'] ?? '', 'diabetes'); ?>>Diabetes</option>
                        <option value="angina" <?php echo rhu_selected($filters['risk_type'] ?? '', 'angina'); ?>>Angina/Stroke</option>
                        <option value="obesity" <?php echo rhu_selected($filters['risk_type'] ?? '', 'obesity'); ?>>Obesity</option>
                        <option value="referral" <?php echo rhu_selected($filters['risk_type'] ?? '', 'referral'); ?>>Doctor Referral</option>
                    </select>
                </div>
                <div class="field">
                    <label for="risk_level">Risk Level</label>
                    <select id="risk_level" name="risk_level">
                        <option value="">All</option>
                        <?php foreach (['Low', 'Moderate', 'High', 'Critical'] as $level): ?>
                            <option value="<?php echo $level; ?>" <?php echo rhu_selected($filters['risk_level'] ?? '', $level); ?>><?php echo $level; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="privacy">Privacy</label>
                    <select id="privacy" name="privacy">
                        <option value="full" <?php echo rhu_selected($privacyMode, 'full'); ?>>Show names</option>
                        <option value="anonymized" <?php echo rhu_selected($privacyMode, 'anonymized'); ?>>Anonymized</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply</button>
                    <a class="btn btn-secondary" href="<?php echo rhu_h($basePublic . '/index.php?page=dashboard_rhu'); ?>">Reset</a>
                </div>
            </div>
        </form>

        <section class="rhu-toolbar">
            <div class="toolbar-left">
                <span class="rhu-chip">Approved Assessments</span>
                <span class="rhu-chip">CVD / NCD Screening</span>
                <span class="rhu-chip"><?php echo $privacyMode === 'anonymized' ? 'Anonymized Mode' : 'Names Visible'; ?></span>
                <span class="rhu-chip">Updated <?php echo rhu_h($lastUpdated); ?></span>
            </div>
            <div class="toolbar-right">
                <a class="btn btn-secondary" href="<?php echo rhu_h(rhu_query_url($basePublic, $filters, $privacyMode, 'rhu_export_csv')); ?>"><i class="fas fa-file-csv"></i> CSV</a>
                <a class="btn btn-secondary" href="<?php echo rhu_h(rhu_query_url($basePublic, $filters, $privacyMode, 'rhu_export_pdf')); ?>"><i class="fas fa-file-pdf"></i> PDF</a>
                <button class="btn btn-secondary" type="button" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            </div>
        </section>

        <section class="rhu-kpis" aria-label="RHU overview">
            <article class="rhu-kpi"><div class="rhu-kpi-top"><div class="rhu-kpi-label">Approved Surveys</div><div class="rhu-kpi-icon"><i class="fas fa-clipboard-check"></i></div></div><div class="rhu-kpi-value"><?php echo number_format($summary['approved_assessments']); ?></div><div class="rhu-kpi-note">Validated records</div></article>
            <article class="rhu-kpi"><div class="rhu-kpi-top"><div class="rhu-kpi-label">Residents Assessed</div><div class="rhu-kpi-icon"><i class="fas fa-users"></i></div></div><div class="rhu-kpi-value"><?php echo number_format($summary['residents_assessed']); ?></div><div class="rhu-kpi-note">Unique residents</div></article>
            <article class="rhu-kpi alert"><div class="rhu-kpi-top"><div class="rhu-kpi-label">Needs Monitoring</div><div class="rhu-kpi-icon"><i class="fas fa-triangle-exclamation"></i></div></div><div class="rhu-kpi-value"><?php echo number_format($summary['high_risk']); ?></div><div class="rhu-kpi-note">Moderate to critical</div></article>
            <article class="rhu-kpi good"><div class="rhu-kpi-top"><div class="rhu-kpi-label">Doctor Referrals</div><div class="rhu-kpi-icon"><i class="fas fa-user-doctor"></i></div></div><div class="rhu-kpi-value"><?php echo number_format($summary['doctor_referrals']); ?></div><div class="rhu-kpi-note">Marked for follow-up</div></article>
        </section>

        <section class="rhu-risk-summary" aria-label="Risk level summary">
            <div class="rhu-risk-item"><div class="rhu-risk-label">Low Risk</div><div class="rhu-risk-value"><?php echo number_format($summary['low_risk']); ?></div></div>
            <div class="rhu-risk-item"><div class="rhu-risk-label">Moderate Risk</div><div class="rhu-risk-value"><?php echo number_format($summary['moderate_risk']); ?></div></div>
            <div class="rhu-risk-item"><div class="rhu-risk-label">High Risk</div><div class="rhu-risk-value"><?php echo number_format($summary['high_level_risk']); ?></div></div>
            <div class="rhu-risk-item"><div class="rhu-risk-label">Critical Risk</div><div class="rhu-risk-value"><?php echo number_format($summary['critical_risk']); ?></div></div>
        </section>

        <section class="rhu-grid">
            <article class="rhu-panel">
                <div class="rhu-panel-header"><div><h2 class="rhu-panel-title">Monthly Trend</h2><div class="rhu-panel-subtitle">Approved surveys and monitoring needs</div></div><span class="rhu-panel-badge">Last 6 months</span></div>
                <div class="rhu-chart"><canvas id="monthlyTrendChart"></canvas></div>
            </article>
            <article class="rhu-panel">
                <div class="rhu-panel-header"><div><h2 class="rhu-panel-title">Risk by Purok</h2><div class="rhu-panel-subtitle">Highest monitoring counts</div></div></div>
                <div class="rhu-chart"><canvas id="purokRiskChart"></canvas></div>
            </article>
        </section>

        <section class="rhu-grid">
            <article class="rhu-panel">
                <div class="rhu-panel-header"><div><h2 class="rhu-panel-title">Residents Needing Immediate Follow-up</h2><div class="rhu-panel-subtitle">Prioritized by risk level and referral flags</div></div><span class="rhu-panel-badge"><?php echo number_format(count($priorityAssessments)); ?> shown</span></div>
                <div class="rhu-list">
                    <?php if (empty($priorityAssessments)): ?>
                        <div class="rhu-empty">No priority residents for the current filters.</div>
                    <?php else: ?>
                        <?php foreach ($priorityAssessments as $row): ?>
                            <div class="rhu-list-item">
                                <div>
                                    <div class="rhu-list-title"><?php echo rhu_h(rhu_name($row, $privacyMode)); ?></div>
                                    <div class="rhu-list-meta"><?php echo rhu_h($row['purok_name'] ?? 'Unassigned'); ?> | <?php echo rhu_h(($row['age'] ?? 'N/A') . ' / ' . ($row['sex'] ?? 'N/A')); ?> | <?php echo rhu_h($row['referral_status'] ?? 'Pending Review'); ?></div>
                                </div>
                                <span class="rhu-pill <?php echo rhu_risk_class($row['risk_level'] ?? 'Low'); ?>"><?php echo rhu_h($row['risk_level'] ?? 'Low'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>

            <article class="rhu-panel">
                <div class="rhu-panel-header"><div><h2 class="rhu-panel-title">Priority Puroks</h2><div class="rhu-panel-subtitle">Sorted by monitoring need</div></div></div>
                <div class="rhu-list">
                    <?php if (empty($purokBreakdown)): ?>
                        <div class="rhu-empty">No purok analytics available yet.</div>
                    <?php else: ?>
                        <?php foreach ($purokBreakdown as $row): ?>
                            <div class="rhu-list-item">
                                <div><div class="rhu-list-title"><?php echo rhu_h($row['purok_name'] ?? 'Unassigned'); ?></div><div class="rhu-list-meta"><?php echo number_format((int) ($row['total_assessments'] ?? 0)); ?> assessments | <?php echo number_format((int) ($row['diabetes_positive'] ?? 0)); ?> diabetes | <?php echo number_format((int) ($row['raised_bp'] ?? 0)); ?> raised BP</div></div>
                                <div class="rhu-list-count"><?php echo number_format((int) ($row['high_risk'] ?? 0)); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>
        </section>

        <section class="rhu-panel">
            <div class="rhu-panel-header"><div><h2 class="rhu-panel-title">Approved Assessments</h2><div class="rhu-panel-subtitle">Click view to inspect health details and update referral tracking</div></div><span class="rhu-panel-badge"><?php echo number_format(count($recentAssessments)); ?> shown</span></div>
            <div class="rhu-table-wrap">
                <table class="rhu-table">
                    <thead><tr><th>Resident</th><th>Purok</th><th>Age / Sex</th><th>BP</th><th>BMI</th><th>Risk</th><th>Referral</th><th>Approved</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($recentAssessments)): ?>
                            <tr><td colspan="9" class="rhu-empty">No approved assessments found for the current filters.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentAssessments as $assessment): ?>
                                <?php $approvedDate = $assessment['approved_at'] ?: $assessment['survey_date']; ?>
                                <tr id="assessment-row-<?php echo (int) $assessment['id']; ?>">
                                    <td><?php echo rhu_h(rhu_name($assessment, $privacyMode)); ?></td>
                                    <td><?php echo rhu_h($assessment['purok_name']); ?></td>
                                    <td><?php echo rhu_h(($assessment['age'] ?? 'N/A') . ' / ' . ($assessment['sex'] ?? 'N/A')); ?></td>
                                    <td><?php echo rhu_h(($assessment['bp_systolic'] ?: '-') . '/' . ($assessment['bp_diastolic'] ?: '-')); ?></td>
                                    <td><?php echo $assessment['bmi'] !== null ? rhu_h(number_format((float) $assessment['bmi'], 1)) : '-'; ?></td>
                                    <td><span class="rhu-pill <?php echo rhu_risk_class($assessment['risk_level'] ?? 'Low'); ?>"><?php echo rhu_h($assessment['risk_level'] ?? 'Low'); ?></span></td>
                                    <td><span class="status-pill" data-referral-status="<?php echo (int) $assessment['id']; ?>"><?php echo rhu_h($assessment['referral_status'] ?? 'Pending Review'); ?></span></td>
                                    <td><?php echo $approvedDate ? rhu_h(date('M d, Y', strtotime($approvedDate))) : '-'; ?></td>
                                    <td><div class="row-actions"><button class="tiny-button" type="button" data-view-assessment="<?php echo (int) $assessment['id']; ?>"><i class="fas fa-eye"></i> View</button></div></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal-backdrop" id="assessmentModal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="assessmentModalTitle">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="assessmentModalTitle">Assessment Details</h2>
                    <div class="rhu-panel-subtitle" id="assessmentModalSubtitle">Loading...</div>
                </div>
                <button class="icon-button" type="button" data-close-modal aria-label="Close"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div id="assessmentModalContent" class="rhu-empty">Loading details...</div>
            </div>
        </div>
    </div>

    <div class="toast" id="rhuToast"></div>

    <form id="rhuLogoutForm" class="rhu-logout-form" method="post" action="<?php echo rhu_h($basePublic . '/index.php?action=logout'); ?>">
        <?php echo csrf_input(); ?>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const basePublic = <?php echo json_encode($basePublic); ?>;
        const privacyMode = <?php echo json_encode($privacyMode); ?>;
        const referralStatuses = <?php echo json_encode($referralStatuses); ?>;
        const csrfField = <?php echo json_encode(csrf_field_name()); ?>;
        const csrfToken = <?php echo json_encode(csrf_token()); ?>;
        const trendLabels = <?php echo json_encode($trendLabels); ?>;
        const trendApproved = <?php echo json_encode($trendApproved); ?>;
        const trendHighRisk = <?php echo json_encode($trendHighRisk); ?>;
        const purokLabels = <?php echo json_encode($purokLabels); ?>;
        const purokHighRisk = <?php echo json_encode($purokHighRisk); ?>;

        const chartText = { family: 'Poppins', size: 12 };

        new Chart(document.getElementById('monthlyTrendChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    { label: 'Approved', data: trendApproved, borderColor: '#2c5282', backgroundColor: 'rgba(44, 82, 130, .08)', borderWidth: 2, tension: .32, fill: true, pointRadius: 3 },
                    { label: 'Needs Monitoring', data: trendHighRisk, borderColor: '#b42318', backgroundColor: 'rgba(180, 35, 24, .06)', borderWidth: 2, tension: .32, fill: true, pointRadius: 3 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: chartText, usePointStyle: true, boxWidth: 8 } } }, scales: { x: { grid: { display: false }, ticks: { font: chartText, color: '#667085' } }, y: { beginAtZero: true, ticks: { precision: 0, font: chartText, color: '#667085' }, grid: { color: '#eef0f3' } } } }
        });

        new Chart(document.getElementById('purokRiskChart'), {
            type: 'bar',
            data: { labels: purokLabels, datasets: [{ label: 'Needs Monitoring', data: purokHighRisk, backgroundColor: '#1e3a5f', borderRadius: 6, maxBarThickness: 38 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: chartText, color: '#667085' } }, y: { beginAtZero: true, ticks: { precision: 0, font: chartText, color: '#667085' }, grid: { color: '#eef0f3' } } } }
        });

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
        }

        function yesNo(value) {
            if (value === null || value === undefined || value === '') return '-';
            return Number(value) === 1 ? 'Yes' : 'No';
        }

        function displayResident(data) {
            return privacyMode === 'anonymized' ? `Resident #${Number(data.person_id || data.id || 0)}` : (data.resident_name || 'Resident');
        }

        function detailItem(label, value) {
            return `<div class="detail-box"><div class="detail-label">${escapeHtml(label)}</div><div class="detail-value">${escapeHtml(value ?? '-')}</div></div>`;
        }

        function detailPair(label, value) {
            return `<div><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value ?? '-')}</div>`;
        }

        function referralOptions(current) {
            return referralStatuses.map(status => `<option value="${escapeHtml(status)}" ${status === current ? 'selected' : ''}>${escapeHtml(status)}</option>`).join('');
        }

        function showToast(message) {
            const toast = document.getElementById('rhuToast');
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2200);
        }

        function openModal() {
            document.getElementById('assessmentModal').classList.add('open');
            document.getElementById('assessmentModal').setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            document.getElementById('assessmentModal').classList.remove('open');
            document.getElementById('assessmentModal').setAttribute('aria-hidden', 'true');
        }

        async function loadAssessment(id) {
            openModal();
            document.getElementById('assessmentModalSubtitle').textContent = 'Loading...';
            document.getElementById('assessmentModalContent').innerHTML = '<div class="rhu-empty">Loading details...</div>';

            const response = await fetch(`${basePublic}/index.php?action=rhu_assessment_detail&assessment_id=${encodeURIComponent(id)}`, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!payload.success) {
                document.getElementById('assessmentModalContent').innerHTML = `<div class="rhu-empty">${escapeHtml(payload.message || 'Unable to load details.')}</div>`;
                return;
            }

            const data = payload.data;
            const vitals = data.vitals || {};
            const diabetes = data.diabetes || {};
            const angina = data.angina || {};
            const lifestyle = data.lifestyle || {};
            const family = data.family_history || {};

            document.getElementById('assessmentModalTitle').textContent = displayResident(data);
            document.getElementById('assessmentModalSubtitle').textContent = `${data.purok_name || 'Unassigned'} | ${data.age || 'N/A'} / ${data.sex || 'N/A'} | ${data.risk_level || 'Low'} Risk`;

            document.getElementById('assessmentModalContent').innerHTML = `
                <div class="detail-grid">
                    ${detailItem('Risk Level', data.risk_level || 'Low')}
                    ${detailItem('Referral Status', data.referral_status || 'Pending Review')}
                    ${detailItem('Approved', data.approved_at ? new Date(data.approved_at).toLocaleDateString() : (data.survey_date || '-'))}
                    ${detailItem('Blood Pressure', `${data.bp_systolic || '-'}/${data.bp_diastolic || '-'}`)}
                    ${detailItem('BMI', data.bmi || '-')}
                    ${detailItem('Review Notes', data.review_notes || '-')}
                </div>

                <div class="section-title">Vitals</div>
                <div class="detail-list">
                    ${detailPair('Height', vitals.height_cm ? `${vitals.height_cm} cm` : '-')}
                    ${detailPair('Weight', vitals.weight_kg ? `${vitals.weight_kg} kg` : '-')}
                    ${detailPair('Waist', vitals.waist_circumference_cm ? `${vitals.waist_circumference_cm} cm` : '-')}
                    ${detailPair('Temperature', vitals.temperature_c ? `${vitals.temperature_c} C` : '-')}
                    ${detailPair('Pulse', vitals.pulse)}
                    ${detailPair('Respiratory Rate', vitals.respiratory_rate)}
                </div>

                <div class="section-title">Diabetes Screening</div>
                <div class="detail-list">
                    ${detailPair('Known Diabetes', yesNo(diabetes.known_diabetes))}
                    ${detailPair('On Medications', yesNo(diabetes.on_medications))}
                    ${detailPair('Family History', yesNo(diabetes.family_history))}
                    ${detailPair('Screen Positive', yesNo(diabetes.screen_positive))}
                    ${detailPair('RBS', diabetes.rbs_mg_dl)}
                    ${detailPair('FBS', diabetes.fbs_mg_dl)}
                    ${detailPair('HbA1c', diabetes.hba1c_percent)}
                </div>

                <div class="section-title">Angina / Stroke Screening</div>
                <div class="detail-list">
                    ${detailPair('Chest Discomfort', yesNo(angina.q1_chest_discomfort))}
                    ${detailPair('Pain Location', yesNo(angina.q2_pain_location_left_arm_neck_back))}
                    ${detailPair('Pain on Exertion', yesNo(angina.q3_pain_on_exertion))}
                    ${detailPair('Screen Positive', yesNo(angina.screen_positive))}
                    ${detailPair('Doctor Referral', yesNo(angina.needs_doctor_referral))}
                </div>

                <div class="section-title">Lifestyle Risks</div>
                <div class="detail-list">
                    ${detailPair('Smoking', lifestyle.smoking_status)}
                    ${detailPair('Alcohol Use', lifestyle.alcohol_use)}
                    ${detailPair('Excessive Alcohol', yesNo(lifestyle.excessive_alcohol))}
                    ${detailPair('Exercise Days/Week', lifestyle.exercise_days_per_week)}
                    ${detailPair('Exercise Minutes/Day', lifestyle.exercise_minutes_per_day)}
                    ${detailPair('Exercise Intensity', lifestyle.exercise_intensity)}
                </div>

                <div class="section-title">Family History</div>
                <div class="detail-list">
                    ${detailPair('Hypertension', yesNo(family.hypertension))}
                    ${detailPair('Stroke', yesNo(family.stroke))}
                    ${detailPair('Heart Attack', yesNo(family.heart_attack))}
                    ${detailPair('Diabetes', yesNo(family.diabetes))}
                    ${detailPair('Cancer', yesNo(family.cancer))}
                    ${detailPair('Kidney Disease', yesNo(family.kidney_disease))}
                </div>

                <form class="referral-form" id="referralForm">
                    <input type="hidden" name="${escapeHtml(csrfField)}" value="${escapeHtml(csrfToken)}">
                    <input type="hidden" name="assessment_id" value="${escapeHtml(data.id)}">
                    <div class="referral-grid">
                        <div class="field">
                            <label for="referralStatus">Referral Status</label>
                            <select id="referralStatus" name="status">${referralOptions(data.referral_status || 'Pending Review')}</select>
                        </div>
                        <div class="field">
                            <label for="referralNotes">Notes</label>
                            <textarea id="referralNotes" name="notes">${escapeHtml(data.referral_notes || '')}</textarea>
                        </div>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            `;

            document.getElementById('referralForm').addEventListener('submit', saveReferral);
        }

        async function saveReferral(event) {
            event.preventDefault();
            const form = event.currentTarget;
            const formData = new FormData(form);
            const response = await fetch(`${basePublic}/index.php?action=rhu_update_referral`, { method: 'POST', body: formData, headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!payload.success) {
                showToast(payload.message || 'Unable to save referral status.');
                return;
            }

            const id = formData.get('assessment_id');
            const status = formData.get('status');
            const badge = document.querySelector(`[data-referral-status="${CSS.escape(String(id))}"]`);
            if (badge) badge.textContent = status;
            showToast('Referral status updated.');
        }

        document.querySelectorAll('[data-view-assessment]').forEach(button => {
            button.addEventListener('click', () => loadAssessment(button.dataset.viewAssessment));
        });

        document.querySelector('[data-close-modal]').addEventListener('click', closeModal);
        document.getElementById('assessmentModal').addEventListener('click', event => {
            if (event.target.id === 'assessmentModal') closeModal();
        });

        document.querySelector('[data-rhu-logout]')?.addEventListener('click', function () {
            document.getElementById('rhuLogoutForm').submit();
        });
    </script>
</body>
</html>
