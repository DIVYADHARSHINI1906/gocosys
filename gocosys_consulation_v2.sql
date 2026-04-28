-- ═══════════════════════════════════════════════════
--  GOCOSYS — Consultation Table (Simple Form Version)
--  phpMyAdmin → gocosys_blog → SQL tab → Run
-- ═══════════════════════════════════════════════════

USE gocosys_blog;

-- Drop old table if exists (only if you haven't used it yet)
DROP TABLE IF EXISTS consultations;

-- Create fresh table matching your HTML form
CREATE TABLE consultations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL,
    phone           VARCHAR(20)   DEFAULT NULL,
    service_type    VARCHAR(100)  NOT NULL,       -- "Internship", "Training", etc.
    project_details TEXT          DEFAULT NULL,   -- "Tell us about your goals"
    status          ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    ip_address      VARCHAR(45)   DEFAULT NULL,
    created_at      DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;