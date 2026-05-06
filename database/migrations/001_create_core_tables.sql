CREATE TABLE IF NOT EXISTS architecture_layers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  layer_key VARCHAR(80) NOT NULL UNIQUE,
  label VARCHAR(160) NOT NULL,
  description TEXT NOT NULL,
  priority_order TINYINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS threat_catalog (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  threat_key VARCHAR(80) NOT NULL UNIQUE,
  label VARCHAR(180) NOT NULL,
  severity TINYINT UNSIGNED NOT NULL,
  description TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS control_catalog (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  control_key VARCHAR(100) NOT NULL UNIQUE,
  label VARCHAR(180) NOT NULL,
  family VARCHAR(80) NOT NULL,
  weight TINYINT UNSIGNED NOT NULL,
  threat_keys JSON NOT NULL,
  description TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_control_family (family)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NOT NULL UNIQUE,
  asset_name VARCHAR(180) NOT NULL,
  environment VARCHAR(80) NOT NULL,
  implemented_controls_json JSON NOT NULL,
  missing_controls_json JSON NOT NULL,
  threat_scores_json JSON NOT NULL,
  maturity_score DECIMAL(8,2) NOT NULL,
  risk_label ENUM('low','moderate','high','critical') NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_assessment_risk_created (risk_label, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS meter_readings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  meter_id VARCHAR(120) NOT NULL,
  reading_time DATETIME NOT NULL,
  phase_a_voltage DECIMAL(10,3) NOT NULL,
  phase_a_current DECIMAL(10,3) NOT NULL,
  phase_a_power DECIMAL(10,3) NOT NULL,
  phase_b_voltage DECIMAL(10,3) NOT NULL,
  phase_b_current DECIMAL(10,3) NOT NULL,
  phase_b_power DECIMAL(10,3) NOT NULL,
  phase_c_voltage DECIMAL(10,3) NOT NULL,
  phase_c_current DECIMAL(10,3) NOT NULL,
  phase_c_power DECIMAL(10,3) NOT NULL,
  power_factor DECIMAL(6,3) NOT NULL,
  integrity_hash CHAR(64) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_meter_time (meter_id, reading_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS experiments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(220) NOT NULL UNIQUE,
  objective TEXT NOT NULL,
  layer_count INT UNSIGNED NOT NULL,
  threat_count INT UNSIGNED NOT NULL,
  control_count INT UNSIGNED NOT NULL,
  status ENUM('planned','active','completed') NOT NULL DEFAULT 'planned',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  action VARCHAR(120) NOT NULL,
  actor VARCHAR(160) NULL,
  payload_json JSON NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_action_created (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
