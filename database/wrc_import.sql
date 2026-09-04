-- =============================================
-- WRC Website - Master Import File
-- Run this file to create database + import all data
-- =============================================

SOURCE wrc_schema.sql;
SOURCE wrc_seed.sql;

-- Verify
SELECT 'Database created successfully!' AS status;
SELECT COUNT(*) AS total_tables FROM information_schema.tables WHERE table_schema = 'wrc_website';
SELECT 'programs' AS tbl, COUNT(*) AS rows FROM programs
UNION ALL SELECT 'program_interventions', COUNT(*) FROM program_interventions
UNION ALL SELECT 'program_focus_areas', COUNT(*) FROM program_focus_areas
UNION ALL SELECT 'site_settings', COUNT(*) FROM site_settings
UNION ALL SELECT 'hero_sliders', COUNT(*) FROM hero_sliders
UNION ALL SELECT 'history_milestones', COUNT(*) FROM history_milestones
UNION ALL SELECT 'team_members', COUNT(*) FROM team_members
UNION ALL SELECT 'organizational_structure', COUNT(*) FROM organizational_structure
UNION ALL SELECT 'impact_stats', COUNT(*) FROM impact_stats
UNION ALL SELECT 'partners', COUNT(*) FROM partners
UNION ALL SELECT 'provinces', COUNT(*) FROM provinces
UNION ALL SELECT 'news_articles', COUNT(*) FROM news_articles
UNION ALL SELECT 'resources', COUNT(*) FROM resources
UNION ALL SELECT 'projects', COUNT(*) FROM projects
UNION ALL SELECT 'campaigns', COUNT(*) FROM campaigns
UNION ALL SELECT 'donation_tiers', COUNT(*) FROM donation_tiers
UNION ALL SELECT 'bank_accounts', COUNT(*) FROM bank_accounts
UNION ALL SELECT 'job_listings', COUNT(*) FROM job_listings
UNION ALL SELECT 'sdg_goals', COUNT(*) FROM sdg_goals
UNION ALL SELECT 'sdg_contributions', COUNT(*) FROM sdg_contributions;
