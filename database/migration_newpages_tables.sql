-- =============================================
-- PHASE 6: NEW PAGES DEDICATED TABLES
-- =============================================

-- 1. vision_mission_hero
CREATE TABLE IF NOT EXISTS vision_mission_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. vision_mission_content (complex nested JSON)
CREATE TABLE IF NOT EXISTS vision_mission_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    content JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. org_structure_hero
CREATE TABLE IF NOT EXISTS org_structure_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. org_structure_content (complex nested JSON)
CREATE TABLE IF NOT EXISTS org_structure_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    content JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. policies_hero
CREATE TABLE IF NOT EXISTS policies_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. policies_content (complex nested JSON)
CREATE TABLE IF NOT EXISTS policies_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    content JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7. annual_reports_hero
CREATE TABLE IF NOT EXISTS annual_reports_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. annual_reports_content (complex nested JSON)
CREATE TABLE IF NOT EXISTS annual_reports_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    content JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
