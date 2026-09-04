-- =============================================
-- PHASE 4: PROJECTS + CAUSES DEDICATED TABLES
-- =============================================

-- 1. projects_hero
CREATE TABLE IF NOT EXISTS projects_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    subtitle TEXT,
    button1_text VARCHAR(255) DEFAULT '',
    button1_link VARCHAR(500) DEFAULT '',
    button2_text VARCHAR(255) DEFAULT '',
    button2_link VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. projects_dashboard
CREATE TABLE IF NOT EXISTS projects_dashboard (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2a. projects_dashboard_metrics
CREATE TABLE IF NOT EXISTS projects_dashboard_metrics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dashboard_id INT UNSIGNED NOT NULL,
    metric_icon VARCHAR(255) DEFAULT '',
    metric_color VARCHAR(100) DEFAULT '',
    metric_label VARCHAR(255) DEFAULT '',
    metric_value VARCHAR(100) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (dashboard_id) REFERENCES projects_dashboard(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2b. projects_dashboard_sectors
CREATE TABLE IF NOT EXISTS projects_dashboard_sectors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dashboard_id INT UNSIGNED NOT NULL,
    sector_name VARCHAR(255) DEFAULT '',
    sector_color VARCHAR(100) DEFAULT '',
    sector_count INT DEFAULT 0,
    sector_percent INT DEFAULT 0,
    sector_gradient VARCHAR(255) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (dashboard_id) REFERENCES projects_dashboard(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. projects_cta
CREATE TABLE IF NOT EXISTS projects_cta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(500) DEFAULT '',
    description TEXT,
    icon VARCHAR(255) DEFAULT '',
    button1_text VARCHAR(255) DEFAULT '',
    button1_link VARCHAR(500) DEFAULT '',
    button2_text VARCHAR(255) DEFAULT '',
    button2_link VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. causes_hero
CREATE TABLE IF NOT EXISTS causes_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    subtitle TEXT,
    badge VARCHAR(255) DEFAULT '',
    button1_text VARCHAR(255) DEFAULT '',
    button1_link VARCHAR(500) DEFAULT '',
    button2_text VARCHAR(255) DEFAULT '',
    button2_link VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. causes_impact
CREATE TABLE IF NOT EXISTS causes_impact (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5a. causes_impact_stats
CREATE TABLE IF NOT EXISTS causes_impact_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    impact_id INT UNSIGNED NOT NULL,
    stat_icon VARCHAR(255) DEFAULT '',
    stat_label VARCHAR(255) DEFAULT '',
    stat_value VARCHAR(100) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (impact_id) REFERENCES causes_impact(id) ON DELETE CASCADE
) ENGINE=InnoDB;
