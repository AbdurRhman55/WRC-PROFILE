<?php
/**
 * WRC - Canonical beneficiary figures.
 *
 * The `projects` table is the source of truth. Sector totals are derived from
 * the project categories (Health + Emergency Response, Education + Livelihood,
 * Food Security, WASH) and the headline total is every active project combined.
 * Editing or adding a project in the dashboard therefore updates every page
 * automatically.
 *
 * This script has two jobs:
 *   1. A one-off content pass (marker-guarded) that makes sure the homepage
 *      beneficiary strips and the Impact page baseline content exist.
 *   2. An always-on sync that re-publishes the live project figures into every
 *      dashboard-managed table (see wrcSyncBeneficiaryStats()).
 *
 * Standalone:  php database/seed_beneficiary_figures.php
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}
require_once dirname( __DIR__ ) . '/includes/functions.php';

const WRC_FIGURES_MARKER = 'beneficiary_figures_v1';

function wrc_figures_done( PDO $pdo, string $marker ): bool {
    try {
        $statement = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
        $statement->execute( [ $marker ] );
        return $statement->fetchColumn() === 'done';
    } catch ( Throwable $error ) {
        return false;
    }
}

function wrc_figures_mark( PDO $pdo, string $marker ): void {
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute( [ $marker, 'done', 'system' ] );
}

$oneTimeSetupDone = wrc_figures_done( $pdo, WRC_FIGURES_MARKER );

if ( ! $oneTimeSetupDone ) {

$figures = wrcBeneficiaryFigures();
$total   = number_format( $figures['total'] );

$canonicalStats = [
    ['icon' => 'fa-solid fa-users', 'color' => 'brandRed', 'target' => (string) $figures['total'], 'label' => 'Beneficiaries Reached', 'sub' => 'All programme sectors combined'],
    [ 'icon' => 'fa-solid fa-heart-pulse', 'color' => 'green', 'target' => (string) $figures['health'], 'label' => 'Health', 'sub' => 'Medical camps, clinics & emergency care' ],
    [ 'icon' => 'fa-solid fa-graduation-cap', 'color' => 'purple', 'target' => (string) $figures['skills'], 'label' => 'Skills', 'sub' => 'Vocational & livelihood training' ],
    [ 'icon' => 'fa-solid fa-bowl-food', 'color' => 'amber', 'target' => (string) $figures['food'], 'label' => 'Food', 'sub' => 'Emergency food & nutrition support' ],
    [ 'icon' => 'fa-solid fa-faucet-drip', 'color' => 'blue', 'target' => (string) $figures['wash'], 'label' => 'WASH', 'sub' => 'Clean water, sanitation & hygiene' ],
    [ 'icon' => 'fa-solid fa-map-location-dot', 'color' => 'teal', 'target' => '4', 'label' => 'Provinces Covered', 'sub' => 'Nationwide coverage' ],
];

$canonicalSdgs = [
    [
        'key'    => 'health',
        'number' => '3',
        'title'  => 'Good Health & Well-Being',
        'desc'   => 'WRC runs free medical camps, maternal and child health services, emergency treatment and community health programmes that bring essential care closer to underserved families.',
        'color'  => 'green-600',
        'bg'     => 'green',
        'tags'   => [ 'Health & emergency care', '4 provinces covered' ],
        'icon'   => 'fa-heart-pulse',
    ],
    [
        'key'    => 'skills',
        'number' => '8',
        'title'  => 'Decent Work & Economic Growth',
        'desc'   => 'Vocational training, enterprise support and livelihood opportunities help young people and women earn a stable income and build stronger local economies.',
        'color'  => 'red-600',
        'bg'     => 'red',
        'tags'   => [ 'Skills & vocational training', 'Women & youth livelihoods' ],
        'icon'   => 'fa-briefcase',
    ],
    [
        'key'    => 'food',
        'number' => '2',
        'title'  => 'Zero Hunger',
        'desc'   => 'Emergency food distributions, nutrition support and food-security assistance protect families facing hunger, seasonal shortages and displacement.',
        'color'  => 'amber-600',
        'bg'     => 'amber',
        'tags'   => [ 'Emergency food relief', 'Nutrition support' ],
        'icon'   => 'fa-bowl-food',
    ],
    [
        'key'    => 'wash',
        'number' => '6',
        'title'  => 'Clean Water & Sanitation',
        'desc'   => 'Clean water systems, sanitation improvements and hygiene promotion reduce waterborne disease and improve health in rural and disaster-affected communities.',
        'color'  => 'blue-600',
        'bg'     => 'blue',
        'tags'   => [ 'Clean water access', 'Sanitation & hygiene' ],
        'icon'   => 'fa-faucet-drip',
    ],
];

$sdgIntro1 = 'WRC aligns its humanitarian and development initiatives with the United Nations Sustainable Development Goals (SDGs). Our programmes contribute directly to SDG 3 (Good Health & Well-Being), SDG 8 (Decent Work & Economic Growth), SDG 2 (Zero Hunger) and SDG 6 (Clean Water & Sanitation), with every beneficiary counted once across these four sectors.';
$sdgIntro2 = 'Our programme activities are mapped against relevant SDG targets to demonstrate their contribution and identify opportunities for greater impact.';

/**
 * Replace every known stale beneficiary label/value pair inside a stats array.
 * Labels are matched, so unrelated metrics (budget, partners, districts) stay.
 */
