<?php

require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../models/RhuAnalytics.php';

class RhuAnalyticsController
{
    private $analytics;

    public function __construct($analytics = null)
    {
        $this->analytics = $analytics ?: new RhuAnalytics();
    }

    public function dashboard()
    {
        requireRhu();

        $filters = $this->filtersFromRequest($_GET);
        $privacyMode = ($_GET['privacy'] ?? '') === 'anonymized' ? 'anonymized' : 'full';

        $summary = $this->analytics->getSummary($filters);
        $purokBreakdown = $this->analytics->getPurokBreakdown($filters);
        $monthlyTrend = $this->analytics->getMonthlyTrend($filters);
        $recentAssessments = $this->analytics->getApprovedAssessments($filters, 12);
        $priorityAssessments = $this->analytics->getTopPriorityAssessments($filters, 6);
        $puroks = $this->analytics->getPuroks();
        $referralStatuses = $this->analytics->getReferralStatuses();

        require __DIR__ . '/../views/rhu/dashboard.php';
    }

    public function assessmentDetail()
    {
        requireRhu();
        header('Content-Type: application/json');

        $assessmentId = (int) ($_GET['assessment_id'] ?? 0);
        if ($assessmentId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Assessment ID is required.']);
            return;
        }

        $detail = $this->analytics->getAssessmentDetail($assessmentId);
        if (!$detail) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Assessment not found.']);
            return;
        }

        echo json_encode(['success' => true, 'data' => $detail]);
    }

    public function updateReferral()
    {
        requireRhu();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        csrf_require_valid_token();

        $assessmentId = (int) ($_POST['assessment_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($assessmentId <= 0 || $status === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Assessment and status are required.']);
            return;
        }

        try {
            $ok = $this->analytics->updateReferralStatus(
                $assessmentId,
                $status,
                $notes,
                $_SESSION['official_id'] ?? null
            );

            echo json_encode(['success' => (bool) $ok, 'message' => $ok ? 'Referral status updated.' : 'No changes saved.']);
        } catch (Throwable $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function exportCsv()
    {
        requireRhu();

        $filters = $this->filtersFromRequest($_GET);
        $privacyMode = ($_GET['privacy'] ?? '') === 'anonymized' ? 'anonymized' : 'full';
        $rows = $this->analytics->getApprovedAssessments($filters, null);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="rhu-approved-assessments-' . date('Ymd-His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Assessment ID', 'Resident', 'Purok', 'Age', 'Sex', 'BP', 'BMI', 'Risk Level', 'Referral Status', 'Approved Date']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                $this->displayName($row, $privacyMode),
                $row['purok_name'] ?? 'Unassigned',
                $row['age'] ?? '',
                $row['sex'] ?? '',
                (($row['bp_systolic'] ?? null) ?: '-') . '/' . (($row['bp_diastolic'] ?? null) ?: '-'),
                $row['bmi'] ?? '',
                $row['risk_level'] ?? 'Low',
                $row['referral_status'] ?? 'Pending Review',
                !empty($row['approved_at']) ? date('Y-m-d', strtotime($row['approved_at'])) : ($row['survey_date'] ?? ''),
            ]);
        }

        fclose($out);
    }

    public function exportPdf()
    {
        requireRhu();

        $filters = $this->filtersFromRequest($_GET);
        $privacyMode = ($_GET['privacy'] ?? '') === 'anonymized' ? 'anonymized' : 'full';
        $summary = $this->analytics->getSummary($filters);
        $purokBreakdown = $this->analytics->getPurokBreakdown($filters);
        $priorityAssessments = $this->analytics->getTopPriorityAssessments($filters, 12);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: DejaVu Sans, Arial, sans-serif; color: #1f2937; font-size: 12px; }
                h1 { font-size: 20px; margin: 0 0 4px; }
                h2 { font-size: 14px; margin: 18px 0 8px; }
                .muted { color: #667085; }
                .stats { width: 100%; border-collapse: collapse; margin-top: 14px; }
                .stats td { border: 1px solid #e5e7eb; padding: 8px; }
                table { width: 100%; border-collapse: collapse; margin-top: 8px; }
                th, td { border-bottom: 1px solid #e5e7eb; padding: 7px; text-align: left; }
                th { background: #f6f8fb; color: #667085; font-size: 10px; text-transform: uppercase; }
            </style>
        </head>
        <body>
            <h1>RHU Monthly Health Summary</h1>
            <div class="muted">Barangay Lumbangan | Generated <?php echo htmlspecialchars(date('M d, Y g:i A')); ?></div>

            <table class="stats">
                <tr>
                    <td><strong><?php echo number_format($summary['approved_assessments']); ?></strong><br>Approved Surveys</td>
                    <td><strong><?php echo number_format($summary['residents_assessed']); ?></strong><br>Residents Assessed</td>
                    <td><strong><?php echo number_format($summary['high_risk']); ?></strong><br>Needs Monitoring</td>
                    <td><strong><?php echo number_format($summary['doctor_referrals']); ?></strong><br>Doctor Referrals</td>
                </tr>
            </table>

            <h2>Priority Residents</h2>
            <table>
                <thead><tr><th>Resident</th><th>Purok</th><th>Age/Sex</th><th>Risk</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($priorityAssessments as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($this->displayName($row, $privacyMode)); ?></td>
                            <td><?php echo htmlspecialchars($row['purok_name'] ?? 'Unassigned'); ?></td>
                            <td><?php echo htmlspecialchars(($row['age'] ?? 'N/A') . ' / ' . ($row['sex'] ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars($row['risk_level'] ?? 'Low'); ?></td>
                            <td><?php echo htmlspecialchars($row['referral_status'] ?? 'Pending Review'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Monthly Report by Purok</h2>
            <table>
                <thead><tr><th>Purok</th><th>Assessments</th><th>Needs Monitoring</th><th>Diabetes</th><th>Raised BP</th></tr></thead>
                <tbody>
                    <?php foreach ($purokBreakdown as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['purok_name'] ?? 'Unassigned'); ?></td>
                            <td><?php echo number_format((int) ($row['total_assessments'] ?? 0)); ?></td>
                            <td><?php echo number_format((int) ($row['high_risk'] ?? 0)); ?></td>
                            <td><?php echo number_format((int) ($row['diabetes_positive'] ?? 0)); ?></td>
                            <td><?php echo number_format((int) ($row['raised_bp'] ?? 0)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        if (!class_exists('\Dompdf\Dompdf')) {
            require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
        }

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('rhu-health-summary-' . date('Ymd-His') . '.pdf', ['Attachment' => true]);
    }

    private function filtersFromRequest(array $source)
    {
        return [
            'date_from' => $this->validDate($source['date_from'] ?? '') ? $source['date_from'] : '',
            'date_to' => $this->validDate($source['date_to'] ?? '') ? $source['date_to'] : '',
            'purok_id' => trim((string) ($source['purok_id'] ?? '')),
            'age_group' => trim((string) ($source['age_group'] ?? '')),
            'sex' => trim((string) ($source['sex'] ?? '')),
            'risk_type' => trim((string) ($source['risk_type'] ?? '')),
            'risk_level' => trim((string) ($source['risk_level'] ?? '')),
        ];
    }

    private function validDate($value)
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    private function displayName(array $row, $privacyMode)
    {
        if ($privacyMode === 'anonymized') {
            return 'Resident #' . (int) ($row['person_id'] ?? $row['id'] ?? 0);
        }

        return trim((string) ($row['resident_name'] ?? 'Resident'));
    }
}
