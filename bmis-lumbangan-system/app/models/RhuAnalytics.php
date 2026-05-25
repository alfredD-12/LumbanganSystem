<?php

require_once __DIR__ . '/../config/Database.php';

class RhuAnalytics
{
    private $db;

    private const REFERRAL_STATUSES = [
        'Pending Review',
        'Referred',
        'Seen by RHU',
        'Follow-up Needed',
        'Completed',
    ];

    public function __construct($db = null)
    {
        $this->db = $db ?: (new Database())->getConnection();
        $this->ensureReferralTable();
    }

    public function getReferralStatuses()
    {
        return self::REFERRAL_STATUSES;
    }

    public function getPuroks()
    {
        try {
            $stmt = $this->db->query('SELECT id, name FROM puroks ORDER BY name ASC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getSummary(array $filters = [])
    {
        [$where, $params] = $this->buildFilterSql($filters, false);
        $riskScore = $this->riskScoreSql();

        $sql = "
            SELECT
                COUNT(*) AS approved_assessments,
                COUNT(DISTINCT a.person_id) AS residents_assessed,
                COALESCE(ROUND(AVG(v.bmi), 1), 0) AS avg_bmi,
                SUM(CASE WHEN d.screen_positive = 1 THEN 1 ELSE 0 END) AS diabetes_positive,
                SUM(CASE WHEN ang.screen_positive = 1 THEN 1 ELSE 0 END) AS angina_positive,
                SUM(CASE WHEN ang.needs_doctor_referral = 1 THEN 1 ELSE 0 END) AS doctor_referrals,
                SUM(CASE WHEN {$this->raisedBpSql()} THEN 1 ELSE 0 END) AS raised_bp,
                SUM(CASE WHEN ({$riskScore}) >= 1 THEN 1 ELSE 0 END) AS high_risk,
                SUM(CASE WHEN ({$riskScore}) = 0 THEN 1 ELSE 0 END) AS low_risk,
                SUM(CASE WHEN ({$riskScore}) BETWEEN 1 AND 2 THEN 1 ELSE 0 END) AS moderate_risk,
                SUM(CASE WHEN ({$riskScore}) BETWEEN 3 AND 4 THEN 1 ELSE 0 END) AS high_level_risk,
                SUM(CASE WHEN ({$riskScore}) >= 5 THEN 1 ELSE 0 END) AS critical_risk
            FROM cvd_ncd_risk_assessments a
            JOIN persons p ON p.id = a.person_id
            LEFT JOIN households h ON h.id = p.household_id
            LEFT JOIN vitals v ON v.cvd_id = a.id
            LEFT JOIN diabetes_screening d ON d.cvd_id = a.id
            LEFT JOIN angina_stroke_screening ang ON ang.cvd_id = a.id
            LEFT JOIN lifestyle_risk lr ON lr.cvd_id = a.id
            LEFT JOIN rhu_referrals rr ON rr.assessment_id = a.id
            WHERE {$where}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'approved_assessments' => (int) ($row['approved_assessments'] ?? 0),
            'residents_assessed' => (int) ($row['residents_assessed'] ?? 0),
            'avg_bmi' => (float) ($row['avg_bmi'] ?? 0),
            'diabetes_positive' => (int) ($row['diabetes_positive'] ?? 0),
            'angina_positive' => (int) ($row['angina_positive'] ?? 0),
            'doctor_referrals' => (int) ($row['doctor_referrals'] ?? 0),
            'raised_bp' => (int) ($row['raised_bp'] ?? 0),
            'high_risk' => (int) ($row['high_risk'] ?? 0),
            'low_risk' => (int) ($row['low_risk'] ?? 0),
            'moderate_risk' => (int) ($row['moderate_risk'] ?? 0),
            'high_level_risk' => (int) ($row['high_level_risk'] ?? 0),
            'critical_risk' => (int) ($row['critical_risk'] ?? 0),
        ];
    }

    public function getPurokBreakdown(array $filters = [])
    {
        [$where, $params] = $this->buildFilterSql($filters, false);
        $riskScore = $this->riskScoreSql();

        $sql = "
            SELECT
                COALESCE(pk.name, CONCAT('Purok ', h.purok_id), 'Unassigned') AS purok_name,
                COUNT(*) AS total_assessments,
                SUM(CASE WHEN ({$riskScore}) >= 1 THEN 1 ELSE 0 END) AS high_risk,
                SUM(CASE WHEN d.screen_positive = 1 THEN 1 ELSE 0 END) AS diabetes_positive,
                SUM(CASE WHEN {$this->raisedBpSql()} THEN 1 ELSE 0 END) AS raised_bp
            FROM cvd_ncd_risk_assessments a
            JOIN persons p ON p.id = a.person_id
            LEFT JOIN households h ON h.id = p.household_id
            LEFT JOIN puroks pk ON pk.id = h.purok_id
            LEFT JOIN vitals v ON v.cvd_id = a.id
            LEFT JOIN diabetes_screening d ON d.cvd_id = a.id
            LEFT JOIN angina_stroke_screening ang ON ang.cvd_id = a.id
            LEFT JOIN lifestyle_risk lr ON lr.cvd_id = a.id
            LEFT JOIN rhu_referrals rr ON rr.assessment_id = a.id
            WHERE {$where}
            GROUP BY purok_name
            ORDER BY high_risk DESC, total_assessments DESC, purok_name ASC
            LIMIT 10
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyTrend(array $filters = [])
    {
        [$where, $params] = $this->buildFilterSql($filters, false);
        $riskScore = $this->riskScoreSql();
        $trendMonths = isset($filters['trend_months']) && in_array((string) $filters['trend_months'], ['3', '6', '12', '24'], true)
            ? (int) $filters['trend_months']
            : 6;

        $sql = "
            SELECT
                DATE_FORMAT(COALESCE(a.survey_date, DATE(a.answered_at)), '%Y-%m') AS month_key,
                COUNT(*) AS approved_count,
                SUM(CASE WHEN ({$riskScore}) >= 1 THEN 1 ELSE 0 END) AS high_risk
            FROM cvd_ncd_risk_assessments a
            JOIN persons p ON p.id = a.person_id
            LEFT JOIN households h ON h.id = p.household_id
            LEFT JOIN vitals v ON v.cvd_id = a.id
            LEFT JOIN diabetes_screening d ON d.cvd_id = a.id
            LEFT JOIN angina_stroke_screening ang ON ang.cvd_id = a.id
            LEFT JOIN lifestyle_risk lr ON lr.cvd_id = a.id
            LEFT JOIN rhu_referrals rr ON rr.assessment_id = a.id
            WHERE {$where}
            GROUP BY month_key
            ORDER BY month_key DESC
            LIMIT {$trendMonths}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getApprovedAssessments(array $filters = [], $limit = 8)
    {
        [$where, $params] = $this->buildFilterSql($filters, true);
        $limitSql = $limit ? 'LIMIT :limit' : '';

        $sql = "
            SELECT *
            FROM ({$this->assessmentListSql($where)}) rhu_rows
            WHERE 1=1
            ORDER BY sort_date DESC
            {$limitSql}
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if ($limit) {
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $this->hydrateAssessmentRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getTopPriorityAssessments(array $filters = [], $limit = 5)
    {
        [$where, $params] = $this->buildFilterSql($filters, true);

        $sql = "
            SELECT *
            FROM ({$this->assessmentListSql($where)}) rhu_rows
            WHERE risk_score >= 1
            ORDER BY risk_score DESC, sort_date DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $this->hydrateAssessmentRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getAssessmentDetail($assessmentId)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ({$this->assessmentListSql('a.is_approved = 1 AND a.id = :assessment_id')}) rhu_rows
            LIMIT 1
        ");
        $stmt->execute([':assessment_id' => (int) $assessmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $rows = $this->hydrateAssessmentRows([$row]);
        $detail = $rows[0];

        $detail['vitals'] = $this->fetchOne('SELECT * FROM vitals WHERE cvd_id = :id LIMIT 1', $assessmentId);
        $detail['diabetes'] = $this->fetchOne('SELECT * FROM diabetes_screening WHERE cvd_id = :id LIMIT 1', $assessmentId);
        $detail['angina'] = $this->fetchOne('SELECT * FROM angina_stroke_screening WHERE cvd_id = :id LIMIT 1', $assessmentId);
        $detail['lifestyle'] = $this->fetchOne('SELECT * FROM lifestyle_risk WHERE cvd_id = :id LIMIT 1', $assessmentId);
        $detail['family_history'] = $this->fetchOne(
            'SELECT hfh.* FROM health_family_history hfh JOIN cvd_ncd_risk_assessments a ON a.person_id = hfh.person_id WHERE a.id = :id ORDER BY hfh.recorded_at DESC LIMIT 1',
            $assessmentId
        );

        return $detail;
    }

    public function updateReferralStatus($assessmentId, $status, $notes, $officialId = null)
    {
        if (!in_array($status, self::REFERRAL_STATUSES, true)) {
            throw new InvalidArgumentException('Invalid referral status.');
        }

        $stmt = $this->db->prepare("
            INSERT INTO rhu_referrals (assessment_id, status, notes, updated_by_official_id, created_at, updated_at)
            VALUES (:assessment_id, :status, :notes, :official_id, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                notes = VALUES(notes),
                updated_by_official_id = VALUES(updated_by_official_id),
                updated_at = NOW()
        ");

        return $stmt->execute([
            ':assessment_id' => (int) $assessmentId,
            ':status' => $status,
            ':notes' => trim((string) $notes) ?: null,
            ':official_id' => $officialId ? (int) $officialId : null,
        ]);
    }

    private function assessmentListSql($where)
    {
        $riskScore = $this->riskScoreSql();

        return "
            SELECT
                a.id,
                a.person_id,
                a.survey_date,
                a.approved_at,
                a.review_notes,
                COALESCE(a.approved_at, a.answered_at, a.survey_date) AS sort_date,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name, p.suffix) AS resident_name,
                p.sex,
                TIMESTAMPDIFF(YEAR, p.birthdate, COALESCE(a.survey_date, CURDATE())) AS age,
                h.purok_id,
                COALESCE(pk.name, CONCAT('Purok ', h.purok_id), 'Unassigned') AS purok_name,
                h.address,
                v.bmi,
                v.bp_systolic,
                v.bp_diastolic,
                v.weight_kg,
                v.height_cm,
                COALESCE(d.screen_positive, 0) AS diabetes_positive,
                COALESCE(ang.screen_positive, 0) AS angina_positive,
                COALESCE(ang.needs_doctor_referral, 0) AS needs_doctor_referral,
                COALESCE(rr.status, 'Pending Review') AS referral_status,
                rr.notes AS referral_notes,
                rr.updated_at AS referral_updated_at,
                ({$riskScore}) AS risk_score
            FROM cvd_ncd_risk_assessments a
            JOIN persons p ON p.id = a.person_id
            LEFT JOIN households h ON h.id = p.household_id
            LEFT JOIN puroks pk ON pk.id = h.purok_id
            LEFT JOIN vitals v ON v.cvd_id = a.id
            LEFT JOIN diabetes_screening d ON d.cvd_id = a.id
            LEFT JOIN angina_stroke_screening ang ON ang.cvd_id = a.id
            LEFT JOIN lifestyle_risk lr ON lr.cvd_id = a.id
            LEFT JOIN rhu_referrals rr ON rr.assessment_id = a.id
            WHERE {$where}
        ";
    }

    private function hydrateAssessmentRows(array $rows)
    {
        foreach ($rows as &$row) {
            $row['risk_score'] = (int) ($row['risk_score'] ?? 0);
            $row['risk_level'] = $this->riskLevelFromScore($row['risk_score']);
        }

        return $rows;
    }

    private function buildFilterSql(array $filters, $allowRiskLevelFilter)
    {
        $where = ['a.is_approved = 1'];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = 'COALESCE(a.survey_date, DATE(a.answered_at)) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'COALESCE(a.survey_date, DATE(a.answered_at)) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['purok_id']) && ctype_digit((string) $filters['purok_id'])) {
            $where[] = 'h.purok_id = :purok_id';
            $params[':purok_id'] = (int) $filters['purok_id'];
        }

        if (!empty($filters['sex']) && in_array($filters['sex'], ['M', 'F'], true)) {
            $where[] = 'p.sex = :sex';
            $params[':sex'] = $filters['sex'];
        }

        $ageExpression = 'TIMESTAMPDIFF(YEAR, p.birthdate, COALESCE(a.survey_date, CURDATE()))';
        if (!empty($filters['age_group'])) {
            if ($filters['age_group'] === '0-17') {
                $where[] = "{$ageExpression} BETWEEN 0 AND 17";
            } elseif ($filters['age_group'] === '18-59') {
                $where[] = "{$ageExpression} BETWEEN 18 AND 59";
            } elseif ($filters['age_group'] === '60+') {
                $where[] = "{$ageExpression} >= 60";
            }
        }

        if (!empty($filters['risk_type'])) {
            $riskScore = $this->riskScoreSql();
            $riskFilters = [
                'raised_bp' => $this->raisedBpSql(),
                'diabetes' => 'd.screen_positive = 1',
                'angina' => 'ang.screen_positive = 1',
                'obesity' => 'COALESCE(v.bmi, 0) >= 30',
                'referral' => 'ang.needs_doctor_referral = 1',
                'high_risk' => "({$riskScore}) >= 1",
            ];

            if (isset($riskFilters[$filters['risk_type']])) {
                $where[] = $riskFilters[$filters['risk_type']];
            }
        }

        if ($allowRiskLevelFilter && !empty($filters['risk_level'])) {
            $riskScore = $this->riskScoreSql();
            if ($filters['risk_level'] === 'Low') {
                $where[] = "({$riskScore}) = 0";
            } elseif ($filters['risk_level'] === 'Moderate') {
                $where[] = "({$riskScore}) BETWEEN 1 AND 2";
            } elseif ($filters['risk_level'] === 'High') {
                $where[] = "({$riskScore}) BETWEEN 3 AND 4";
            } elseif ($filters['risk_level'] === 'Critical') {
                $where[] = "({$riskScore}) >= 5";
            }
        }

        return [implode(' AND ', $where), $params];
    }

    private function riskScoreSql()
    {
        return "(
            CASE WHEN d.screen_positive = 1 THEN 1 ELSE 0 END
            + CASE WHEN ang.screen_positive = 1 THEN 1 ELSE 0 END
            + CASE WHEN ang.needs_doctor_referral = 1 THEN 2 ELSE 0 END
            + CASE WHEN {$this->raisedBpSql()} THEN 1 ELSE 0 END
            + CASE WHEN COALESCE(v.bmi, 0) >= 30 THEN 1 ELSE 0 END
            + CASE WHEN lr.smoking_status IN ('Current', 'Passive') THEN 1 ELSE 0 END
            + CASE WHEN COALESCE(lr.excessive_alcohol, 0) = 1 THEN 1 ELSE 0 END
        )";
    }

    private function raisedBpSql()
    {
        return "(COALESCE(v.raised_bp, 0) = 1 OR COALESCE(v.bp_systolic, 0) >= 140 OR COALESCE(v.bp_diastolic, 0) >= 90)";
    }

    private function riskLevelFromScore($score)
    {
        if ($score >= 5) {
            return 'Critical';
        }
        if ($score >= 3) {
            return 'High';
        }
        if ($score >= 1) {
            return 'Moderate';
        }

        return 'Low';
    }

    private function fetchOne($sql, $assessmentId)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int) $assessmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function ensureReferralTable()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rhu_referrals (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                assessment_id BIGINT UNSIGNED NOT NULL,
                status VARCHAR(64) NOT NULL DEFAULT 'Pending Review',
                notes TEXT DEFAULT NULL,
                updated_by_official_id BIGINT UNSIGNED DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_rhu_referrals_assessment (assessment_id),
                KEY idx_rhu_referrals_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }
}
