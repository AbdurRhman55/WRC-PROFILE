<?php
/**
 * Content passes for the Contact page.
 *
 *  v1  Sets the approved hero tagline ("Let's Connect and Make a Difference")
 *      and the three introduction paragraphs that now live inside the hero.
 *  v2  Sets the contact form wording ("Send Us a Message"), the Inquiry Type
 *      options, and trims Our Offices down to the head office.
 *
 * Both passes merge into the existing contact_content JSON, so phone numbers,
 * the map, feedback categories, and whistleblower text are preserved. Each
 * pass is guarded by its own site_settings marker, so later dashboard edits
 * survive every deployment.
 *
 * Standalone:  php database/seed_contact_page.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key IN ('contact_page_v1','contact_page_v2');
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_CONTACT_MARKER_V1 = 'contact_page_v1';
const WRC_CONTACT_MARKER_V2 = 'contact_page_v2';

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

    // Two extra details the contact form now collects.
    try {
        foreach ( [
            'organization'  => 'VARCHAR(255) NULL',
            'inquiry_label' => 'VARCHAR(100) NULL',
        ] as $column => $definition ) {
            if ( !$hasColumn( 'contact_submissions', $column ) ) {
                $pdo->exec( "ALTER TABLE `contact_submissions` ADD COLUMN `{$column}` {$definition}" );
            }
        }
    } catch ( Throwable $error ) {
        // Older installs without contact_submissions: the form falls back.
    }
}

/** Has this one-off pass already run? */
function wrc_contact_done( PDO $pdo, string $marker ): bool {
    try {
        $stmt = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
        $stmt->execute( [ $marker ] );
        return $stmt->fetchColumn() === 'done';
    } catch ( Throwable $error ) {
        return false;
    }
}

/** Remember that a pass has been applied. */
function wrc_contact_mark( PDO $pdo, string $marker ): void {
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ $marker, 'done', 'system' ] );
}

/** Read the stored contact_content JSON. */
function wrc_contact_read( PDO $pdo ): array {
    $row = $pdo->query( 'SELECT id, content FROM contact_content ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    $data = [];
    if ( $row && !empty( $row[ 'content' ] ) ) {
        $decoded = json_decode( $row[ 'content' ], true );
        if ( is_array( $decoded ) ) {
            $data = $decoded;
        }
    }
    return [ 'id' => $row[ 'id' ] ?? null, 'data' => $data ];
}

/** Merge new keys over the stored JSON. */
function wrc_contact_write( PDO $pdo, ?int $id, array $data ): void {
    $json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    if ( $id !== null ) {
        $pdo->prepare( 'UPDATE contact_content SET content = ? WHERE id = ?' )->execute( [ $json, $id ] );
    } else {
        $pdo->prepare( 'INSERT INTO contact_content (section_title, section_subtitle, content, is_active) VALUES (?, ?, ?, 1)' )
            ->execute( [ 'Contact Information', 'Get in touch with our team', $json ] );
    }
}

try {
    wrc_contact_ensure_schema( $pdo );
} catch ( Throwable $error ) {
    fwrite( STDERR, 'Contact page schema check failed: ' . $error->getMessage() . "\n" );
    throw $error;
}

$applied = [];

// ---------------------------------------------------------------- pass v1 --
if ( !wrc_contact_done( $pdo, WRC_CONTACT_MARKER_V1 ) ) {
    $heroTitle    = 'Contact Us';
    $heroSubtitle = "Let's Connect and Make a Difference";

    try {
        $pdo->beginTransaction();

        $heroRow = $pdo->query( 'SELECT id, hero_image FROM contact_hero ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
        if ( $heroRow ) {
            $pdo->prepare( 'UPDATE contact_hero SET section_title = ?, section_subtitle = ? WHERE id = ?' )
                ->execute( [ $heroTitle, $heroSubtitle, $heroRow[ 'id' ] ] );
        } else {
            $pdo->prepare( 'INSERT INTO contact_hero (hero_image, section_title, section_subtitle, is_active) VALUES (?, ?, ?, 1)' )
                ->execute( [ 'assets/images/hero/contact-hero.jpg', $heroTitle, $heroSubtitle ] );
        }

        $stored = wrc_contact_read( $pdo );
        $merged = array_merge( $stored[ 'data' ], [
            'intro1' => 'We welcome communication from individuals, communities, government institutions, development partners, donors, and organizations interested in working with WRC.',
            'intro2' => 'Whether you have an inquiry, wish to explore a partnership, need information about our programmes, or would like to share feedback, our team is here to assist you.',
            'intro3' => 'We value open communication and aim to respond to all inquiries timely.',
        ] );
        wrc_contact_write( $pdo, $stored[ 'id' ], $merged );

        wrc_contact_mark( $pdo, WRC_CONTACT_MARKER_V1 );
        $pdo->commit();
        $applied[] = 'hero tagline and introduction';
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

// ---------------------------------------------------------------- pass v2 --
if ( !wrc_contact_done( $pdo, WRC_CONTACT_MARKER_V2 ) ) {
    try {
        $pdo->beginTransaction();

        $stored = wrc_contact_read( $pdo );
        $data   = $stored[ 'data' ];

        // Keep the head office only; drop regional and field sample offices.
        $offices = array_values( array_filter( (array) ( $data[ 'offices' ] ?? [] ), 'is_array' ) );
        if ( !empty( $offices ) ) {
            $head = null;
            foreach ( $offices as $office ) {
                $name  = strtolower( (string) ( $office[ 'name' ] ?? '' ) );
                $badge = strtolower( (string) ( $office[ 'badge' ] ?? '' ) );
                if ( strpos( $name, 'head' ) !== false || $badge === 'main' ) {
                    $head = $office;
                    break;
                }
            }
            $data[ 'offices' ] = [ $head ?? $offices[0] ];
        }

        $data = array_merge( $data, [
            'form_subtitle'      => 'Contact Us',
            'form_title'         => 'Send Us a Message',
            'form_description'   => 'Have a question, need more information about our work, or wish to explore an opportunity to collaborate with WRC? We would be pleased to hear from you.',
            'form_note'          => 'Please complete the contact form, and our team will review your inquiry and get back to you as soon as possible.',
            'form_closing'       => "We value your communication and appreciate your interest in WRC's humanitarian and development initiatives.",
            'inquiry_types'      => [
                'General Inquiry',
                'Partnership & Collaboration',
                'Project / Programme Inquiry',
                'Donor & Funding Inquiry',
                'Feedback & Suggestions',
                'Complaint / Concern',
                'Other',
            ],
            'offices_subtitle'   => 'Visit Us',
            'offices_title'      => 'Our Office',
            'offices_description' => 'Our head office is open for inquiries, partnerships, and community engagement. Please call ahead so we can give you our full attention.',
        ] );

        wrc_contact_write( $pdo, $stored[ 'id' ], $data );

        wrc_contact_mark( $pdo, WRC_CONTACT_MARKER_V2 );
        $pdo->commit();
        $applied[] = 'contact form wording, inquiry types, and head office';
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

if ( $applied ) {
    echo 'Contact page prepared: ' . implode( '; ', $applied ) . ".\n";
} else {
    echo "Contact page content already applied; left untouched.\n";
}
