<?php
/**
 * One-off content pass for the Impact page.
 *
 *  - Sets the hero eyebrow ("Our Impact in Numbers") and headline
 *    ("Measuring Progress, Demonstrating Accountability").
 *  - Sets both approved hero paragraphs.
 *  - Adds the five "Impact at a Glance" indicators.
 *  - Replaces the old M&E steps with the approved five-step
 *    monitoring, evaluation, and accountability framework, plus its intro.
 *
 * Existing counters, SDG cards, and case studies are preserved. The pass is
 * guarded by a site_settings marker, so later dashboard edits survive every
 * deployment.
 *
 * Standalone:  php database/seed_impact_page.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key = 'impact_page_v1';
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_IMPACT_MARKER = 'impact_page_v1';

/** Add the columns and tables the impact page needs, without touching rows. */
function wrc_impact_ensure_schema( PDO $pdo ): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `impact_hero` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `hero_image` VARCHAR(500) DEFAULT '',
            `section_title` VARCHAR(255) DEFAULT '',
            `section_subtitle` VARCHAR(255) DEFAULT '',
            `heading` TEXT NULL,
            `description` TEXT NULL,
            `cta_primary` VARCHAR(255) DEFAULT '',
            `cta_secondary` VARCHAR(255) DEFAULT '',
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `impact_content` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `section_title` VARCHAR(255) DEFAULT '',
            `section_subtitle` VARCHAR(255) DEFAULT '',
            `content` LONGTEXT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $existing = [];
    foreach ( $pdo->query( 'SHOW COLUMNS FROM `impact_hero`' ) as $field ) {
        $existing[ $field[ 'Field' ] ?? '' ] = true;
    }

    foreach ( [
        'eyebrow' => 'VARCHAR(160) NULL',
        'intro2'  => 'TEXT NULL',
    ] as $column => $definition ) {
        if ( !isset( $existing[ $column ] ) ) {
            $pdo->exec( "ALTER TABLE `impact_hero` ADD COLUMN `{$column}` {$definition}" );
        }
    }
}

try {
    wrc_impact_ensure_schema( $pdo );
} catch ( Throwable $error ) {
    fwrite( STDERR, 'Impact page schema check failed: ' . $error->getMessage() . "\n" );
    throw $error;
}

try {
    $marker = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
    $marker->execute( [ WRC_IMPACT_MARKER ] );
    if ( $marker->fetchColumn() === 'done' ) {
        echo "Impact page content already applied; left untouched.\n";
        return;
    }
} catch ( Throwable $error ) {
    // Older database without site_settings: carry on.
}

$heroCopy = [
    'eyebrow'       => 'Our Impact in Numbers',
    'heading'       => 'Measuring Progress, Demonstrating Accountability',
    'description'   => 'WRC is committed to measuring the reach, effectiveness, and outcomes of its humanitarian and development initiatives. Through systematic monitoring, programme reporting, and performance assessment, we track progress, identify areas for improvement, and strengthen accountability to our partners, donors, and the communities we serve.',
    'intro2'        => 'Our impact indicators reflect the scale of our interventions, the services delivered, and the progress achieved across our programme sectors.',
    'cta_primary'   => 'Impact at a Glance',
    'cta_secondary' => 'SDG Contributions',
];

$indicators = [
    [
        'icon'  => 'fa-heart-pulse',
        'title' => 'Healthcare Services',
        'desc'  => 'Tracking access to essential healthcare, medical assistance, and community health interventions.',
    ],
    [
        'icon'  => 'fa-graduation-cap',
        'title' => 'Education & Skills Development',
        'desc'  => 'Measuring participation in educational programmes, vocational training, and capacity-building initiatives.',
    ],
    [
        'icon'  => 'fa-hand-holding-heart',
        'title' => 'Humanitarian Assistance',
        'desc'  => 'Monitoring households reached through food assistance, emergency relief, and essential support.',
    ],
    [
        'icon'  => 'fa-faucet-drip',
        'title' => 'Water, Sanitation & Hygiene',
        'desc'  => 'Assessing access to clean water and community-based WASH interventions.',
    ],
    [
        'icon'  => 'fa-people-roof',
        'title' => "Women's Empowerment",
        'desc'  => 'Tracking participation in vocational training, skills development, and livelihood initiatives.',
    ],
];

