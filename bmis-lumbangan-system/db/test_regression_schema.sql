SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS incidents;
DROP TABLE IF EXISTS case_types;
DROP TABLE IF EXISTS statuses;
DROP TABLE IF EXISTS document_requests;
DROP TABLE IF EXISTS document_templates;
DROP TABLE IF EXISTS document_types;
DROP TABLE IF EXISTS document_categories;
DROP TABLE IF EXISTS gallery;

CREATE TABLE document_categories (
  category_id INT NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE document_types (
  document_type_id INT NOT NULL AUTO_INCREMENT,
  category_id INT NOT NULL,
  document_name VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  requirements TEXT DEFAULT NULL,
  fee DECIMAL(10,2) DEFAULT 0.00,
  PRIMARY KEY (document_type_id),
  KEY idx_document_types_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE document_requests (
  request_id INT NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  document_type_id INT NOT NULL,
  purpose VARCHAR(255) DEFAULT NULL,
  status ENUM('Pending','Approved','Released','Rejected') NOT NULL DEFAULT 'Pending',
  proof_upload VARCHAR(255) DEFAULT NULL,
  requested_for VARCHAR(150) DEFAULT NULL,
  relation_to_requestee VARCHAR(150) DEFAULT NULL,
  request_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (request_id),
  KEY idx_document_requests_user (user_id),
  KEY idx_document_requests_type (document_type_id),
  KEY idx_document_requests_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE document_templates (
  id INT NOT NULL AUTO_INCREMENT,
  document_type_id INT NOT NULL,
  template_html LONGTEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_document_templates_type (document_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE gallery (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  image_path VARCHAR(500) NOT NULL,
  display_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_gallery_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE statuses (
  id INT NOT NULL AUTO_INCREMENT,
  label VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_statuses_label (label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE case_types (
  id INT NOT NULL AUTO_INCREMENT,
  label VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_case_types_label (label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE incidents (
  id INT NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  incident_title VARCHAR(255) NOT NULL,
  blotter_type VARCHAR(50) NOT NULL,
  case_type_id INT NOT NULL,
  complainant_name VARCHAR(255) NOT NULL,
  complainant_type VARCHAR(50) NOT NULL,
  complainant_contact VARCHAR(50) DEFAULT NULL,
  complainant_gender VARCHAR(20) NOT NULL,
  complainant_birthday DATE DEFAULT NULL,
  complainant_address TEXT DEFAULT NULL,
  offender_name VARCHAR(255) DEFAULT NULL,
  offender_type VARCHAR(50) DEFAULT NULL,
  offender_gender VARCHAR(20) DEFAULT NULL,
  offender_address TEXT DEFAULT NULL,
  offender_description TEXT DEFAULT NULL,
  date_of_incident DATE NOT NULL,
  time_of_incident TIME NOT NULL,
  location VARCHAR(255) NOT NULL,
  narrative TEXT NOT NULL,
  status_id INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_incidents_status (status_id),
  KEY idx_incidents_case_type (case_type_id),
  KEY idx_incidents_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

