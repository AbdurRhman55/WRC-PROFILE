-- =============================================
-- WRC Website Database Schema
-- Database: wrc_website
-- Tables: 31
-- =============================================

CREATE DATABASE IF NOT EXISTS wrc_website DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wrc_website;

-- Disable foreign key checks during creation
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- GROUP 1: SITE CONFIGURATION (3 tables)
-- =============================================

CREATE TABLE site_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE hero_sliders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    image_path VARCHAR(500) NOT NULL,
    cta_text VARCHAR(100),
    cta_link VARCHAR(500),
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE homepage_sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) NOT NULL UNIQUE,
    section_title VARCHAR(255),
    section_subtitle TEXT,
    content JSON,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- GROUP 2: PROGRAMS / OUR WORK (4 tables)
-- =============================================

CREATE TABLE programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    full_title VARCHAR(255),
    icon_class VARCHAR(100),
    hero_image VARCHAR(500),
    fallback_image VARCHAR(500),
    short_description VARCHAR(500),
    hero_description TEXT,
    description TEXT,
    overview TEXT,
    why_it_matters TEXT,
    focus_areas JSON,
    target_groups JSON,
    related_programs JSON,
    sector_number VARCHAR(10),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE program_interventions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    icon_class VARCHAR(100),
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE program_focus_areas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE program_impact_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    stat_value VARCHAR(50) NOT NULL,
    stat_label VARCHAR(255) NOT NULL,
    icon_class VARCHAR(100),
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- GROUP 3: PROJECTS & CAMPAIGNS (4 tables)
-- =============================================

CREATE TABLE provinces (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon_class VARCHAR(100),
    beneficiaries_count INT UNSIGNED DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    status ENUM('active','completed','upcoming') NOT NULL DEFAULT 'active',
    start_date DATE,
    end_date DATE,
    budget_amount DECIMAL(12,2),
    budget_currency VARCHAR(10) DEFAULT 'PKR',
    beneficiaries_count INT UNSIGNED DEFAULT 0,
    progress_percent TINYINT UNSIGNED DEFAULT 0,
    description TEXT,
    overview TEXT,
    location_details TEXT,
    hero_image VARCHAR(500),
    program_id INT UNSIGNED,
    province_id INT UNSIGNED,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE project_donors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    donor_name VARCHAR(255) NOT NULL,
    donor_type VARCHAR(100),
    amount DECIMAL(12,2),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE campaigns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    goal_amount DECIMAL(12,2) NOT NULL,
    raised_amount DECIMAL(12,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'PKR',
    image_path VARCHAR(500),
    status ENUM('active','completed','paused') DEFAULT 'active',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- GROUP 4: NEWS & RESOURCES (2 tables)
-- =============================================

CREATE TABLE news_articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    title VARCHAR(500) NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    category ENUM('press_release','report','story','blog','event') NOT NULL,
    featured_image VARCHAR(500),
    author VARCHAR(255),
    published_at DATE,
    is_published BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    description TEXT,
    resource_type ENUM('publication','annual_report','financial_report','audit_report','policy','download','media_kit') NOT NULL,
    category VARCHAR(100),
    file_path VARCHAR(500),
    file_type VARCHAR(20) DEFAULT 'pdf',
    year YEAR,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- GROUP 5: PEOPLE & ORGANIZATION (3 tables)
-- =============================================

CREATE TABLE team_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    role_type ENUM('chairman','executive','leadership','staff') NOT NULL,
    bio TEXT,
    photo_path VARCHAR(500),
    email VARCHAR(255),
    sort_order TINYINT UNSIGNED DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE history_milestones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year SMALLINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE organizational_structure (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    position_title VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    tier TINYINT UNSIGNED NOT NULL,
    parent_id INT UNSIGNED NULL,
    description TEXT,
    icon_class VARCHAR(100),
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (parent_id) REFERENCES organizational_structure(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- GROUP 6: IMPACT & PARTNERS (3 tables)
-- =============================================

CREATE TABLE impact_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stat_value VARCHAR(50) NOT NULL,
    stat_label VARCHAR(255) NOT NULL,
    description TEXT,
    icon_class VARCHAR(100),
    color VARCHAR(30),
    display_page VARCHAR(100),
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE partners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    logo_path VARCHAR(500) NOT NULL,
    website_url VARCHAR(500),
    partner_type VARCHAR(100),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE province_programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    province_id INT UNSIGNED NOT NULL,
    program_id INT UNSIGNED NOT NULL,
    beneficiaries_count INT UNSIGNED DEFAULT 0,
    UNIQUE KEY (province_id, program_id),
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- GROUP 7: FORMS & SUBMISSIONS (5 tables)
-- =============================================

CREATE TABLE contact_submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    subject VARCHAR(255) NOT NULL,
    inquiry_type ENUM('general','program','donation','partnership','media') NOT NULL DEFAULT 'general',
    message TEXT NOT NULL,
    volunteer_interest BOOLEAN DEFAULT FALSE,
    status ENUM('new','read','replied','archived') DEFAULT 'new',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE donation_tiers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tier_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'PKR',
    description TEXT,
    icon_class VARCHAR(100),
    impact_description TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE bank_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(255) NOT NULL,
    account_title VARCHAR(255) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    iban VARCHAR(50),
    branch VARCHAR(255),
    swift_code VARCHAR(20),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE job_listings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    location VARCHAR(255),
    employment_type ENUM('full_time','part_time','contract','internship') NOT NULL,
    description TEXT,
    requirements TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deadline DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- GROUP 8: SDG (2 tables)
-- =============================================

CREATE TABLE sdg_goals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    goal_number TINYINT NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(20),
    sort_order TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE sdg_contributions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sdg_goal_id INT UNSIGNED NOT NULL,
    contribution_text TEXT NOT NULL,
    progress_percent TINYINT UNSIGNED DEFAULT 0,
    program_id INT UNSIGNED,
    FOREIGN KEY (sdg_goal_id) REFERENCES sdg_goals(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
