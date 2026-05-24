-- Complaint History / Audit Log for Police status updates
-- Run this once on your BMIS Lumbangan database

CREATE TABLE IF NOT EXISTS complaint_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    previous_status_id INT DEFAULT NULL,
    updated_status_id INT NOT NULL,
    updated_by_official_id BIGINT UNSIGNED NOT NULL,
    remarks TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_complaint_history_complaint (complaint_id),
    KEY idx_complaint_history_updated_status (updated_status_id),
    KEY idx_complaint_history_updated_by (updated_by_official_id),
    KEY idx_complaint_history_created_at (created_at),
    CONSTRAINT fk_complaint_history_incident FOREIGN KEY (complaint_id) REFERENCES incidents (id) ON DELETE CASCADE,
    CONSTRAINT fk_complaint_history_prev_status FOREIGN KEY (previous_status_id) REFERENCES statuses (id) ON DELETE SET NULL,
    CONSTRAINT fk_complaint_history_new_status FOREIGN KEY (updated_status_id) REFERENCES statuses (id) ON DELETE RESTRICT,
    CONSTRAINT fk_complaint_history_official FOREIGN KEY (updated_by_official_id) REFERENCES officials (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
