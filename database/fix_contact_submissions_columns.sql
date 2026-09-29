USE wrc_website;

-- Add missing columns for feedback rating and whistleblower anonymity
ALTER TABLE contact_submissions
  ADD COLUMN rating TINYINT DEFAULT NULL AFTER message,
  ADD COLUMN is_anonymous TINYINT(1) DEFAULT 0 AFTER rating;