function wrc_figures_normalise_stats( array $stats, array $figures ): array {
    $labels = [
        'lives impacted'          => [ number_format( $figures['total'] ), 'Beneficiaries Reached' ],
        'total beneficiaries'     => [ number_format( $figures['total'] ), 'Total Beneficiaries' ],
        'beneficiaries reached'   => [ number_format( $figures['total'] ), 'Beneficiaries Reached' ],
        'people reached'          => [ number_format( $figures['total'] ), 'Beneficiaries Reached' ],
        'healthcare visits'       => [ number_format( $figures['health'] ), 'Health Beneficiaries' ],
        'water wells installed'   => [ number_format( $figures['wash'] ), 'WASH Beneficiaries' ],
        'food packages distributed' => [ number_format( $figures['food'] ), 'Food Beneficiaries' ],
        'children enrolled'       => [ number_format( $figures['skills'] ), 'Skills Beneficiaries' ],
    ];

    foreach ( $stats as &$stat ) {
        if ( !is_array( $stat ) ) {
            continue;
        }
        $label = strtolower( trim( (string) ( $stat['label'] ?? $stat['stat_label'] ?? '' ) ) );
        if ( isset( $labels[ $label ] ) ) {
            [ $value, $newLabel ] = $labels[ $label ];
            if ( isset( $stat['value' ] ) ) {
                $stat['value'] = $value;
            }
            if ( isset( $stat['stat_value'] ) ) {
                $stat['stat_value'] = $value;
            }
            if ( isset( $stat['label'] ) ) {
                $stat['label'] = $newLabel;
            }
            if ( isset( $stat['stat_label'] ) ) {
                $stat['stat_label'] = $newLabel;
            }
        }
    }
    unset( $stat );

    return $stats;
}

$applied = [];

