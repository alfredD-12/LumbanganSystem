<?php
/**
 * Dashboard Statistics Helper
 * Fetches real-time statistics from the database for the admin dashboard
 */

require_once __DIR__ . '/../config/Database.php';

class DashboardStatsHelper {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Get total registered residents count
     */
    public function getTotalResidents() {
        try {
            $query = "SELECT COUNT(DISTINCT u.id) as total 
                      FROM users u 
                      INNER JOIN persons p ON u.person_id = p.id
                      WHERE u.status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting total residents: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get residents registered this month
     */
    public function getResidentsThisMonth() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM users 
                      WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                      AND YEAR(created_at) = YEAR(CURRENT_DATE())
                      AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting residents this month: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get pending complaints count
     */
    public function getPendingComplaints() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM incidents 
                      WHERE status_id = 1"; // 1 = Pending status
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting pending complaints: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get complaints from last week
     */
    public function getComplaintsLastWeek() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM incidents 
                      WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK)
                      AND created_at < CURRENT_DATE()";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting complaints last week: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get resolved complaints count
     */
    public function getResolvedComplaints() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM incidents 
                      WHERE status_id = 3"; // 3 = Resolved status
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting resolved complaints: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get complaints resolved this week
     */
    public function getResolvedThisWeek() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM incidents 
                      WHERE status_id = 3 
                      AND resolved_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK)";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting resolved this week: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total document requests count
     */
    public function getTotalDocumentRequests() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM document_requests 
                      WHERE status IN ('Pending', 'Approved')";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting document requests: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get new document requests today
     */
    public function getDocumentRequestsToday() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM document_requests 
                      WHERE DATE(request_date) = CURRENT_DATE()";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting document requests today: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get recent complaints for the activity timeline
     */
    public function getRecentComplaints($limit = 6) {
        try {
            $query = "SELECT 
                        i.id,
                        i.incident_title,
                        i.created_at,
                        s.label as status_label
                      FROM incidents i
                      LEFT JOIN statuses s ON i.status_id = s.id
                      ORDER BY i.created_at DESC
                      LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error getting recent complaints: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending document approvals by document type
     */
    public function getPendingApprovalsByType() {
        try {
            $query = "SELECT 
                        dt.document_name as name,
                        COUNT(*) as count
                      FROM document_requests dr
                      INNER JOIN document_types dt ON dr.document_type_id = dt.document_type_id
                      WHERE dr.status = 'Pending'
                      GROUP BY dt.document_name
                      ORDER BY count DESC
                      LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error getting pending approvals: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get released documents today by type
     */
    public function getReleasedToday() {
        try {
            $query = "SELECT 
                        dt.document_name as label,
                        COUNT(*) as num
                      FROM document_requests dr
                      INNER JOIN document_types dt ON dr.document_type_id = dt.document_type_id
                      WHERE dr.status = 'Released'
                      AND DATE(dr.release_date) = CURRENT_DATE()
                      GROUP BY dt.document_name
                      ORDER BY num DESC
                      LIMIT 3";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format for display
            return array_map(function($row) {
                return [
                    'num' => (string)$row['num'],
                    'label' => $this->shortenDocumentName($row['label'])
                ];
            }, $results);
        } catch (Exception $e) {
            error_log('Error getting released today: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get document request queue by status
     */
    public function getDocumentQueue() {
        try {
            $query = "SELECT 
                        dt.document_name as label,
                        COUNT(*) as count
                      FROM document_requests dr
                      INNER JOIN document_types dt ON dr.document_type_id = dt.document_type_id
                      WHERE dr.status IN ('Pending', 'Approved')
                      GROUP BY dt.document_name
                      ORDER BY count DESC
                      LIMIT 3";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate max count for width percentage
            $maxCount = !empty($results) ? max(array_column($results, 'count')) : 1;
            
            return array_map(function($row) use ($maxCount) {
                $percentage = $maxCount > 0 ? round(($row['count'] / $maxCount) * 100) : 0;
                return [
                    'label' => $this->shortenDocumentName($row['label']),
                    'count' => $row['count'],
                    'width' => $percentage . '%'
                ];
            }, $results);
        } catch (Exception $e) {
            error_log('Error getting document queue: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly document processing progress
     */
    public function getMonthlyProgress() {
        try {
            // Get completed documents this month
            $query = "SELECT COUNT(*) as current
                      FROM document_requests
                      WHERE status = 'Released'
                      AND MONTH(release_date) = MONTH(CURRENT_DATE())
                      AND YEAR(release_date) = YEAR(CURRENT_DATE())";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $current = $result['current'] ?? 0;
            
            // Set a monthly target (you can adjust this or make it configurable)
            $target = 200;
            $percentage = $target > 0 ? round(($current / $target) * 100) : 0;
            $remaining = max(0, $target - $current);
            
            return [
                'current' => $current,
                'target' => $target,
                'percentage' => min(100, $percentage), // Cap at 100%
                'remaining' => $remaining
            ];
        } catch (Exception $e) {
            error_log('Error getting monthly progress: ' . $e->getMessage());
            return [
                'current' => 0,
                'target' => 200,
                'percentage' => 0,
                'remaining' => 200
            ];
        }
    }

    /**
     * Helper function to shorten document names for display
     */
    private function shortenDocumentName($name) {
        $shortNames = [
            'Barangay Clearance' => 'Clearances',
            'Certificate of Indigency' => 'Indigency',
            'Certificate of Residency' => 'Residency',
            'Barangay ID' => 'Brgy ID',
            'Business Permit' => 'Business'
        ];
        
        return $shortNames[$name] ?? $name;
    }

    /**
     * Format time difference for "time ago" display
     */
    public function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;
        
        if ($difference < 60) {
            return $difference . ' seconds ago';
        } elseif ($difference < 3600) {
            $minutes = floor($difference / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($difference < 86400) {
            $hours = floor($difference / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($difference < 604800) {
            $days = floor($difference / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M d, Y', $timestamp);
        }
    }

    /**
     * Get badge class based on status
     */
    public function getStatusBadgeClass($status) {
        $badgeMap = [
            'Pending' => 'badge-pending',
            'Under Investigation' => 'badge-pending',
            'Resolved' => 'badge-completed',
            'Closed' => 'badge-completed'
        ];
        
        return $badgeMap[$status] ?? 'badge-new';
    }

    /**
     * Get registration statistics for last 7 days
     */
    public function getRegistrationsByDay($days = 7) {
        try {
            $query = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as count,
                        DAYNAME(created_at) as day_name
                      FROM users
                      WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL :days DAY)
                      AND status = 'active'
                      GROUP BY DATE(created_at)
                      ORDER BY DATE(created_at) ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fill in missing days with zero
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $dayName = date('D', strtotime($date));
                $count = 0;
                
                foreach ($results as $row) {
                    if ($row['date'] === $date) {
                        $count = $row['count'];
                        break;
                    }
                }
                
                $data[] = [
                    'date' => $date,
                    'day' => $dayName,
                    'count' => $count
                ];
            }
            
            return $data;
        } catch (Exception $e) {
            error_log('Error getting registrations by day: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get document statistics for the mini chart
     */
    public function getDocumentStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'Released' THEN 1 ELSE 0 END) as released
                      FROM document_requests";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error getting document stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'released' => 0
            ];
        }
    }

    /**
     * Get barangay officials statistics
     */
    public function getOfficialsStats() {
        try {
            // Get total active officials
            $query = "SELECT COUNT(*) as total FROM officials WHERE active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $result['total'] ?? 0;

            // Get Barangay Captain (Punong Barangay)
            $query = "SELECT full_name, role FROM officials 
                      WHERE active = 1 
                      AND role LIKE '%Captain%' 
                      LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $captain = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get Kagawad count (Council members)
            $query = "SELECT COUNT(*) as count FROM officials 
                      WHERE active = 1 
                      AND (role LIKE '%Kagawad%' OR role LIKE '%Councilor%')";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $kagawad = $result['count'] ?? 0;

            // Get SK and Staff count (everyone else except Captain and Kagawad)
            $query = "SELECT COUNT(*) as count FROM officials 
                      WHERE active = 1 
                      AND role NOT LIKE '%Captain%'
                      AND role NOT LIKE '%Kagawad%' 
                      AND role NOT LIKE '%Councilor%'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $skStaff = $result['count'] ?? 0;

            return [
                'total' => $total,
                'captain_name' => $captain['full_name'] ?? 'Punong Barangay',
                'captain_title' => $captain['role'] ?? 'Barangay Captain',
                'kagawad' => $kagawad,
                'sk_staff' => $skStaff
            ];
        } catch (Exception $e) {
            error_log('Error getting officials stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'captain_name' => 'Punong Barangay',
                'captain_title' => 'Barangay Captain',
                'kagawad' => 0,
                'sk_staff' => 0
            ];
        }
    }
}