$mneIntro = 'WRC recognizes that effective programme delivery requires continuous monitoring, transparent reporting, and meaningful accountability. Our monitoring and evaluation approach focuses on tracking implementation progress, assessing programme performance, identifying challenges, and documenting results.';

$mneSteps = [
    [
        'label' => '01',
        'title' => 'Programme Monitoring',
        'desc'  => 'Regular monitoring of project activities, implementation progress, timelines, and deliverables against approved project plans.',
        'icon'  => 'fa-magnifying-glass-chart',
        'side'  => 'left',
    ],
    [
        'label' => '02',
        'title' => 'Data Collection & Verification',
        'desc'  => 'Systematic collection and review of programme information, beneficiary records, and supporting documentation to maintain reliable reporting.',
        'icon'  => 'fa-clipboard-check',
        'side'  => 'right',
    ],
    [
        'label' => '03',
        'title' => 'Quality Assurance',
        'desc'  => 'Review of implementation processes and service delivery to identify gaps, strengthen operational standards, and promote continuous improvement.',
        'icon'  => 'fa-shield-halved',
        'side'  => 'left',
    ],
    [
        'label' => '04',
        'title' => 'Community Feedback & Accountability',
        'desc'  => 'Encouraging feedback from beneficiaries and communities to understand their experiences, identify concerns, and improve programme responsiveness.',
        'icon'  => 'fa-comments',
        'side'  => 'right',
    ],
    [
        'label' => '05',
        'title' => 'Evaluation & Reporting',
        'desc'  => 'Periodic assessment of programme achievements, challenges, outputs, and outcomes to support evidence-based decisions and institutional learning.',
        'icon'  => 'fa-chart-line',
        'side'  => 'left',
    ],
];

try {
    $pdo->beginTransaction();

    // ---------------------------------------------------------------- hero --
    $row = $pdo->query( 'SELECT * FROM impact_hero ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $row ) {
        $columns = [];
        $values  = [];
        foreach ( $heroCopy as $column => $value ) {
            $columns[] = "`{$column}` = ?";
            $values[]  = $value;
        }
        $values[] = $row[ 'id' ];
        $pdo->prepare( 'UPDATE impact_hero SET ' . implode( ', ', $columns ) . ' WHERE id = ?' )->execute( $values );
    } else {
        $insert                    = $heroCopy;
        $insert[ 'hero_image' ]    = 'assets/images/hero/impact-hero.jpg';
        $insert[ 'section_title' ] = 'Our Impact in Numbers';
        $insert[ 'is_active' ]     = 1;

        $columns      = '`' . implode( '`, `', array_keys( $insert ) ) . '`';
        $placeholders = implode( ', ', array_fill( 0, count( $insert ), '?' ) );
        $pdo->prepare( "INSERT INTO impact_hero ({$columns}) VALUES ({$placeholders})" )
            ->execute( array_values( $insert ) );
    }

    // ------------------------------------------------------------- content --
    $contentRow = $pdo->query( 'SELECT * FROM impact_content ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    $data = [];
    if ( $contentRow ) {
        $decoded = json_decode( (string) ( $contentRow[ 'content' ] ?? '' ), true );
        $data    = is_array( $decoded ) ? $decoded : [];
    }

    $data[ 'indicators' ] = $indicators;
    $data[ 'mne_intro' ]  = $mneIntro;
    $data[ 'mne_steps' ]  = $mneSteps;

    $json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

    if ( $contentRow ) {
        $pdo->prepare( 'UPDATE impact_content SET content = ? WHERE id = ?' )
            ->execute( [ $json, $contentRow[ 'id' ] ] );
    } else {
        $pdo->prepare(
            'INSERT INTO impact_content (section_title, section_subtitle, content, is_active)
             VALUES (?, ?, ?, 1)'
        )->execute( [ 'Impact at a Glance', '', $json ] );
    }

    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ WRC_IMPACT_MARKER, 'done', 'system' ] );

    $pdo->commit();

    echo "Impact page prepared: hero copy, five indicators, and the accountability framework added.\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    throw $error;
}
