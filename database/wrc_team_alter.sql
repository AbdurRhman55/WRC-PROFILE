USE wrc_website;
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'wrc_website' AND TABLE_NAME = 'team_members' AND COLUMN_NAME = 'linkedin_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE team_members ADD COLUMN linkedin_url VARCHAR(500) AFTER email', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
