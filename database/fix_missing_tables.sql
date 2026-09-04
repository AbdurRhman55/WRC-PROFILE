-- Fix: Create missing tables referenced by admin and frontend

CREATE TABLE IF NOT EXISTS `history_projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `year` VARCHAR(10) NOT NULL DEFAULT '',
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `sector` VARCHAR(100) NOT NULL DEFAULT '',
    `location` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT,
    `beneficiaries` VARCHAR(100) NOT NULL DEFAULT '',
    `status` VARCHAR(50) NOT NULL DEFAULT 'Completed',
    `program_slug` VARCHAR(100) NOT NULL DEFAULT '',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `where_we_work_districts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `district` VARCHAR(255) NOT NULL DEFAULT '',
    `province` VARCHAR(100) NOT NULL DEFAULT '',
    `programs` VARCHAR(255) NOT NULL DEFAULT '',
    `beneficiaries` VARCHAR(100) NOT NULL DEFAULT '',
    `year` VARCHAR(10) NOT NULL DEFAULT '',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fix: Add missing columns to history_milestones
ALTER TABLE `history_milestones`
    ADD COLUMN IF NOT EXISTS `image` VARCHAR(255) DEFAULT '' AFTER `description`,
    ADD COLUMN IF NOT EXISTS `tag` VARCHAR(100) DEFAULT '' AFTER `image`,
    ADD COLUMN IF NOT EXISTS `tag_color` VARCHAR(50) DEFAULT '' AFTER `tag`,
    ADD COLUMN IF NOT EXISTS `stats_text` VARCHAR(255) DEFAULT '' AFTER `tag_color`;

-- Fix: Add missing sort_order to news_articles if not exists
SET @exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news_articles' AND COLUMN_NAME = 'sort_order');
SET @sql = IF(@exists = 0, 'ALTER TABLE `news_articles` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0 AFTER `is_published`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Fix: Update hero_sliders cta_link from .html to .php
UPDATE `hero_sliders` SET `cta_link` = REPLACE(`cta_link`, '.html', '.php') WHERE `cta_link` LIKE '%.html';

-- Fix: Update impact_stats display_page values to match homepage queries
UPDATE `impact_stats` SET `display_page` = 'home_hero' WHERE `display_page` = 'homepage' AND `stat_label` LIKE '%Year%' OR `stat_label` LIKE '%life%';
UPDATE `impact_stats` SET `display_page` = 'home_kpi' WHERE `display_page` = 'homepage';
