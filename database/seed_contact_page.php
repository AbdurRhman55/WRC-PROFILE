<?php
/**
 * One-off content pass for the Contact page.
 *
 *  - Sets the approved hero tagline ("Let's Connect and Make a Difference").
 *  - Adds the approved introduction (eyebrow, heading, three paragraphs)
 *    that appears directly below the hero.
 *
 * The copy is merged into the existing contact_content JSON, so offices,
 * phone numbers, the map, feedback categories, and whistleblower text are
 * left exactly as they are. Runs once, guarded by a site_settings marker, so
 * later edits made in the dashboard survive every deployment.
 *
 * Standalone:  php database/seed_contact_page.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key = 'contact_page_v1';
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_CONTACT_MARKER = 'contact_page_v1';

/** Create the tables and any missing columns without disturbing stored data. */
function wrc_contact_ensure_schema( PDO $pdo ): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `contact_hero` (
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
        "CREATE TABLE IF NOT EXISTS `contact_content` (
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

    foreach ( [
        'hero_image'       => "VARCHAR(500) DEFAULT ''",
        'section_title'    => "VARCHAR(255) DEFAULT ''",
        'section_subtitle' => 'TEXT NULL',
    ] as $column => $definition ) {
        if ( !$hasColumn( 'contact_hero', $column ) ) {
            $pdo->exec( "ALTER TABLE `contact_hero` ADD COLUMN `{$column}` {$definition}" );
        }
    }

    foreach ( [
        'section_title'    => "VARCHAR(255) DEFAULT ''",
        'section_subtitle' => 'TEXT NULL',
        'content'          => 'JSON NULL',
    ] as $column => $definition ) {
        if ( !$hasColumn( 'contact_content', $column ) ) {
            $pdo->exec( "ALTER TABLE `contact_content` ADD COLUMN `{$column}` {$definition}" );
        }
    }
}

try {
    wrc_contact_ensure_schema( $pdo );
} catch ( Throwable $error ) {
    fwrite( STDERR, 'Contact page schema check failed: ' . $error->getMessage() . "\n" );
    throw $error;
}

try {
    $marker = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
    $marker->execute( [ WRC_CONTACT_MARKER ] );
    if ( $marker->fetchColumn() === 'done' ) {
        echo "Contact page content already applied; left untouched.\n";
        return;
    }
} catch ( Throwable $error ) {
    // Older database without site_settings: carry on.
}

$heroTitle    = 'Contact Us';
$heroSubtitle = "Let's Connect and Make a Difference";

$introEyebrow = 'Get in Touch';
$introTitle   = "We're Here to Help";

$introOne   = 'We welcome communication from individuals, communities, government institutions, development partners, donors, and organizations interested in working with WRC.';
$introTwo   = 'Whether you have an inquiry, wish to explore a partnership, need information about our programmes, or would like to share feedback, our team is here to assist you.';
$introThree = 'We value open communication and aim to respond to all inquiries timely.';

try {
    $pdo->beginTransaction();

    // 1. Hero title + tagline.
    $heroRow = $pdo->query( 'SELECT id, hero_image FROM contact_hero ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $heroRow ) {
        $pdo->prepare( 'UPDATE contact_hero SET section_title = ?, section_subtitle = ? WHERE id = ?' )
            ->execute( [ $heroTitle, $heroSubtitle, $heroRow[ 'id' ] ] );
    } else {
        $pdo->prepare( 'INSERT INTO contact_hero (hero_image, section_title, section_subtitle, is_active) VALUES (?, ?, ?, 1)' )
            ->execute( [ 'assets/images/hero/contact-hero.jpg', $heroTitle, $heroSubtitle ] );
    }

    // 2. Merge the introduction into the existing contact content.
    $contentRow = $pdo->query( 'SELECT id, content FROM contact_content ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    $existing   = [];
    if ( $contentRow && !empty( $contentRow[ 'content' ] ) ) {
        $decoded = json_decode( $contentRow[ 'content' ], true );
        if ( is_array( $decoded ) ) {
            $existing = $decoded;
        }
    }

    $merged = array_merge( $existing, [
        'intro_eyebrow' => $introEyebrow,
        'intro_title'   => $introTitle,
        'intro1'        => $introOne,
        'intro2'        => $introTwo,
        'intro3'        => $introThree,
    ] );

    $json = json_encode( $merged, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    if ( $contentRow ) {
        $pdo->prepare( 'UPDATE contact_content SET content = ? WHERE id = ?' )
            ->execute( [ $json, $contentRow[ 'id' ] ] );
    } else {
        $pdo->prepare( 'INSERT INTO contact_content (section_title, section_subtitle, content, is_active) VALUES (?, ?, ?, 1)' )
            ->execute( [ 'Contact Information', 'Get in touch with our team', $json ] );
    }

    // 3. Remember that this pass has been applied.
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ WRC_CONTACT_MARKER, 'done', 'system' ] );

    $pdo->commit();

    echo "Contact page prepared: hero tagline and introduction added.\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    throw $error;
}
