-- ---------------------------------------------------------------------------
-- WRC project taxonomy: Organisation -> Category -> Sub-category
--
-- Every project belongs to an organisation (ZFA, Turkiye Diyanet, KP-TEVTA,
-- Islamic Relief, ...). That organisation owns its own list of categories, and
-- each category can have any number of sub-categories.
--
-- projects.category keeps storing the public programme sector ("Health",
-- "Food Security", ...) so the public filters never change; the new taxonomy
-- columns hold the detailed classification used by the admin.
--
-- The deploy step also applies this schema through wrcEnsureProjectTaxonomy(),
-- so running this file by hand is only needed for a fresh local database.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS project_organizations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    contact_email VARCHAR(191) NULL,
    website_url VARCHAR(500) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_project_organizations_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization_id INT UNSIGNED NOT NULL,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    sector VARCHAR(100) NOT NULL DEFAULT 'General',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_project_categories_slug (organization_id, slug),
    KEY idx_project_categories_org (organization_id),
    CONSTRAINT fk_project_categories_org FOREIGN KEY (organization_id)
        REFERENCES project_organizations (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_subcategories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_project_subcategories_slug (category_id, slug),
    KEY idx_project_subcategories_category (category_id),
    CONSTRAINT fk_project_subcategories_category FOREIGN KEY (category_id)
        REFERENCES project_categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE projects
    ADD COLUMN organization_id INT UNSIGNED NULL AFTER category,
    ADD COLUMN category_id INT UNSIGNED NULL AFTER organization_id,
    ADD COLUMN subcategory_id INT UNSIGNED NULL AFTER category_id;

ALTER TABLE projects
    ADD KEY idx_projects_organization (organization_id),
    ADD KEY idx_projects_category (category_id),
    ADD KEY idx_projects_subcategory (subcategory_id);
