<?php
/**
 * WRC - Registration page content
 *
 * The Resources page is a dedicated registration page: it carries the
 * organization's registration statement and nothing else. Two marker-guarded
 * passes keep deployments from overwriting dashboard edits:
 *
 *  v1  Sets the registration hero copy and tagline.
 *
 *  v2  Removes the certificate, stat, and button keys left over from the
 *      earlier resources layout, so the page renders a single registration
 *      section.
 *
 * Standalone:  php database/seed_registration_page.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key IN
 *              ('registration_page_v1','registration_page_v2');
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_REGISTRATION_MARKER = 'registration_page_v1';
const WRC_REGISTRATION_CLEANUP = 'registration_page_v2';

/** True when a content pass has already been applied. */
function wrc_registration_done( PDO $pdo, string $marker ): bool {
    try {
        $statement = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
        $statement->execute( [ $marker ] );
        return $statement->fetchColumn() === 'done';
    } catch ( Throwable $error ) {
        return false;
    }
}

/** Record a finished content pass. */
function wrc_registration_mark( PDO $pdo, string $marker ): void {
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ $marker, 'done', 'system' ] );
}

$applied = [];

// ---------------------------------------------------------------- pass v1 --
if ( !wrc_registration_done( $pdo, WRC_REGISTRATION_MARKER ) ) {
    $subtitle = 'Welfare & Relief Committee (WRC) is a registered organization committed to serving communities through humanitarian support and responsible development initiatives.';

    try {
        $pdo->beginTransaction();

        $row = $pdo->query( 'SELECT * FROM resources_hero ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );

        $stored = [];
        if ( $row && !empty( $row[ 'content' ] ) ) {
            $decoded = json_decode( (string) $row[ 'content' ], true );
            $stored  = is_array( $decoded ) ? $decoded : [];
        }

        $data = array_merge( $stored, [
            'title'    => 'Organizational Registration',
            'subtitle' => $subtitle,
            'tagline'  => 'Transparency & Accountability',
        ] );

        // Nothing from the old resources layout belongs on this page.
        foreach ( [ 'stats', 'cert_heading', 'cert_intro', 'button1_text', 'button1_link', 'button2_text', 'button2_link' ] as $legacyKey ) {
            unset( $data[ $legacyKey ] );
        }

        $json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

        if ( $row ) {
            $pdo->prepare(
                'UPDATE resources_hero SET section_title = ?, section_subtitle = ?, content = ? WHERE id = ?'
            )->execute( [ 'Organizational Registration', $subtitle, $json, $row[ 'id' ] ] );
        } else {
            $pdo->prepare(
                'INSERT INTO resources_hero (section_title, section_subtitle, hero_image, content, is_active)
                 VALUES (?, ?, ?, ?, 1)'
            )->execute( [ 'Organizational Registration', $subtitle, 'assets/images/hero/resources-hero.jpg', $json ] );
        }

        wrc_registration_mark( $pdo, WRC_REGISTRATION_MARKER );

        $pdo->commit();

        $applied[] = 'registration hero copy';
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

// ---------------------------------------------------------------- pass v2 --
if ( !wrc_registration_done( $pdo, WRC_REGISTRATION_CLEANUP ) ) {
    try {
        $pdo->beginTransaction();

        $row = $pdo->query( 'SELECT * FROM resources_hero ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );

        if ( $row && !empty( $row[ 'content' ] ) ) {
            $data = json_decode( (string) $row[ 'content' ], true );
            if ( is_array( $data ) ) {
                foreach ( [ 'stats', 'cert_heading', 'cert_intro', 'button1_text', 'button1_link', 'button2_text', 'button2_link' ] as $legacyKey ) {
                    unset( $data[ $legacyKey ] );
                }

                $pdo->prepare( 'UPDATE resources_hero SET content = ? WHERE id = ?' )
                    ->execute( [
                        json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
                        $row[ 'id' ],
                    ] );
            }
        }

        wrc_registration_mark( $pdo, WRC_REGISTRATION_CLEANUP );

        $pdo->commit();

        $applied[] = 'removed the leftover certificate, stat, and button content';
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

if ( $applied ) {
    echo 'Registration page prepared: ' . implode( '; ', $applied ) . ".\n";
} else {
    echo "Registration page content already applied; left untouched.\n";
}
