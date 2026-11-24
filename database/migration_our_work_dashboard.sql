-- ======================================================
-- Migration: Our Work Dashboard Section
-- Date: 2026-11-24
-- ======================================================

-- 1. our_work_dashboard (main table)
CREATE TABLE IF NOT EXISTS our_work_dashboard (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle VARCHAR(255) DEFAULT '',
    subtitle TEXT,
    hero_image VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 1a. our_work_dashboard_metrics
CREATE TABLE IF NOT EXISTS our_work_dashboard_metrics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dashboard_id INT UNSIGNED NOT NULL,
    metric_icon VARCHAR(255) DEFAULT '',
    metric_color VARCHAR(100) DEFAULT '',
    metric_label VARCHAR(255) DEFAULT '',
    metric_value VARCHAR(100) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (dashboard_id) REFERENCES our_work_dashboard(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 1b. our_work_dashboard_sectors
CREATE TABLE IF NOT EXISTS our_work_dashboard_sectors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dashboard_id INT UNSIGNED NOT NULL,
    sector_name VARCHAR(255) DEFAULT '',
    sector_color VARCHAR(100) DEFAULT '',
    sector_count INT DEFAULT 0,
    sector_percent INT DEFAULT 0,
    sector_gradient VARCHAR(255) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (dashboard_id) REFERENCES our_work_dashboard(id) ON DELETE CASCADE
) ENGINE=InnoDB;