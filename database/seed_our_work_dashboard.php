<?php
/**
 * One-off content pass for the Our Work Dashboard section.
 *
 * Sets up the unified impact dashboard with metrics and sectors.
 *
 * Standalone:  php database/seed_our_work_dashboard.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key = 'our_work_dashboard_v1';
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_OW_DASH_MARKER = 'our_work_dashboard_v1';

/** Add the tables the Our Work dashboard needs. */
function wrc_ow_dashboard_ensure_schema( PDO $pdo ): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `our_work_dashboard` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `section_title` VARCHAR(255) DEFAULT '',
            `section_subtitle` VARCHAR(255) DEFAULT '',
            `subtitle` TEXT NULL,
            `hero_image` VARCHAR(500) DEFAULT '',
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `our_work_dashboard_metrics` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `dashboard_id` INT UNSIGNED NOT NULL,
            `metric_icon` VARCHAR(255) DEFAULT '',
            `metric_color` VARCHAR(100) DEFAULT '',
            `metric_label` VARCHAR(255) DEFAULT '',
            `metric_value` VARCHAR(100) DEFAULT '',
            `sort_order` TINYINT UNSIGNED DEFAULT 0,
            FOREIGN KEY (`dashboard_id`) REFERENCES `our_work_dashboard`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `our_work_dashboard_sectors` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `dashboard_id` INT UNSIGNED NOT NULL,
            `sector_name` VARCHAR(255) DEFAULT '',
            `sector_color` VARCHAR(100) DEFAULT '',
            `sector_count` INT DEFAULT 0,
            `sector_percent` INT DEFAULT 0,
            `sector_gradient` VARCHAR(255) DEFAULT '',
            `sort_order` TINYINT UNSIGNED DEFAULT 0,
            FOREIGN KEY (`dashboard_id`) REFERENCES `our_work_dashboard`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );
}

try {
    wrc_ow_dashboard_ensure_schema( $pdo );
} catch ( Throwable $error ) {
    fwrite( STDERR, 'Our Work Dashboard schema check failed: ' . $error->getMessage() . "\n" );
    throw $error;
}

/** True when a content pass has already been applied. */
function wrc_ow_dash_done( PDO $pdo, string $marker ): bool {
    try {
        $statement = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
        $statement->execute( [ $marker ] );
        return $statement->fetchColumn() === 'done';
    } catch ( Throwable $error ) {
        return false;
    }
}

/** Record a finished content pass. */
function wrc_ow_dash_mark( PDO $pdo, string $marker ): void {
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ $marker, 'done', 'system' ] );
}

if ( !wrc_ow_dash_done( $pdo, WRC_OW_DASH_MARKER ) ) {
    try {
        $pdo->beginTransaction();

        // Insert main dashboard row
        $pdo->prepare(
            'INSERT INTO our_work_dashboard (section_title, section_subtitle, subtitle, hero_image, is_active)
             VALUES (?, ?, ?, ?, 1)'
        )->execute( [
            'Impact Dashboard',
            'Real-time overview of our programme sectors and reach',
            'Live data from active projects across all sectors',
            'assets/images/our-work/dashboard-hero.jpg',
        ] );

        $dashId = (int) $pdo->lastInsertId();

        // Default metrics (values will be overridden by live data at render time)
        $metrics = [
            ['icon' => 'fa-solid fa-users', 'color' => 'brandRed', 'label' => 'Total Beneficiaries', 'value' => '0'],
            ['icon' => 'fa-solid fa-folder-open', 'color' => 'blue', 'label' => 'Active Projects', 'value' => '0'],
            ['icon' => 'fa-solid fa-handshake', 'color' => 'amber', 'label' => 'Partners', 'value' => '0'],
            ['icon' => 'fa-solid fa-map-location-dot', 'color' => 'teal', 'label' => 'Provinces Covered', 'value' => '0'],
        ];

        $insertMetric = $pdo->prepare(
            'INSERT INTO our_work_dashboard_metrics (dashboard_id, metric_icon, metric_color, metric_label, metric_value, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach ( $metrics as $i => $m ) {
            $insertMetric->execute( [ $dashId, $m['icon'], $m['color'], $m['label'], $m['value'], $i ] );
        }

        // Default sectors (counts will be computed from live projects at render time)
        $sectors = [
            ['name' => 'Health', 'color' => '#ef4444', 'count' => 0, 'percent' => 0, 'gradient' => 'linear-gradient(135deg, #ef4444, #f87171)'],
            ['name' => 'WASH', 'color' => '#06b6d4', 'count' => 0, 'percent' => 0, 'gradient' => 'linear-gradient(135deg, #06b6d4, #22d3ee)'],
            ['name' => 'Food & Relief Distribution', 'color' => '#f59e0b', 'count' => 0, 'percent' => 0, 'gradient' => 'linear-gradient(135deg, #f59e0b, #fbbf24)'],
            ['name' => 'Skill Development', 'color' => '#8b5cf6', 'count' => 0, 'percent' => 0, 'gradient' => 'linear-gradient(135deg, #8b5cf6, #a78bfa)'],
        ];

        $insertSector = $pdo->prepare(
            'INSERT INTO our_work_dashboard_sectors (dashboard_id, sector_name, sector_color, sector_count, sector_percent, sector_gradient, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ( $sectors as $i => $s ) {
            $insertSector->execute( [ $dashId, $s['name'], $s['color'], $s['count'], $s['percent'], $s['gradient'], $i ] );
        }

        wrc_ow_dash_mark( $pdo, WRC_OW_DASH_MARKER );

        $pdo->commit();

        echo "Our Work Dashboard prepared.\n";
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }
} else {
    echo "Our Work Dashboard content already applied; left untouched.\n";
}