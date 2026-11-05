<?php
/**
 * One-off content pass for the Who We Are page.
 *
 *  - Sets the two section introductions requested for "Our Areas of Impact"
 *    and "Why We Exist".
 *  - Replaces the three principle cards with the four agreed principles, in
 *    the wording supplied.
 *  - Gives each of the six impact sectors its own genuine WRC photograph,
 *    copied into assets/images/sectors/ so the dashboard can replace any of
 *    them later with a single upload.
 *
 * Runs once, guarded by a site_settings marker, so later edits made in the
 * dashboard are never overwritten by a deployment.
 *
 * Standalone:  php database/seed_about_sections.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key = 'about_sections_v1';
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_ABOUT_MARKER = 'about_sections_v1';

try {
    $marker = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
    $marker->execute( [ WRC_ABOUT_MARKER ] );
    if ( $marker->fetchColumn() === 'done' ) {
        echo "About-page content already applied; left untouched.\n";
        return;
    }
} catch ( Throwable $error ) {
    // Older database without site_settings: carry on.
}

$impactIntro = 'We take a comprehensive approach to community development, addressing immediate humanitarian needs while implementing sustainable solutions that strengthen communities and improve quality of life. Our work focuses on key sectors where strategic interventions can create meaningful and lasting impact.';

$whyIntro = 'We believe that meaningful development begins with recognizing the potential of every individual and creating an environment where communities can thrive. Our work is guided by principles that place people, dignity, accountability, and long-term progress at the Centre of every intervention.';

$principles = [
    [
        'icon'        => 'fa-solid fa-scale-balanced',
        'title'       => 'Equal Opportunities',
        'description' => 'We believe everyone deserves fair and equitable access to essential services, resources, and opportunities. We work to reduce barriers, address inequalities, and ensure that vulnerable and underserved communities are not left behind.',
    ],
    [
        'icon'        => 'fa-solid fa-seedling',
        'title'       => 'Sustainable Development',
        'description' => 'We focus on solutions that address the root causes of challenges rather than temporary needs alone. Through locally relevant initiatives, capacity building, and strategic partnerships, we aim to create lasting improvements that communities can sustain and build upon.',
    ],
    [
        'icon'        => 'fa-solid fa-hand-holding-heart',
        'title'       => 'Human Dignity',
        'description' => 'We believe every individual deserves to be treated with respect, compassion, and fairness. Our approach prioritizes dignity, inclusion, cultural sensitivity, and meaningful community participation in every stage of our work.',
    ],
    [
        'icon'        => 'fa-solid fa-shield-halved',
        'title'       => 'Transparency & Accountability',
        'description' => 'We are committed to responsible management of resources, transparent processes, and accountable implementation. Through effective oversight, monitoring, documentation, and reporting, we strive to build trust with the communities we serve, our partners, and stakeholders.',
    ],
];

// Each sector's photograph, keyed by the title already in the dashboard.
$sectorImages = [
    'Humanitarian Assistance' => 'assets/images/sectors/humanitarian-assistance.jpg',
    'Education'               => 'assets/images/sectors/education.jpg',
    'Health & Wellbeing'      => 'assets/images/sectors/health-wellbeing.jpg',
    'Community Development'   => 'assets/images/sectors/community-development.jpg',
    'Livelihoods'             => 'assets/images/sectors/livelihoods.jpg',
    'Emergency Response'      => 'assets/images/sectors/emergency-response.jpg',
];

try {
    $pdo->beginTransaction();

    if ( !isset( $tableExists ) ) {
        $tableExists = static function ( string $table ) use ( $pdo ): bool {
            $statement = $pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
            );
            $statement->execute( [ $table ] );
            return (int) $statement->fetchColumn() > 0;
        };
    }

    if ( !isset( $columnExists ) ) {
        $columnExists = static function ( string $table, string $column ) use ( $pdo ): bool {
            $statement = $pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?'
            );
            $statement->execute( [ $table, $column ] );
            return (int) $statement->fetchColumn() > 0;
        };
    }

    // The sector cards gained an image column with this change; add it here as
    // well so a stand-alone run on an older database still works.
    if ( $tableExists( 'who_we_are_impact_items' ) && !$columnExists( 'who_we_are_impact_items', 'image' ) ) {
        $pdo->exec( "ALTER TABLE `who_we_are_impact_items` ADD COLUMN `image` VARCHAR(500) NOT NULL DEFAULT '' AFTER `description`" );
        echo "Added who_we_are_impact_items.image.\n";
    }

    // 1. Section introductions.
    $impactRow = $pdo->query( 'SELECT id FROM who_we_are_impact ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $impactRow ) {
        $statement = $pdo->prepare( 'UPDATE who_we_are_impact SET section_subtitle = ? WHERE id = ?' );
        $statement->execute( [ $impactIntro, $impactRow['id'] ] );
    } else {
        $pdo->prepare( 'INSERT INTO who_we_are_impact (section_title, section_subtitle) VALUES (?, ?)' )
            ->execute( [ 'Our Areas of Impact', $impactIntro ] );
    }

    $whyRow = $pdo->query( 'SELECT id FROM why_we_exist ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $whyRow ) {
        $statement = $pdo->prepare( 'UPDATE why_we_exist SET section_subtitle = ? WHERE id = ?' );
        $statement->execute( [ $whyIntro, $whyRow['id'] ] );
    } else {
        $pdo->prepare( 'INSERT INTO why_we_exist (section_title, section_subtitle) VALUES (?, ?)' )
            ->execute( [ 'Why We Exist', $whyIntro ] );
        $whyRow = [ 'id' => (int) $pdo->lastInsertId() ];
    }

    // 2. The four principles, replacing whatever was there before.
    $pdo->prepare( 'DELETE FROM why_we_exist_items WHERE why_id = ?' )->execute( [ $whyRow['id'] ] );
    $insertPrinciple = $pdo->prepare(
        'INSERT INTO why_we_exist_items (why_id, icon, title, description, sort_order) VALUES (?, ?, ?, ?, ?)'
    );
    foreach ( $principles as $order => $principle ) {
        $insertPrinciple->execute( [
            $whyRow['id'],
            $principle['icon'],
            $principle['title'],
            $principle['description'],
            $order,
        ] );
    }

    // 3. A genuine WRC photograph for every sector.
    $sectors = $pdo->query( 'SELECT id, title, sort_order FROM who_we_are_impact_items ORDER BY sort_order ASC, id ASC' )
        ->fetchAll( PDO::FETCH_ASSOC );

    $byTitle = array_values( $sectorImages );
    $assigned = 0;

    foreach ( $sectors as $position => $sector ) {
        $image = $sectorImages[ $sector['title'] ] ?? ( $byTitle[ $position ] ?? '' );
        if ( $image === '' ) {
            continue;
        }
        $pdo->prepare( 'UPDATE who_we_are_impact_items SET image = ? WHERE id = ?' )
            ->execute( [ $image, $sector['id'] ] );
        $assigned++;
    }

    // 4. Remember that this pass has been applied.
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ WRC_ABOUT_MARKER, 'done', 'system' ] );

    $pdo->commit();

    echo "About page updated: 4 principles, {$assigned} sector photograph(s).\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    throw $error;
}
