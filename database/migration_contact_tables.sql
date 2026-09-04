-- =============================================
-- PHASE 7: CONTACT + IMPACT + WHERE WE WORK TABLES
-- =============================================

-- 1. contact_hero
CREATE TABLE IF NOT EXISTS contact_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. contact_content (complex nested JSON)
CREATE TABLE IF NOT EXISTS contact_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    content JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. impact_hero
CREATE TABLE IF NOT EXISTS impact_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. impact_content (complex nested JSON)
CREATE TABLE IF NOT EXISTS impact_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    content JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. where_we_work_hero
CREATE TABLE IF NOT EXISTS where_we_work_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. where_we_work_content (complex nested JSON)
CREATE TABLE IF NOT EXISTS where_we_work_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    content JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
