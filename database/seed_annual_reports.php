<?php
/**
 * One-off content pass for the Annual Reports page.
 *
 *  - Fills the Annual Reports hero and introduction with the approved wording.
 *  - Creates the six annual-report editions listed in the Website Content
 *    document (2020-2021 through 2025-2026) as resources, so the page is
 *    complete on day one. Each edition starts without a file: the dashboard
 *    shows it as "PDF coming soon" on the website until a PDF is uploaded
 *    through Resources -> Reports -> Add Report.
 *
 * Runs once, guarded by a site_settings marker, so later edits made in the
 * dashboard (including uploaded PDFs) are never overwritten by a deployment.
 *
 * Standalone:  php database/seed_annual_reports.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key = 'annual_reports_page_v1';
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_ANNUAL_REPORTS_MARKER = 'annual_reports_page_v1';

/**
 * Create the two dedicated tables on databases that predate the page, then
 * make sure the hero table has the heading columns. Every step is idempotent,
 * so this is safe even when the migrations were already applied by hand.
 */
function wrc_annual_reports_ensure_schema( PDO $pdo ): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `annual_reports_hero` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `hero_image` VARCHAR(500) DEFAULT '',
            `section_title` VARCHAR(255) DEFAULT '',
            `section_subtitle` TEXT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `annual_reports_content` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `section_title` VARCHAR(255) DEFAULT '',
            `section_subtitle` TEXT NULL,
            `content` JSON NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $hasColumn = static function ( string $table, string $column ) use ( $pdo ): bool {
        foreach ( $pdo->query( 'SHOW COLUMNS FROM `' . $table . '`' ) as $field ) {
            if ( ( $field[ 'Field' ] ?? '' ) === $column ) {
                return true;
            }
        }
        return false;
    };

    if ( !$hasColumn( 'annual_reports_hero', 'section_title' ) ) {
        $pdo->exec( "ALTER TABLE `annual_reports_hero` ADD COLUMN `section_title` VARCHAR(255) DEFAULT '' AFTER `hero_image`" );
    }
    if ( !$hasColumn( 'annual_reports_hero', 'section_subtitle' ) ) {
        $pdo->exec( "ALTER TABLE `annual_reports_hero` ADD COLUMN `section_subtitle` TEXT NULL AFTER `section_title`" );
    }
    if ( !$hasColumn( 'annual_reports_hero', 'hero_image' ) ) {
        $pdo->exec( "ALTER TABLE `annual_reports_hero` ADD COLUMN `hero_image` VARCHAR(500) DEFAULT ''" );
    }
    if ( !$hasColumn( 'annual_reports_content', 'section_title' ) ) {
        $pdo->exec( "ALTER TABLE `annual_reports_content` ADD COLUMN `section_title` VARCHAR(255) DEFAULT ''" );
    }
    if ( !$hasColumn( 'annual_reports_content', 'section_subtitle' ) ) {
        $pdo->exec( "ALTER TABLE `annual_reports_content` ADD COLUMN `section_subtitle` TEXT NULL" );
    }
    if ( !$hasColumn( 'annual_reports_content', 'content' ) ) {
        $pdo->exec( "ALTER TABLE `annual_reports_content` ADD COLUMN `content` JSON NULL" );
    }
}

try {
    wrc_annual_reports_ensure_schema( $pdo );
} catch ( Throwable $error ) {
    fwrite( STDERR, 'Annual Reports schema check failed: ' . $error->getMessage() . "\n" );
    throw $error;
}

try {
    $marker = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
    $marker->execute( [ WRC_ANNUAL_REPORTS_MARKER ] );
    if ( $marker->fetchColumn() === 'done' ) {
        echo "Annual Reports page content already applied; left untouched.\n";
        return;
    }
} catch ( Throwable $error ) {
    // Older database without site_settings: carry on.
}

$heroImage = 'assets/images/hero/about-hero.JPG';

$heroTitle = 'Annual Reports';
$heroSubtitle = 'Transparency Through Reporting, Accountability Through Action';

$introOne = "WRC is committed to transparency, accountability, and responsible resource stewardship. Our annual reports provide an overview of the organization's humanitarian and development initiatives, programme achievements, financial performance, institutional progress, and the impact of our work across communities.";
$introTwo = 'Through regular reporting, we aim to keep our donors, partners, stakeholders, and the communities we serve informed about our activities, progress, challenges, and commitment to continuous improvement.';

$sectionTitle = 'Explore Our Annual Reports';
$sectionSubtitle = "Access our annual reports to learn more about WRC's programmes, partnerships, organizational developments, and financial performance over the years.";

// Edition start year => title exactly as supplied in the Website Content.
$editions = [
    2025 => 'WRC Annual Report · 2025-2026',
    2024 => 'WRC Annual Report · 2024-2025',
    2023 => 'WRC Annual Report · 2023-2024',
    2022 => 'WRC Annual Report · 2022-2023',
    2021 => 'WRC Annual Report · 2021-2022',
    2020 => 'WRC Annual Report · 2020-2021',
];

try {
    $pdo->beginTransaction();

    // 1. Hero section.
    $heroRow = $pdo->query( 'SELECT id FROM annual_reports_hero ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $heroRow ) {
        $pdo->prepare( 'UPDATE annual_reports_hero SET section_title = ?, section_subtitle = ?, hero_image = ? WHERE id = ?' )
            ->execute( [ $heroTitle, $heroSubtitle, $heroImage, $heroRow[ 'id' ] ] );
    } else {
        $pdo->prepare( 'INSERT INTO annual_reports_hero (section_title, section_subtitle, hero_image, is_active) VALUES (?, ?, ?, 1)' )
            ->execute( [ $heroTitle, $heroSubtitle, $heroImage ] );
    }

    // 2. Introduction text. The report cards themselves come from resources.
    $contentJson = json_encode(
        [ 'intro1' => $introOne, 'intro2' => $introTwo ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    $contentRow = $pdo->query( 'SELECT id FROM annual_reports_content ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $contentRow ) {
        $pdo->prepare( 'UPDATE annual_reports_content SET section_title = ?, section_subtitle = ?, content = ? WHERE id = ?' )
            ->execute( [ $sectionTitle, $sectionSubtitle, $contentJson, $contentRow[ 'id' ] ] );
    } else {
        $pdo->prepare( 'INSERT INTO annual_reports_content (section_title, section_subtitle, content, is_active) VALUES (?, ?, ?, 1)' )
            ->execute( [ $sectionTitle, $sectionSubtitle, $contentJson ] );
    }

    // 3. The six editions as report records (no file until one is uploaded).
    $exists = $pdo->prepare( "SELECT COUNT(*) FROM resources WHERE resource_type = 'annual_report' AND year = ?" );
    $insert = $pdo->prepare(
        "INSERT INTO resources (title, description, resource_type, file_path, file_type, year, icon_class, is_active, sort_order)
         VALUES (?, '', 'annual_report', '', 'pdf', ?, 'fa-solid fa-file-pdf', 1, 0)"
    );

    $created = 0;
    foreach ( $editions as $year => $title ) {
        $exists->execute( [ $year ] );
        if ( (int) $exists->fetchColumn() > 0 ) {
            continue;
        }
        $insert->execute( [ $title, $year ] );
        $created++;
    }

    // 4. Remember that this pass has been applied.
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ WRC_ANNUAL_REPORTS_MARKER, 'done', 'system' ] );

    $pdo->commit();

    echo "Annual Reports page prepared: hero, introduction, and {$created} report edition(s).\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    throw $error;
}
