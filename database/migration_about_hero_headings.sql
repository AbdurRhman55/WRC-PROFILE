-- =============================================
-- FIX: about_hero heading columns
-- =============================================
--
-- about_hero was created ( database/migration_about_tables.sql ) with only
-- hero_image, subtitle and the two button pairs. Every other hero table in the
-- site also stores its heading in section_title / section_subtitle, and the
-- admin editor ( admin/pages.php -> saveHeroSection ) offers "Section Title"
-- and "Section Subtitle" for about_hero as well.
--
-- Because the columns did not exist, an edit to the About page heading was
-- never written anywhere and the editor reloaded showing a hardcoded
-- "Hero Section" label -- the change looked saved, then quietly reverted.
--
-- These columns are also added automatically on every deployment by
-- database/run_project_import.php, so this file is the reference copy.

ALTER TABLE `about_hero`
    ADD COLUMN `section_title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `id`,
    ADD COLUMN `section_subtitle` TEXT NULL AFTER `section_title`;

-- Seed the heading with the wording the public page falls back to, so nothing
-- visible changes for visitors the first time the column is added.
UPDATE `about_hero`
   SET `section_title` = 'About Welfare Relief Committee'
 WHERE `section_title` = '';
