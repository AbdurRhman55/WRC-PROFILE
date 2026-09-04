-- =============================================
-- PHASE 8: REMAINING DEDICATED TABLES
-- =============================================

-- 1. news_hero
CREATE TABLE IF NOT EXISTS news_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    subtitle TEXT,
    badge VARCHAR(255) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. resources_hero (complex nested JSON)
CREATE TABLE IF NOT EXISTS resources_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    content JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. get_involved_hero
CREATE TABLE IF NOT EXISTS get_involved_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    subtitle TEXT,
    badge VARCHAR(255) DEFAULT '',
    button1_text VARCHAR(255) DEFAULT '',
    button2_text VARCHAR(255) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. get_involved_cta (simple CTA)
CREATE TABLE IF NOT EXISTS get_involved_cta (
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

-- 5. contribute_hero
CREATE TABLE IF NOT EXISTS contribute_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    subtitle TEXT,
    badge VARCHAR(255) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. contribute_impact (items)
CREATE TABLE IF NOT EXISTS contribute_impact (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6a. contribute_impact_items
CREATE TABLE IF NOT EXISTS contribute_impact_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    impact_id INT UNSIGNED NOT NULL,
    icon VARCHAR(255) DEFAULT '',
    amount VARCHAR(255) DEFAULT '',
    description TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (impact_id) REFERENCES contribute_impact(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. contribute_transparency (items)
CREATE TABLE IF NOT EXISTS contribute_transparency (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7a. contribute_transparency_items
CREATE TABLE IF NOT EXISTS contribute_transparency_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transparency_id INT UNSIGNED NOT NULL,
    icon VARCHAR(255) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    description TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (transparency_id) REFERENCES contribute_transparency(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. contribute_why (stats)
CREATE TABLE IF NOT EXISTS contribute_why (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8a. contribute_why_stats
CREATE TABLE IF NOT EXISTS contribute_why_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    why_id INT UNSIGNED NOT NULL,
    stat_label VARCHAR(255) DEFAULT '',
    stat_value VARCHAR(100) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (why_id) REFERENCES contribute_why(id) ON DELETE CASCADE
) ENGINE=InnoDB;