try {
    $pdo->beginTransaction();

    // --------------------------------------------------------- Impact page --
    $contentRow = $pdo->query( 'SELECT * FROM impact_content ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    $data = [];
    if ( $contentRow ) {
        $decoded = json_decode( (string) ( $contentRow['content'] ?? '' ), true );
        $data    = is_array( $decoded ) ? $decoded : [];
    }

    $data['stats']      = $canonicalStats;
    $data['sdgs']       = $canonicalSdgs;
    $data['sdg_intro1'] = $sdgIntro1;
    $data['sdg_intro2'] = $sdgIntro2;

    $json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

    if ( $contentRow ) {
        $pdo->prepare( 'UPDATE impact_content SET content = ? WHERE id = ?' )
            ->execute( [ $json, $contentRow['id'] ] );
    } else {
        $pdo->prepare(
            'INSERT INTO impact_content (section_title, section_subtitle, content, is_active)
             VALUES (?, ?, ?, 1)'
        )->execute( [ 'Impact at a Glance', '', $json ] );
    }
    $applied[] = 'Impact page counters and SDG cards';

    // --------------------------------------------- impact_stats (all pages) --
    $statMap = [
        'lives impacted'            => [ number_format( $figures['total'] ), 'Beneficiaries Reached', 'All programme sectors combined', 'fa-solid fa-users', 'brandRed' ],
        'total beneficiaries'       => [ number_format( $figures['total'] ), 'Beneficiaries Reached', 'All programme sectors combined', 'fa-solid fa-users', 'brandRed' ],
        'people reached'            => [ number_format( $figures['total'] ), 'Beneficiaries Reached', 'All programme sectors combined', 'fa-solid fa-users', 'brandRed' ],
        'healthcare visits'         => [ number_format( $figures['health'] ), 'Health Beneficiaries', 'Medical camps, clinics & emergency care', 'fa-solid fa-heart-pulse', 'green' ],
        'water wells installed'     => [ number_format( $figures['wash'] ), 'WASH Beneficiaries', 'Clean water, sanitation & hygiene', 'fa-solid fa-faucet-drip', 'blue' ],
        'food packages distributed' => [ number_format( $figures['food'] ), 'Food Beneficiaries', 'Emergency food & nutrition support', 'fa-solid fa-bowl-food', 'amber' ],
        'children enrolled'         => [ number_format( $figures['skills'] ), 'Skills Beneficiaries', 'Vocational & livelihood training', 'fa-solid fa-graduation-cap', 'purple' ],
        'communities served'        => [ '4', 'Provinces Covered', 'Nationwide coverage', 'fa-solid fa-map-location-dot', 'teal' ],
    ];

    $statRows = $pdo->query( 'SELECT id, stat_label FROM impact_stats' )->fetchAll( PDO::FETCH_ASSOC );
    foreach ( $statRows as $row ) {
        $label = strtolower( trim( (string) $row['stat_label'] ) );
        if ( !isset( $statMap[ $label ] ) ) {
            continue;
        }
        [ $value, $newLabel, $description, $icon, $color ] = $statMap[ $label ];
        $pdo->prepare(
            'UPDATE impact_stats
                SET stat_value = ?, stat_label = ?, description = ?, icon_class = ?, color = ?
              WHERE id = ?'
        )->execute( [ $value, $newLabel, $description, $icon, $color, $row['id'] ] );
    }
    $applied[] = 'impact stat tables';

    // ----------------------------------------- geographic presence stats ----
    $pdo->prepare(
        "UPDATE geographic_presence_stats
            SET stat_value = ?, stat_label = ?
          WHERE stat_label IN ('Lives Impacted', 'People Reached', 'Beneficiaries Reached')"
    )->execute( [ $total, 'Beneficiaries Reached' ] );

    // -------------------------------------------------- history + partners --
    $pdo->prepare(
        "UPDATE history_stats_items
            SET stat_value = ?, stat_label = 'Beneficiaries Reached'
          WHERE stat_label IN ('Lives Impacted', 'Beneficiaries Reached')"
    )->execute( [ $total ] );

    $pdo->prepare(
        "UPDATE contribute_why_stats
            SET stat_value = ?, stat_label = 'Beneficiaries Reached'
          WHERE stat_label IN ('Lives Impacted', 'Beneficiaries Reached')"
    )->execute( [ $total ] );

    // About page transparency strip (450K+ lives impacted).
    $pdo->prepare(
        "UPDATE about_transparency_stats
            SET stat_value = ?, stat_label = 'Beneficiaries Reached'
          WHERE stat_label IN ('Lives Impacted', 'Beneficiaries Reached')"
    )->execute( [ $total ] );

    $pdo->prepare(
        "UPDATE projects_dashboard_metrics SET metric_value = ? WHERE metric_label = 'Total Beneficiaries'"
    )->execute( [ $total ] );

    // Legacy SDG contribution copy (not currently printed on the site, kept in
    // step so no dashboard or future template can surface old numbers).
    $sdgCopy = [
        1 => number_format( $figures['health'] ) . ' health beneficiaries reached through free medical camps, clinical services and emergency care across 4 provinces.',
        2 => number_format( $figures['skills'] ) . ' skills beneficiaries trained through education, vocational and livelihood programmes.',
        3 => number_format( $figures['wash'] ) . ' WASH beneficiaries reached with clean water, sanitation and hygiene support.',
        4 => number_format( $figures['food'] ) . ' food beneficiaries reached through emergency food relief and nutrition support.',
    ];
    foreach ( $sdgCopy as $sdgId => $text ) {
        $pdo->prepare( 'UPDATE sdg_contributions SET contribution_text = ? WHERE id = ?' )
            ->execute( [ $text, $sdgId ] );
    }
    $applied[] = 'geographic, history and partner stats';

    // ------------------------------------------------------------ provinces --
    // The province cards on the homepage must add up to the published total.
    // Keep the original relative distribution and scale it to the canonical
    // figure, giving any rounding remainder to the smallest province.
    $provinceRows = $pdo->query( 'SELECT id, beneficiaries_count FROM provinces ORDER BY sort_order ASC' )->fetchAll( PDO::FETCH_ASSOC );
    $provinceSum  = array_sum( array_map( 'intval', array_column( $provinceRows, 'beneficiaries_count' ) ) );
    if ( $provinceRows && $provinceSum > 0 && $provinceSum !== $figures['total'] ) {
        $allocated = 0;
        $last      = count( $provinceRows ) - 1;
        foreach ( $provinceRows as $index => $province ) {
            if ( $index === $last ) {
                $scaled = $figures['total'] - $allocated;
            } else {
                $scaled     = (int) round( $figures['total'] * (int) $province['beneficiaries_count'] / $provinceSum );
                $allocated += $scaled;
            }
            $pdo->prepare( 'UPDATE provinces SET beneficiaries_count = ? WHERE id = ?' )
                ->execute( [ $scaled, $province['id'] ] );
        }
        $applied[] = 'province beneficiary split';
    }

    // ------------------------------------------ where-we-work content table --
    $wwRow = $pdo->query( 'SELECT * FROM where_we_work_content ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $wwRow && !empty( $wwRow['content'] ) ) {
        $ww = json_decode( (string) $wwRow['content'], true );
        if ( is_array( $ww ) ) {
            if ( isset( $ww['stats']['items'] ) && is_array( $ww['stats']['items'] ) ) {
                $ww['stats']['items'] = wrc_figures_normalise_stats( $ww['stats']['items'], $figures );
            } elseif ( isset( $ww['stats'] ) && is_array( $ww['stats'] ) ) {
                $ww['stats'] = wrc_figures_normalise_stats( $ww['stats'], $figures );
            }
            $pdo->prepare( 'UPDATE where_we_work_content SET content = ? WHERE id = ?' )
                ->execute( [ json_encode( $ww, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $wwRow['id'] ] );
        }
    }

    // ---------------------------------------------- homepage_sections rows ---
    $homeSections = $pdo->query( 'SELECT id, section_key, content, section_subtitle FROM homepage_sections' )->fetchAll( PDO::FETCH_ASSOC );
    foreach ( $homeSections as $section ) {
        $key     = $section['section_key'] ?? '';
        $changed = false;
        $updated = $section;

        if ( $key === 'history_hero' ) {
            if ( !empty( $section['section_subtitle'] ) ) {
                $subtitle = str_ireplace( 'over 150,000 lives', 'over ' . $total . ' people', $section['section_subtitle'] );
                if ( $subtitle !== $section['section_subtitle'] ) {
                    $updated['section_subtitle'] = $subtitle;
                    $changed = true;
                }
            }
        }

        if ( !empty( $section['content'] ) ) {
            $content = json_decode( (string) $section['content'], true );
            if ( is_array( $content ) ) {
                if ( isset( $content['stats'] ) && is_array( $content['stats'] ) ) {
                    $before = $content['stats'];
                    $content['stats'] = wrc_figures_normalise_stats( $content['stats'], $figures );
                    if ( $content['stats'] !== $before ) {
                        $changed = true;
                    }
                }
                if ( isset( $content['metrics'] ) && is_array( $content['metrics'] ) ) {
                    $before = $content['metrics'];
                    $content['metrics'] = wrc_figures_normalise_stats( $content['metrics'], $figures );
                    if ( $content['metrics'] !== $before ) {
                        $changed = true;
                    }
                }
                if ( isset( $content['featured']['stats'] ) && is_array( $content['featured']['stats'] ) ) {
                    $before = $content['featured']['stats'];
                    $content['featured']['stats'] = wrc_figures_normalise_stats( $content['featured']['stats'], $figures );
                    if ( $content['featured']['stats'] !== $before ) {
                        $changed = true;
                    }
                }

                // History hero keeps its subtitle inside the JSON payload.
                if ( $key === 'history_hero' && !empty( $content['subtitle'] ) ) {
                    $subtitle = str_ireplace(
                        [ 'over 150,000 lives', 'over 150,000 people' ],
                        'over ' . $total . ' people',
                        (string) $content['subtitle']
                    );
                    if ( $subtitle !== $content['subtitle'] ) {
                        $content['subtitle'] = $subtitle;
                        $changed = true;
                    }
                }

                if ( $changed ) {
                    $updated['content'] = json_encode( $content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                }
            }
        }

        if ( $changed ) {
            $pdo->prepare( 'UPDATE homepage_sections SET content = ?, section_subtitle = ? WHERE id = ?' )
                ->execute( [ $updated['content'], $updated['section_subtitle'], $section['id'] ] );
        }
    }
    $applied[] = 'homepage section metrics';

    // ------------------------------------------- homepage impact strips ------
    // The homepage renders two impact_stat bands (a light beneficiary strip and
    // the dark KPI dashboard). Fresh installs shipped without those rows, which
    // left both bands empty. Seed them once from the canonical figures; later
    // dashboard edits survive because this only runs when a strip is entirely
    // empty.
    $insertStat = $pdo->prepare(
        'INSERT INTO impact_stats (stat_value, stat_label, description, icon_class, color, sort_order, display_page)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $hasHero = (int) $pdo->query( "SELECT COUNT(*) FROM impact_stats WHERE display_page = 'home_hero'" )->fetchColumn();
    if ( $hasHero === 0 ) {
        $heroRows = [
            [ number_format( $figures['total'] ),  'Beneficiaries Reached', 'All programme sectors combined',      'fa-solid fa-users',        'brandRed', 1 ],
            [ number_format( $figures['health'] ), 'Health Beneficiaries',  'Medical camps, clinics & emergency care', 'fa-solid fa-heart-pulse',  'green',    2 ],
            [ number_format( $figures['skills'] ), 'Skills Beneficiaries',  'Vocational & livelihood training',    'fa-solid fa-graduation-cap', 'purple',  3 ],
            [ number_format( $figures['food'] ),   'Food Beneficiaries',    'Emergency food & nutrition support',  'fa-solid fa-bowl-food',    'amber',    4 ],
            [ number_format( $figures['wash'] ),   'WASH Beneficiaries',    'Clean water, sanitation & hygiene',   'fa-solid fa-faucet-drip',  'blue',     5 ],
        ];
        foreach ( $heroRows as $row ) {
            $insertStat->execute( array_merge( $row, [ 'home_hero' ] ) );
        }
        $applied[] = 'homepage beneficiary strip';
    }

    $hasKpi = (int) $pdo->query( "SELECT COUNT(*) FROM impact_stats WHERE display_page = 'home_kpi'" )->fetchColumn();
    if ( $hasKpi === 0 ) {
        $projectCount  = (int) $pdo->query( 'SELECT COUNT(*) FROM projects WHERE is_active = 1' )->fetchColumn();
        $sectorCount   = (int) $pdo->query( "SELECT COUNT(DISTINCT category) FROM projects WHERE is_active = 1 AND category IS NOT NULL AND category <> ''" )->fetchColumn();
        $provinceCount = (int) $pdo->query( 'SELECT COUNT(*) FROM provinces WHERE is_active = 1' )->fetchColumn();

        $kpiRows = [
            [ number_format( $figures['total'] ), 'Beneficiaries Reached', 'Across all programme sectors',        'fa-solid fa-users',           'brandRed', 1 ],
            [ (string) $projectCount,             'Projects Delivered',    'Active and completed projects',       'fa-solid fa-diagram-project', 'teal',     2 ],
            [ (string) $provinceCount,            'Provinces Covered',     'Nationwide coverage',                 'fa-solid fa-map-location-dot','blue',     3 ],
            [ (string) $sectorCount,              'Programme Sectors',     'Health, skills, food, WASH and more', 'fa-solid fa-layer-group',     'amber',    4 ],
        ];
        foreach ( $kpiRows as $row ) {
            $insertStat->execute( array_merge( $row, [ 'home_kpi' ] ) );
        }
        $applied[] = 'homepage KPI dashboard';
    }

    // ------------------------------------------------ annual impact article --
    $article = $pdo->query( 'SELECT id, title FROM news_articles WHERE id = 8 LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
    if ( $article && strpos( (string) $article['title'], '420,000' ) !== false ) {
        $newTitle = 'WRC Releases 2025 Annual Impact Report: ' . $total . ' Beneficiaries Reached';
        $newBody  = '<p>WRC\'s annual impact report for 2025 confirms that <strong>' . $total . ' beneficiaries</strong> were reached through integrated development interventions across Pakistan.</p>'
            . '<h2>Key Highlights</h2><ul>'
            . '<li>' . $total . ' beneficiaries reached across all programme sectors</li>'
            . '<li>Health programmes serving ' . number_format( $figures['health'] ) . ' beneficiaries</li>'
            . '<li>Skills and livelihood initiatives reaching ' . number_format( $figures['skills'] ) . ' beneficiaries</li>'
            . '<li>Food security assistance reaching ' . number_format( $figures['food'] ) . ' beneficiaries</li>'
            . '<li>WASH projects reaching ' . number_format( $figures['wash'] ) . ' beneficiaries</li>'
            . '</ul><p>The report demonstrates WRC\'s commitment to transparency and accountability, with detailed financial breakdowns and programme outcome measurements. Independent evaluations confirm the effectiveness of our integrated approach to community development.</p>';
        $pdo->prepare( 'UPDATE news_articles SET title = ?, content = ? WHERE id = ?' )
            ->execute( [ $newTitle, $newBody, $article['id'] ] );
        $applied[] = 'annual impact article figures';
    }

    wrc_figures_mark( $pdo, WRC_FIGURES_MARKER );

    $pdo->commit();

    echo 'Beneficiary figures published: ' . implode( '; ', $applied ) . ".\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    throw $error;
}
}

// Always refresh every dashboard-managed copy from the live projects table,
// whether or not the one-time setup above was needed.
$syncApplied = wrcSyncBeneficiaryStats( $pdo );

echo 'Beneficiary figures published: '
    . ( $syncApplied ? implode( '; ', $syncApplied ) : 'already up to date' )
    . ".\n";
