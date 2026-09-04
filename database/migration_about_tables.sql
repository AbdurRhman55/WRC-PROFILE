-- =============================================
-- PHASE 1: ABOUT PAGE DEDICATED TABLES
-- =============================================

-- 1. about_hero
CREATE TABLE IF NOT EXISTS about_hero (
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

-- 2. about_mission
CREATE TABLE IF NOT EXISTS about_mission (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2a. about_mission_items
CREATE TABLE IF NOT EXISTS about_mission_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mission_id INT UNSIGNED NOT NULL,
    icon VARCHAR(100) DEFAULT 'fa-bullseye',
    icon_bg VARCHAR(100) DEFAULT 'bg-brandRed/10',
    icon_color VARCHAR(100) DEFAULT 'text-brandRed',
    title VARCHAR(255) DEFAULT '',
    description TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (mission_id) REFERENCES about_mission(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. about_governance
CREATE TABLE IF NOT EXISTS about_governance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3a. about_governance_items
CREATE TABLE IF NOT EXISTS about_governance_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    governance_id INT UNSIGNED NOT NULL,
    icon VARCHAR(100) DEFAULT 'fa-circle-info',
    title VARCHAR(255) DEFAULT '',
    description TEXT,
    link VARCHAR(500) DEFAULT '#',
    link_text VARCHAR(255) DEFAULT 'Learn More',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (governance_id) REFERENCES about_governance(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. about_transparency
CREATE TABLE IF NOT EXISTS about_transparency (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    badge VARCHAR(255) DEFAULT '',
    button1_text VARCHAR(255) DEFAULT '',
    button1_link VARCHAR(500) DEFAULT '',
    button2_text VARCHAR(255) DEFAULT '',
    button2_link VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4a. about_transparency_stats
CREATE TABLE IF NOT EXISTS about_transparency_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transparency_id INT UNSIGNED NOT NULL,
    stat_value VARCHAR(100) DEFAULT '',
    stat_label VARCHAR(255) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (transparency_id) REFERENCES about_transparency(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. about_cta
CREATE TABLE IF NOT EXISTS about_cta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(500) DEFAULT '',
    description TEXT,
    button1_text VARCHAR(255) DEFAULT '',
    button1_link VARCHAR(500) DEFAULT '',
    button2_text VARCHAR(255) DEFAULT '',
    button2_link VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
