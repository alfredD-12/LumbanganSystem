ALTER TABLE incidents
  ADD COLUMN forwarded_to_police TINYINT(1) NOT NULL DEFAULT 0 AFTER status_id,
  ADD COLUMN forwarded_to_police_at DATETIME DEFAULT NULL AFTER forwarded_to_police,
  ADD KEY idx_incident_forwarded_to_police (forwarded_to_police);
