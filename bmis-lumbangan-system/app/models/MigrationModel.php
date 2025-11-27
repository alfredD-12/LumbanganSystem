<?php

class MigrationModel
{
    private $db;

    public function __construct()
    {
        require_once dirname(__DIR__) . '/config/Database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Compute features for residents for next month prediction
     * Uses synthetic data only (is_synthetic = 1)
     * 
     * @param bool $syntheticOnly - Filter by synthetic data
     * @return array - Array of resident features
     */
    public function computeFeaturesForNextMonth($syntheticOnly = true)
    {
        $syntheticFilter = $syntheticOnly ? "AND rm.is_synthetic = 1" : "";

        $query = "
            SELECT DISTINCT
                p.id AS person_id,
                COALESCE(TIMESTAMPDIFF(YEAR, p.birthdate, CURDATE()), 25) AS age,
                p.sex,
                COALESCE(hh_count.total_members, 1) AS household_size,
                COALESCE(rm.to_purok_id, 1) AS to_purok_id,
                COALESCE(rm.from_purok_id, 1) AS from_purok_id
            FROM persons p
            LEFT JOIN (
                SELECT household_id, COUNT(*) AS total_members 
                FROM persons 
                GROUP BY household_id
            ) hh_count ON hh_count.household_id = p.household_id
            LEFT JOIN resident_migrations rm ON rm.person_id = p.id
            WHERE p.id IS NOT NULL
            {$syntheticFilter}
            GROUP BY p.id
            LIMIT 100
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process and normalize data
            $features = [];
            foreach ($results as $row) {
                $features[] = [
                    'person_id' => (int)$row['person_id'],
                    'age' => floatval($row['age'] ?? 25),
                    'household_size' => intval($row['household_size'] ?? 1),
                    'sex' => strtoupper(substr($row['sex'] ?? 'M', 0, 1)),
                    'to_purok_id' => intval($row['to_purok_id'] ?? 1),
                    'from_purok_id' => intval($row['from_purok_id'] ?? 1)
                ];
            }

            return $features;
        } catch (PDOException $e) {
            error_log("Error computing features: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Store prediction result in database
     * 
     * @param int $person_id
     * @param string $timeframe - 'day', 'month', 'year'
     * @param int $prediction - 0 or 1
     * @param array $probabilities - [prob_class_0, prob_class_1]
     * @param string $model_version
     * @return bool
     */
    public function storePrediction($person_id, $timeframe, $prediction, $probabilities, $model_version = 'nb_v1.0')
    {
        $query = "
            INSERT INTO migration_predictions 
            (person_id, timeframe, prediction, probability, model_version, created_at)
            VALUES (:person_id, :timeframe, :prediction, :probability, :model_version, NOW())
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
            $stmt->bindParam(':timeframe', $timeframe, PDO::PARAM_STR);
            $stmt->bindParam(':prediction', $prediction, PDO::PARAM_INT);
            $stmt->bindParam(':probability', json_encode($probabilities), PDO::PARAM_STR);
            $stmt->bindParam(':model_version', $model_version, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error storing prediction: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all predictions with resident details
     * 
     * @param int $limit
     * @return array
     */
    public function getAllPredictions($limit = 1000)
    {
        $query = "
            SELECT 
                mp.*,
                p.firstname,
                p.lastname,
                p.sex,
                TIMESTAMPDIFF(YEAR, p.birthdate, CURDATE()) AS age
            FROM migration_predictions mp
            LEFT JOIN persons p ON p.id = mp.person_id
            ORDER BY mp.created_at DESC
            LIMIT :limit
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching predictions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get predictions aggregated by month
     * 
     * @return array
     */
    public function getPredictionsByMonth()
    {
        $query = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS month,
                SUM(CASE WHEN prediction = 1 THEN 1 ELSE 0 END) AS predicted_moveouts,
                SUM(CASE WHEN prediction = 0 THEN 1 ELSE 0 END) AS predicted_stayins,
                COUNT(*) AS total_predictions
            FROM migration_predictions
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching monthly predictions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top puroks by predicted migration
     * 
     * @return array
     */
    public function getTopPuroksByPredictedMigration()
    {
        $query = "
            SELECT 
                rm.to_purok_id AS purok_id,
                COUNT(DISTINCT mp.person_id) AS predicted_migrations,
                AVG(JSON_EXTRACT(mp.probability, '$[1]')) AS avg_migration_probability
            FROM migration_predictions mp
            LEFT JOIN resident_migrations rm ON rm.person_id = mp.person_id
            WHERE mp.prediction = 1 AND rm.to_purok_id IS NOT NULL
            GROUP BY rm.to_purok_id
            ORDER BY predicted_migrations DESC
            LIMIT 5
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching top puroks: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get probability distribution for histogram
     * 
     * @return array
     */
    public function getProbabilityDistribution()
    {
        $query = "
            SELECT 
                JSON_EXTRACT(probability, '$[1]') AS migration_probability,
                prediction
            FROM migration_predictions
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY created_at DESC
            LIMIT 500
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching probability distribution: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStats()
    {
        $query = "
            SELECT 
                COUNT(*) AS total_predictions,
                SUM(CASE WHEN prediction = 1 THEN 1 ELSE 0 END) AS total_predicted_moveouts,
                SUM(CASE WHEN prediction = 0 THEN 1 ELSE 0 END) AS total_predicted_stayins,
                AVG(JSON_EXTRACT(probability, '$[1]')) AS avg_moveout_probability,
                MAX(created_at) AS last_prediction_date
            FROM migration_predictions
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching dashboard stats: " . $e->getMessage());
            return [
                'total_predictions' => 0,
                'total_predicted_moveouts' => 0,
                'total_predicted_stayins' => 0,
                'avg_moveout_probability' => 0,
                'last_prediction_date' => null
            ];
        }
    }

    /**
     * Clear old predictions (optional cleanup)
     * 
     * @param int $days - Delete predictions older than this many days
     * @return bool
     */
    public function clearOldPredictions($days = 90)
    {
        $query = "
            DELETE FROM migration_predictions
            WHERE created_at < DATE_SUB(CURDATE(), INTERVAL :days DAY)
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error clearing old predictions: " . $e->getMessage());
            return false;
        }
    }
}
