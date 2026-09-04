-- =============================================
-- PHASE 2: HOMEPAGE DEDICATED TABLES
-- =============================================

-- 1. chairman_message
CREATE TABLE IF NOT EXISTS chairman_message (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    subtitle VARCHAR(255) DEFAULT '',
    overlay_title VARCHAR(500) DEFAULT '',
    note_label VARCHAR(255) DEFAULT '',
    paragraph1 TEXT,
    paragraph2 TEXT,
    person_name VARCHAR(255) DEFAULT '',
    person_title VARCHAR(255) DEFAULT '',
    vision_label VARCHAR(255) DEFAULT '',
    vision_text TEXT,
    image_path VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. geographic_presence
CREATE TABLE IF NOT EXISTS geographic_presence (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2a. geographic_presence_stats
CREATE TABLE IF NOT EXISTS geographic_presence_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    geo_id INT UNSIGNED NOT NULL,
    stat_value VARCHAR(100) DEFAULT '',
    stat_label VARCHAR(255) DEFAULT '',
    stat_icon VARCHAR(255) DEFAULT '',
    stat_color VARCHAR(100) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (geo_id) REFERENCES geographic_presence(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. cta_banner (same structure as about_cta)
CREATE TABLE IF NOT EXISTS cta_banner (
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
