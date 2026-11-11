<?php
/**
 * WRC - Registration page content
 *
 * The Resources page is now a dedicated registration page: the public Policies
 * section is gone and only registration status and registration certificates
 * remain. This seed applies the registration-focused hero copy and certificate
 * section headings once, guarded by a site_settings marker so later dashboard
 * edits are never overwritten.
 *
 * Standalone:  php database/seed_registration_page.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key = 'registration_page_v1';
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_REGISTRATION_MARKER = 'registration_page_v1';

/** True when the content pass has already been applied. */
function wrc_registration_done( PDO $pdo, string $marker ): bool {
    try {
        $statement = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
        $statement->execute( [ $marker ] );
        return $statement->fetchColumn() === 'done';
    } catch ( Throwable $error ) {
        return false;
    }
}

if ( wrc_registration_done( $pdo, WRC_REGISTRATION_MARKER ) ) {
    echo "Registration page content already applied; left untouched.\n";
    return;
}

$subtitle = 'Welfare & Relief Committee (WRC) is a registered organization committed to serving communities through humanitarian support and responsible development initiatives.';

$certificateCount = 0;
try {
    $certificateCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM resources WHERE resource_type = 'download' AND is_active = 1"
    )->fetchColumn();
} catch ( Throwable $error ) {
    // Older database without the resources table: keep the fallback.
}

$copy = [
    'title'        => 'Organizational Registration',
    'subtitle'     => $subtitle,
    'tagline'      => 'Transparency & Accountability',
    'cert_heading' => 'Registration Certificates',
    'cert_intro'   => 'Download WRC’s official registration certificates and regulatory documents issued by the relevant authorities.',
    'stats'        => [
        [ 'label' => 'Registration Certificates', 'value' => max( 1, $certificateCount ) . '+', 'icon' => 'fa-file-shield' ],
        [ 'label' => 'Registered Organization', 'value' => 'WRC', 'icon' => 'fa-stamp' ],
        [ 'label' => 'Legal Status', 'value' => 'Registered', 'icon' => 'fa-circle-check' ],
    ],
];

try {
    $pdo->beginTransaction();

    $row = $pdo->query( 'SELECT * FROM resources_hero ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );

    $stored = [];
    if ( $row && !empty( $row[ 'content' ] ) ) {
        $decoded = json_decode( (string) $row[ 'content' ], true );
        $stored  = is_array( $decoded ) ? $decoded : [];
    }

    $data = array_merge( $stored, $copy );

    // Old resources-era buttons no longer apply.
    $data[ 'button1_text' ] = 'Registration Status';
    $data[ 'button1_link' ] = '#registration';
    $data[ 'button2_text' ] = 'Registration Certificates';
    $data[ 'button2_link' ] = '#certificates';

    $json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

    if ( $row ) {
        $pdo->prepare(
            'UPDATE resources_hero SET section_title = ?, section_subtitle = ?, content = ? WHERE id = ?'
        )->execute( [ $copy[ 'title' ], $copy[ 'subtitle' ], $json, $row[ 'id' ] ] );
    } else {
        $pdo->prepare(
            'INSERT INTO resources_hero (section_title, section_subtitle, hero_image, content, is_active)
             VALUES (?, ?, ?, ?, 1)'
        )->execute( [ $copy[ 'title' ], $copy[ 'subtitle' ], 'assets/images/hero/resources-hero.jpg', $json ] );
    }

    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ WRC_REGISTRATION_MARKER, 'done', 'system' ] );

    $pdo->commit();

    echo "Registration page prepared: hero copy, registration stats, and certificate headings added.\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    throw $error;
}
