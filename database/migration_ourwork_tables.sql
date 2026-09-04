-- =============================================
-- PHASE 3: OUR WORK DEDICATED TABLES
-- =============================================

-- 1. our_work_hero
CREATE TABLE IF NOT EXISTS our_work_hero (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_image VARCHAR(500) DEFAULT '',
    subtitle VARCHAR(500) DEFAULT '',
    button1_text VARCHAR(255) DEFAULT '',
    button1_link VARCHAR(500) DEFAULT '',
    button2_text VARCHAR(255) DEFAULT '',
    button2_link VARCHAR(500) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. our_work_editorial
CREATE TABLE IF NOT EXISTS our_work_editorial (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    image VARCHAR(500) DEFAULT '',
    label VARCHAR(255) DEFAULT '',
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2a. our_work_editorial_features
CREATE TABLE IF NOT EXISTS our_work_editorial_features (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    editorial_id INT UNSIGNED NOT NULL,
    icon VARCHAR(255) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    description TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (editorial_id) REFERENCES our_work_editorial(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. our_work_process
CREATE TABLE IF NOT EXISTS our_work_process (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3a. our_work_process_steps
CREATE TABLE IF NOT EXISTS our_work_process_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id INT UNSIGNED NOT NULL,
    step_num VARCHAR(10) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    description TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (process_id) REFERENCES our_work_process(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. our_work_pillars
CREATE TABLE IF NOT EXISTS our_work_pillars (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    section_subtitle TEXT,
    subtitle TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4a. our_work_pillars_items
CREATE TABLE IF NOT EXISTS our_work_pillars_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pillars_id INT UNSIGNED NOT NULL,
    pillar_num VARCHAR(10) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    description TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (pillars_id) REFERENCES our_work_pillars(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. our_work_featured_projects
CREATE TABLE IF NOT EXISTS our_work_featured_projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) DEFAULT '',
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. our_work_cta
CREATE TABLE IF NOT EXISTS our_work_cta (
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
