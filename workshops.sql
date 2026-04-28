-- ═══════════════════════════════════════════════════
--  GOCOSYS — Workshops Table
--  phpMyAdmin → gocosys_blog → SQL tab → Run
-- ═══════════════════════════════════════════════════

USE gocosys_blog;

CREATE TABLE IF NOT EXISTS workshops (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(200)  NOT NULL,
    description    TEXT          DEFAULT NULL,
    workshop_date  DATE          NOT NULL,
    workshop_time  TIME          DEFAULT '10:00:00',
    mode           ENUM('Online','Offline','Hybrid') DEFAULT 'Online',
    price          VARCHAR(50)   DEFAULT 'Free',
    seats          INT           DEFAULT 0,
    icon           VARCHAR(10)   DEFAULT '🎓',
    color          VARCHAR(200)  DEFAULT 'linear-gradient(135deg,#90CAF9,#1565c0)',
    register_link  VARCHAR(500)  DEFAULT NULL,
    status         ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
    created_at     DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample workshops (optional - delete if not needed)
INSERT INTO workshops (title, description, workshop_date, workshop_time, mode, price, seats, icon, color, register_link, status) VALUES
('Web Development Bootcamp', 'Learn HTML, CSS, JavaScript and React from scratch. Build real projects and get placement support.', '2026-05-15', '10:00:00', 'Online', 'Free', 50, '💻', 'linear-gradient(135deg,#90CAF9,#1565c0)', '#', 'upcoming'),
('AI & Machine Learning Workshop', 'Hands-on session on Python, ML algorithms, and building AI models for real-world applications.', '2026-05-22', '11:00:00', 'Online', '₹299', 30, '🤖', 'linear-gradient(135deg,#6a1b9a,#ce93d8)', '#', 'upcoming'),
('SEO Masterclass', 'Advanced SEO strategies, keyword research, and Google ranking techniques for 2026.', '2026-06-01', '10:00:00', 'Offline', 'Free', 40, '📊', 'linear-gradient(135deg,#c8942a,#f0c040)', '#', 'upcoming');
