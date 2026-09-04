-- =============================================
-- PHASE 5: WHO WE ARE + HISTORY DEDICATED TABLES
-- =============================================

-- 1. who_we_are_hero
CREATE TABLE IF NOT EXISTS who_we_are_hero (
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

-- 2. who_we_are_impact
CREATE TABLE IF NOT EXISTS who_we_are_impact (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2a. who_we_are_impact_items
CREATE TABLE IF NOT EXISTS who_we_are_impact_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    impact_id INT UNSIGNED NOT NULL,
    icon VARCHAR(255) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    description TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (impact_id) REFERENCES who_we_are_impact(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. why_we_exist
CREATE TABLE IF NOT EXISTS why_we_exist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3a. why_we_exist_items
CREATE TABLE IF NOT EXISTS why_we_exist_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    why_id INT UNSIGNED NOT NULL,
    icon VARCHAR(255) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    description TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (why_id) REFERENCES why_we_exist(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. history_hero
CREATE TABLE IF NOT EXISTS history_hero (
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

-- 5. history_stats
CREATE TABLE IF NOT EXISTS history_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5a. history_stats_items
CREATE TABLE IF NOT EXISTS history_stats_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stats_id INT UNSIGNED NOT NULL,
    stat_icon VARCHAR(255) DEFAULT '',
    stat_color VARCHAR(100) DEFAULT '',
    stat_label VARCHAR(255) DEFAULT '',
    stat_value VARCHAR(100) DEFAULT '',
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (stats_id) REFERENCES history_stats(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. history_cta
CREATE TABLE IF NOT EXISTS history_cta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    badge VARCHAR(255) DEFAULT '',
    section_title VARCHAR(500) DEFAULT '',
    section_subtitle TEXT,
    button1_text VARCHAR(255) DEFAULT '',
    button1_link VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
