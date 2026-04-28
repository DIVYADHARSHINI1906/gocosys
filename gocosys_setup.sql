-- ═══════════════════════════════════════════════════
--  GOCOSYS — Complete Database Setup
--  phpMyAdmin → gocosys_blog → SQL tab → Run
--  (Safe to run multiple times — uses IF NOT EXISTS)
-- ═══════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS gocosys_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gocosys_blog;

-- ══════════════════════════════════════════════════
--  USERS
-- ══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    password        VARCHAR(255)  NOT NULL,
    role            ENUM('user','admin') DEFAULT 'user',
    avatar_initials VARCHAR(3)    DEFAULT 'U',
    avatar_color    VARCHAR(200)  DEFAULT 'linear-gradient(135deg,#90CAF9,#1565c0)',
    created_at      DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════
--  ARTICLES
-- ══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS articles (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    category         VARCHAR(50)   NOT NULL,
    category_label   VARCHAR(80)   NOT NULL,
    title            VARCHAR(300)  NOT NULL,
    excerpt          TEXT          NOT NULL,
    content          LONGTEXT      NOT NULL,
    author           VARCHAR(100)  NOT NULL,
    author_initials  VARCHAR(3)    DEFAULT 'GC',
    author_role      VARCHAR(100)  DEFAULT '',
    author_color     VARCHAR(200)  DEFAULT 'linear-gradient(135deg,#90CAF9,#1565c0)',
    read_time        VARCHAR(20)   DEFAULT '5 min',
    featured         TINYINT(1)    DEFAULT 0,
    published        TINYINT(1)    DEFAULT 1,
    created_at       DATETIME      DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category  (category),
    INDEX idx_published (published),
    INDEX idx_featured  (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════
--  LIKES & BOOKMARKS
-- ══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS likes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    article_id  INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_like (user_id, article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookmarks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    article_id  INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_bookmark (user_id, article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════
--  CONSULTATIONS (Contact / Book form)
-- ══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS consultations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL,
    phone           VARCHAR(20)   DEFAULT NULL,
    service_type    VARCHAR(100)  NOT NULL,
    project_details TEXT          DEFAULT NULL,
    status          ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    ip_address      VARCHAR(45)   DEFAULT NULL,
    created_at      DATETIME      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email  (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════
--  CONTACT MESSAGES (Contact Us form)
-- ══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL,
    subject     VARCHAR(200)  NOT NULL,
    message     TEXT          NOT NULL,
    status      ENUM('new','read','replied') DEFAULT 'new',
    ip_address  VARCHAR(45)   DEFAULT NULL,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email  (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════
--  NEWSLETTER SUBSCRIBERS
-- ══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    email          VARCHAR(150)  NOT NULL UNIQUE,
    status         ENUM('active','unsubscribed') DEFAULT 'active',
    ip_address     VARCHAR(45)   DEFAULT NULL,
    subscribed_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════
--  EMAIL LOGS (track all sent emails)
-- ══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS email_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    to_email    VARCHAR(150)  NOT NULL,
    subject     VARCHAR(300)  NOT NULL,
    type        VARCHAR(80)   NOT NULL COMMENT 'consultation_confirm, newsletter_welcome, contact_autoreply, etc.',
    sent        TINYINT(1)    DEFAULT 0 COMMENT '1=success, 0=failed',
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type  (type),
    INDEX idx_email (to_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════
--  MAKE YOURSELF ADMIN
--  (Register பண்ணிட்டு இந்த query run பண்ணுங்கள்)
-- ══════════════════════════════════════════════════
-- UPDATE users SET role='admin' WHERE email='youremail@gmail.com';

-- ══════════════════════════════════════════════════
--  VERIFY TABLES
-- ══════════════════════════════════════════════════
-- SHOW TABLES;
-- SELECT COUNT(*) FROM articles;
-- SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 10;
-- SELECT * FROM newsletter_subscribers;
-- SELECT * FROM consultations ORDER BY created_at DESC;
-- SELECT * FROM contact_messages ORDER BY created_at DESC;
