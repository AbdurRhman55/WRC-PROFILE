<?php
/**
 * One-off content pass for the Project Portfolio page.
 *
 *  - Adds the approved hero eyebrow ("Our Portfolio").
 *  - Sets the hero headline ("Delivering Projects, Creating Lasting Impact").
 *  - Adds the two approved portfolio paragraphs that render inside the hero.
 *
 * Runs once, guarded by a site_settings marker, so later dashboard edits are
 * never overwritten by a deployment.
 *
 * Standalone:  php database/seed_portfolio_page.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key = 'portfolio_page_v1';
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_PORTFOLIO_MARKER = 'portfolio_page_v1';

/** Add the columns the portfolio hero needs, without touching stored rows. */
function wrc_portfolio_ensure_schema( PDO $pdo ): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `projects_hero` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `hero_image` VARCHAR(500) DEFAULT '',
            `section_title` VARCHAR(255) DEFAULT '',
            `section_subtitle` VARCHAR(255) DEFAULT '',
            `subtitle` TEXT NULL,
            `button1_text` VARCHAR(255) DEFAULT '',
            `button1_link` VARCHAR(500) DEFAULT '',
            `button2_text` VARCHAR(255) DEFAULT '',
            `button2_link` VARCHAR(500) DEFAULT '',
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $existing = [];
    foreach ( $pdo->query( 'SHOW COLUMNS FROM `projects_hero`' ) as $field ) {
        $existing[ $field[ 'Field' ] ?? '' ] = true;
    }

    foreach ( [
        'eyebrow' => 'VARCHAR(120) NULL',
        'intro1'  => 'TEXT NULL',
        'intro2'  => 'TEXT NULL',
    ] as $column => $definition ) {
        if ( !isset( $existing[ $column ] ) ) {
            $pdo->exec( "ALTER TABLE `projects_hero` ADD COLUMN `{$column}` {$definition}" );
        }
    }
}

try {
    wrc_portfolio_ensure_schema( $pdo );
} catch ( Throwable $error ) {
    fwrite( STDERR, 'Portfolio page schema check failed: ' . $error->getMessage() . "\n" );
    throw $error;
}

try {
    $marker = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
    $marker->execute( [ WRC_PORTFOLIO_MARKER ] );
    if ( $marker->fetchColumn() === 'done' ) {
        echo "Portfolio page content already applied; left untouched.\n";
        return;
    }
} catch ( Throwable $error ) {
    // Older database without site_settings: carry on.
}

$copy = [
    'eyebrow'       => 'Our Portfolio',
    'section_title' => 'Delivering Projects, Creating Lasting Impact',
    'intro1'        => "Explore WRC's portfolio of humanitarian and development initiatives implemented across Pakistan in collaboration with government departments, institutional partners, and development organizations.",
    'intro2'        => "Our portfolio highlights our experience in healthcare service delivery, education and skills development, women's empowerment, humanitarian assistance, community development, and emergency response. Each project reflects our commitment to addressing community needs through practical interventions, effective coordination, and responsible implementation.",
];

try {
    $pdo->beginTransaction();

    $row = $pdo->query( 'SELECT * FROM projects_hero ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $row ) {
        $columns = [];
        $values  = [];
        foreach ( $copy as $column => $value ) {
            $columns[] = "`{$column}` = ?";
            $values[]  = $value;
        }
        $values[] = $row[ 'id' ];
        $pdo->prepare( 'UPDATE projects_hero SET ' . implode( ', ', $columns ) . ' WHERE id = ?' )->execute( $values );
    } else {
        $insert                   = $copy;
        $insert[ 'hero_image' ]   = 'assets/images/hero/projects-hero.jpg';
        $insert[ 'button1_text' ] = 'Active Projects';
        $insert[ 'button1_link' ] = '#active';
        $insert[ 'button2_text' ] = 'View Dashboard';
        $insert[ 'button2_link' ] = '#dashboard';
        $insert[ 'is_active' ]    = 1;

        $columns      = '`' . implode( '`, `', array_keys( $insert ) ) . '`';
        $placeholders = implode( ', ', array_fill( 0, count( $insert ), '?' ) );
        $pdo->prepare( "INSERT INTO projects_hero ({$columns}) VALUES ({$placeholders})" )
            ->execute( array_values( $insert ) );
    }

    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ WRC_PORTFOLIO_MARKER, 'done', 'system' ] );

    $pdo->commit();

    echo "Portfolio page prepared: hero eyebrow, headline, and introduction added.\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    throw $error;
}
