-- Migration: New tables for hardcoded sections
-- Run after wrc_schema.sql

-- =============================================
-- EVENTS TABLE (for news/index.php Upcoming Events)
-- =============================================
CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time VARCHAR(50),
    location VARCHAR(255),
    icon_class VARCHAR(100) DEFAULT 'fa-solid fa-calendar-days',
    gradient_from VARCHAR(20) DEFAULT '#ca0000',
    gradient_to VARCHAR(20) DEFAULT '#8b0000',
    register_link VARCHAR(500),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT NULL
) ENGINE=InnoDB;

-- =============================================
-- PHOTO GALLERY TABLE (for news/index.php Photo Gallery)
-- =============================================
CREATE TABLE IF NOT EXISTS photo_gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    caption VARCHAR(500),
    category VARCHAR(100) DEFAULT 'general',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- VIDEO GALLERY TABLE (for news/index.php Video Gallery)
-- =============================================
CREATE TABLE IF NOT EXISTS video_gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    thumbnail_path VARCHAR(500),
    video_url VARCHAR(500),
    duration VARCHAR(20),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- GEOGRAPHIC STATS (for homepage Geographic Presence hardcoded text)
-- =============================================
INSERT IGNORE INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('geographic_presence', 'Serving Communities Across Pakistan', NULL, '{"stats":[{"value":"4","label":"Provinces"},{"value":"46+","label":"Districts Covered"},{"value":"150,000+","label":"Lives Impacted"}]}', 22, 1);

-- Seed: Insert sample events
INSERT INTO events (title, description, event_date, event_time, location, icon_class, gradient_from, gradient_to, sort_order) VALUES
('Humanitarian Aid Drive', 'Join us in distributing essential supplies to families in need across flood-affected regions.', '2026-09-15', '09:00 AM - 05:00 PM', 'WRC Field Office, Muzaffarabad', 'fa-solid fa-hand-holding-heart', '#ca0000', '#8b0000', 1),
('Volunteer Orientation Workshop', 'Learn about our programs and discover how you can make a meaningful difference in your community.', '2026-10-08', '10:00 AM - 02:00 PM', 'WRC Training Center, Islamabad', 'fa-solid fa-users', '#2F6F62', '#1a4a40', 2),
('Annual Gala & Fundraiser', 'An evening of hope and solidarity, celebrating our collective achievements and raising funds for 2027 programs.', '2026-12-20', '06:00 PM - 10:00 PM', 'Serena Hotel, Islamabad', 'fa-solid fa-glass-cheers', '#C89B3C', '#a67d2e', 3);

-- Seed: Insert sample gallery photos
INSERT INTO photo_gallery (image_path, alt_text, caption, category, sort_order) VALUES
('assets/images/hero.jpg', 'Emergency relief distribution', 'Emergency relief distribution in progress', 'relief', 1),
('assets/images/water.jpg', 'Clean water access project', 'Clean water access for rural communities', 'wash', 2),
('assets/images/food.jpg', 'Food security program', 'Food security program for vulnerable families', 'relief', 3),
('assets/images/health.jpg', 'Mobile health camp', 'Mobile health camp serving remote areas', 'health', 4),
('assets/images/education.jpg', 'Education initiative', 'Quality education for every child', 'education', 5),
('assets/images/hero.jpg', 'Community resilience building', 'Building resilient communities together', 'relief', 6),
('assets/images/water.jpg', 'Water infrastructure development', 'Sustainable water infrastructure', 'wash', 7),
('assets/images/food.jpg', 'Nutrition support', 'Nutrition support for mothers and children', 'health', 8);

-- Seed: Insert sample videos
INSERT INTO video_gallery (title, description, thumbnail_path, duration, sort_order) VALUES
('WRC in Action: Flood Response 2025', 'Watch how our teams responded to devastating floods, providing emergency shelter and relief to thousands.', 'assets/images/hero.jpg', '3:42', 1),
('Clean Water Changes Everything', 'A documentary on how clean water access transforms entire communities, reducing disease and improving lives.', 'assets/images/water.jpg', '5:18', 2),
('Education: Our Hope for the Future', 'Meet the children whose lives are being transformed through quality education and dedicated teachers.', 'assets/images/education.jpg', '4:05', 3);
