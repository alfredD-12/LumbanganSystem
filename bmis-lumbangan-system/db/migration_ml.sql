-- 1) resident_migrations table (if not present)
CREATE TABLE IF NOT EXISTS resident_migrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL,
    from_purok_id BIGINT UNSIGNED DEFAULT NULL,
    to_purok_id BIGINT UNSIGNED DEFAULT NULL,
    moved_at DATE NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    is_synthetic TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(idx_moved_at) (moved_at),
    INDEX(idx_person) (person_id)
);

-- 2) Table to store predictions
CREATE TABLE IF NOT EXISTS migration_predictions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED DEFAULT NULL,
    timeframe ENUM('day','month','year') NOT NULL,
    prediction TINYINT(1) NOT NULL,       -- binary 0/1
    probability JSON NOT NULL,            -- store probability array e.g. [0.7,0.3]
    model_version VARCHAR(128) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3) ML view that returns ALL data including is_synthetic flag.
--    This lets the training script filter by is_synthetic easily.
CREATE OR REPLACE VIEW ml_migration_dataset_all AS
SELECT
    rm.id AS migration_id,
    p.id AS person_id,
    p.birthdate,
    p.sex,
    p.household_id,
    COALESCE(hh_count.total_members, 1) AS household_size,
    TIMESTAMPDIFF(YEAR, p.birthdate, rm.moved_at) AS age,
    rm.to_purok_id,
    rm.from_purok_id,
    rm.moved_at,
    rm.reason,
    rm.is_synthetic
FROM resident_migrations rm
JOIN persons p ON p.id = rm.person_id
LEFT JOIN (
  SELECT household_id, COUNT(*) AS total_members FROM persons GROUP BY household_id
) hh_count ON hh_count.household_id = p.household_id;
