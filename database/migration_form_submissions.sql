-- =============================================================
-- WRC: Form submissions - relevant inquiry types + attachment
-- Run once against the `wrc_website` database.
-- Safe to re-run (guarded by IF NOT EXISTS style checks in PHP
-- migration runner; MySQL has no ADD COLUMN IF NOT EXISTS here,
-- so run this file only one time).
-- =============================================================

USE wrc_website;

-- 1) Let the admin filter submissions by the form that produced them
ALTER TABLE contact_submissions
  MODIFY COLUMN inquiry_type
  ENUM('general','program','donation','partnership','media','career','volunteer','membership')
  NOT NULL DEFAULT 'general';

-- 2) Store the uploaded CV / supporting evidence path
ALTER TABLE contact_submissions
  ADD COLUMN attachment VARCHAR(255) NULL DEFAULT NULL AFTER volunteer_interest;
