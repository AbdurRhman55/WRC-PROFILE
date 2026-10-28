<?php
/**
 * WRC - CLI project portfolio importer (deployment step).
 *
 * Applies database/replace_projects_from_wrc_excel.sql - the authoritative list of
 * approved projects - to the live database using the site's existing DB config.
 *
 * The script is idempotent and safe to run on every deployment:
 *   - When the live portfolio already matches the approved list it changes nothing.
 *   - Otherwise it snapshots the current rows into timestamped backup tables,
 *     applies the import inside a single transaction and only commits once the
 *     result has been verified.
 *   - Older backup tables are pruned automatically so repeated deployments can
 *     never accumulate tables or block the pipeline.
 *
 * Run by the deployment SSH step: php database/run_project_import.php
 */

// The deployment host may run with display_errors disabled, which hides fatal
// errors completely. Force them into the CI log so a broken environment is
// diagnosable from the workflow output alone.
ini_set( 'display_errors', '1' );
error_reporting( E_ALL );

if ( PHP_SAPI !== 'cli' ) {
    http_response_code( 404 );
    exit;
}

$configFile = dirname( __DIR__ ) . '/includes/config.php';
if ( !is_file( $configFile ) ) {
    fwrite( STDERR, "Project import failed: the site database configuration is missing (includes/config.php).\n" );
    fwrite( STDERR, "That file is intentionally not tracked by git, so it must already exist in public_html/includes/.\n" );
    exit( 1 );
}

try {
    require_once $configFile;
} catch ( Throwable $error ) {
    fwrite( STDERR, 'Project import failed: could not load includes/config.php: ' . $error->getMessage() . "\n" );
    fwrite( STDERR, "Check that the file, and anything it requires, parses under the server's PHP version.\n" );
    exit( 1 );
}

global $pdo;

if ( !isset( $pdo ) || ! ( $pdo instanceof PDO ) ) {
    fwrite( STDERR, "Project import failed: includes/config.php did not establish a database connection.\n" );
    exit( 1 );
}

/** Number of projects the approved portfolio must contain. */
const PORTFOLIO_SIZE = 20;

/** Backup tables are named: <table>_excel_backup_<YYYYMMDD_HHMMSS>. */
const BACKUP_SUFFIX = '_excel_backup_';

/** How many backup snapshots to keep on the server. */
const BACKUP_KEEP = 5;

$expectedSlugs = [
    'kp-tevta-pc1-paramedic-training-nmd-2021',
    'kp-tevta-nmd-paramedic-training-2021',
    'kp-tevta-nmd-paramedic-training-2022',
    'kp-tevta-skills-training-nmd-2023',
    'fata-da-paramedic-training-nmd-2016-2017',
    'fata-da-paramedic-training-nmd-2016-2018',
    'fata-da-paramedic-training-nmd-2018-2021',
    'islamic-relief-women-skills-khyber-2023',
    'islamic-relief-women-skills-islamabad-rawalpindi-2024',
    'islamic-relief-women-skills-islamabad-rawalpindi-2024-2',
    'health-department-rhc-peshawar-2011-2013',
    'health-department-cat-d-hospital-malakand-2013-2024',
    'health-department-rhc-swabi-2011-2024',
    'smart-aid-emergency-response-kits-malakand-swat-2023',
    'tdf-ramzan-food-distribution-khyber-2021',
    'tdf-ramzan-food-distribution-khyber-2022',
    'muslim-aid-hand-pump-mansehra-2016-2017',
    'phhsa-nfi-kits-di-khan-2022',
    'islamic-relief-vocational-skills-islamabad-rawalpindi-2025',
    'zfa-ramadan-food-packs-buner-swabi-nowshera-2026',
];
sort( $expectedSlugs );

$tableExists = static function ( string $table ) use ( $pdo ): bool {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
    );
    $stmt->execute( [ $table ] );
    return (int) $stmt->fetchColumn() > 0;
};

$columnExists = static function ( string $table, string $column ) use ( $pdo ): bool {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?'
    );
    $stmt->execute( [ $table, $column ] );
    return (int) $stmt->fetchColumn() > 0;
};

/**
 * Returns the list of reasons why the live portfolio is not yet the approved one.
 * An empty array means the live data already matches.
 */
$portfolioStatus = static function () use ( $pdo, $expectedSlugs ): array {
    $slugs = $pdo->query( 'SELECT slug FROM projects ORDER BY slug' )->fetchAll( PDO::FETCH_COLUMN );
    sort( $slugs );

    $activeCount  = (int) $pdo->query( 'SELECT COUNT(*) FROM projects WHERE is_active = 1' )->fetchColumn();
    $pricedCount  = (int) $pdo->query( 'SELECT COUNT(*) FROM projects WHERE budget_amount IS NOT NULL' )->fetchColumn();
    $partnerCount = (int) $pdo->query( 'SELECT COUNT(*) FROM project_donors' )->fetchColumn();

    $reasons = [];

    if ( $slugs !== $expectedSlugs ) {
        $missing = array_values( array_diff( $expectedSlugs, $slugs ) );
        $extra   = array_values( array_diff( $slugs, $expectedSlugs ) );

        if ( $missing ) {
            $reasons[] = count( $missing ) . ' approved project(s) missing (first: ' . $missing[ 0 ] . ')';
        }
        if ( $extra ) {
            $reasons[] = count( $extra ) . ' unapproved project(s) present (first: ' . $extra[ 0 ] . ')';
        }
        if ( !$missing && !$extra ) {
            $reasons[] = 'project slugs do not match the approved list exactly';
        }
    }

    if ( $activeCount !== PORTFOLIO_SIZE ) {
        $reasons[] = "active project count is {$activeCount}, expected " . PORTFOLIO_SIZE;
    }
    if ( $pricedCount !== 0 ) {
        $reasons[] = "{$pricedCount} project(s) still carry a budget_amount";
    }
    if ( $partnerCount !== PORTFOLIO_SIZE ) {
        $reasons[] = "project partner count is {$partnerCount}, expected " . PORTFOLIO_SIZE;
    }

    return $reasons;
};

/** Groups existing backup tables by their timestamp, newest first. */
$backupGroups = static function ( string $base ) use ( $pdo ): array {
    $stmt = $pdo->prepare(
        'SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE ?'
    );
    $stmt->execute( [ $base . BACKUP_SUFFIX . '%' ] );

    $groups = [];
    foreach ( $stmt->fetchAll( PDO::FETCH_COLUMN ) as $table ) {
        $stamp = substr( $table, strlen( $base ) + strlen( BACKUP_SUFFIX ) );
        if ( $stamp === '' ) {
            continue;
        }
        $groups[ $stamp ][] = $table;
    }

    krsort( $groups );
    return array_values( $groups );
};

/** Drops the oldest backup snapshots, keeping BACKUP_KEEP of the newest. */
$pruneBackups = static function () use ( $pdo, $backupGroups ): int {
    $removed = 0;

    foreach ( [ 'projects', 'project_donors' ] as $base ) {
        foreach ( array_slice( $backupGroups( $base ), BACKUP_KEEP ) as $tables ) {
            foreach ( $tables as $table ) {
                $pdo->exec( "DROP TABLE IF EXISTS `{$table}`" );
                $removed++;
            }
        }
    }

    return $removed;
};

/**
 * Removes photo gallery rows whose image files are byte-for-byte identical, so the
 * gallery can never show the same photograph twice. The row with the canonical file
 * name wins, otherwise the oldest row survives; every other duplicate is deleted.
 *
 * Rows whose file is not on the server are left alone: the gallery already skips them
 * when rendering, and a missing file is not proof of duplication.
 *
 * Returns the number of duplicate rows removed.
 */
$dedupePhotoGallery = static function () use ( $pdo ): int {
    $exists = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
    );
    $exists->execute( [ 'photo_gallery' ] );
    if ( !(int) $exists->fetchColumn() ) {
        echo "Photo gallery: table not present, duplicate check skipped.\n";
        return 0;
    }

    $root = dirname( __DIR__ );
    $rows = $pdo->query( 'SELECT id, image_path FROM photo_gallery ORDER BY id ASC' )->fetchAll( PDO::FETCH_ASSOC );

    $seen   = [];
    $remove = [];

    foreach ( $rows as $row ) {
        $path = trim( (string) ( $row[ 'image_path' ] ?? '' ) );
        if ( $path === '' ) {
            continue;
        }

        if ( preg_match( '~^(?:https?:)?//|^(?:data|blob):~i', $path ) ) {
            $key = 'remote:' . $path;
        } else {
            $local = $path;
            if ( strpos( $local, 'assets/' ) !== 0 && strpos( $local, 'uploads/' ) !== 0 ) {
                $local = 'assets/images/' . ltrim( $local, '/' );
            }
            $file = $root . DIRECTORY_SEPARATOR . str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $local );
            if ( !is_file( $file ) ) {
                continue;
            }
            $hash = @md5_file( $file );
            if ( $hash === false ) {
                continue;
            }
            $key = 'file:' . $hash;
        }

        // A file without a "-1"/"-2" suffix is the canonical one; ties go to the lower id.
        $rank = preg_match( '/-\d+\.[a-z0-9]+$/i', $path ) ? 1 : 0;
        $id   = (int) $row[ 'id' ];

        if ( !isset( $seen[ $key ] ) ) {
            $seen[ $key ] = [ 'id' => $id, 'rank' => $rank ];
            continue;
        }

        if ( $rank < $seen[ $key ][ 'rank' ] ) {
            $remove[]   = $seen[ $key ][ 'id' ];
            $seen[ $key ] = [ 'id' => $id, 'rank' => $rank ];
        } else {
            $remove[] = $id;
        }
    }

    if ( !$remove ) {
        echo 'Photo gallery: ' . count( $rows ) . " row(s) checked, no duplicates found.\n";
        return 0;
    }

    $pdo->beginTransaction();
    try {
        $delete = $pdo->prepare( 'DELETE FROM photo_gallery WHERE id = ?' );
        foreach ( $remove as $id ) {
            $delete->execute( [ $id ] );
        }
        $pdo->commit();
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }

    echo 'Photo gallery: ' . count( $rows ) . ' row(s) checked, ' . count( $remove ) . " duplicate image row(s) removed.\n";

    return count( $remove );
};

/**
 * Removes duplicate rows from the homepage "How we work" timeline.
 *
 * process_steps was populated twice: every one of the eight steps exists twice
 * with the same number, the same order and the same text, so the homepage
 * rendered each step twice in a row ("Funding opportunity", "Funding
 * opportunity", "Proposal development", "Proposal development", ...).
 *
 * Only rows that agree on both the step number and its title are removed, so
 * nothing can be lost by running this. The lowest id - the original insert -
 * is kept and every later matching row is deleted.
 *
 * Returns the number of duplicate rows removed.
 */
$dedupeProcessSteps = static function () use ( $pdo, $tableExists ): int {
    if ( !$tableExists( 'process_steps' ) ) {
        echo "Process steps: table not present, duplicate check skipped.\n";
        return 0;
    }

    $rows = $pdo->query( 'SELECT * FROM process_steps ORDER BY id ASC' )->fetchAll( PDO::FETCH_ASSOC );
    if ( !$rows ) {
        echo "Process steps: no rows, duplicate check skipped.\n";
        return 0;
    }

    $seen   = [];
    $remove = [];

    foreach ( $rows as $row ) {
        // A step is identified by the number it is published under together
        // with its title - that is exactly what the visitor sees twice. Ids
        // and timestamps deliberately take no part, so a re-inserted copy is
        // still recognised as the same step.
        $key = (string) ( $row[ 'step_number' ] ?? '' ) . "\n" . trim( (string) ( $row[ 'title' ] ?? '' ) );

        if ( isset( $seen[ $key ] ) ) {
            $remove[] = (int) $row[ 'id' ];
            continue;
        }

        $seen[ $key ] = (int) $row[ 'id' ];
    }

    if ( !$remove ) {
        echo 'Process steps: ' . count( $rows ) . " row(s) checked, no duplicates found.\n";
        return 0;
    }

    $pdo->beginTransaction();
    try {
        $delete = $pdo->prepare( 'DELETE FROM process_steps WHERE id = ?' );
        foreach ( $remove as $id ) {
            $delete->execute( [ $id ] );
        }
        $pdo->commit();
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }

    $left = count( $rows ) - count( $remove );
    echo 'Process steps: ' . count( $rows ) . ' row(s) checked, ' . count( $remove )
        . " duplicate step(s) removed; {$left} unique step(s) remain.\n";

    return count( $remove );
};

/**
 * Removes duplicate rows from the homepage "Success Stories" testimonials.
 *
 * The seed data was applied twice, sixty-eight seconds apart, so every story
 * existed as an identical pair (the same name, the same photo, the same quote
 * under two ids). That produced two visible symptoms:
 *
 *   - The same person and photograph appeared twice on the homepage.
 *   - Editing a story looked like it had not saved. The update did persist -
 *     verified directly against the row - but the untouched twin beside it
 *     kept rendering the old name and photo, so nothing appeared to change.
 *
 * A story is treated as the same one when the person's name, their quote and
 * their photograph all agree. Those three together are what the visitor sees,
 * so two rows agreeing on all three are the same testimonial shown twice. The
 * lowest id - the original insert - is kept and later matches are removed,
 * which means no distinct story can ever be discarded by running this.
 *
 * Returns the number of duplicate rows removed.
 */
$dedupeSuccessStories = static function () use ( $pdo, $tableExists ): int {
    if ( !$tableExists( 'success_stories' ) ) {
        echo "Success stories: table not present, duplicate check skipped.\n";
        return 0;
    }

    $rows = $pdo->query( 'SELECT * FROM success_stories ORDER BY id ASC' )->fetchAll( PDO::FETCH_ASSOC );
    if ( !$rows ) {
        echo "Success stories: no rows, duplicate check skipped.\n";
        return 0;
    }

    $seen   = [];
    $remove = [];

    foreach ( $rows as $row ) {
        $key = json_encode( [
            trim( (string) ( $row[ 'person_name' ] ?? '' ) ),
            trim( (string) ( $row[ 'quote' ] ?? '' ) ),
            trim( (string) ( $row[ 'image_path' ] ?? '' ) ),
        ] );

        if ( isset( $seen[ $key ] ) ) {
            $remove[] = (int) $row[ 'id' ];
            continue;
        }

        $seen[ $key ] = (int) $row[ 'id' ];
    }

    if ( !$remove ) {
        echo 'Success stories: ' . count( $rows ) . " row(s) checked, no duplicates found.\n";
        return 0;
    }

    $pdo->beginTransaction();
    try {
        $delete = $pdo->prepare( 'DELETE FROM success_stories WHERE id = ?' );
        foreach ( $remove as $id ) {
            $delete->execute( [ $id ] );
        }
        $pdo->commit();
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }

    $left = count( $rows ) - count( $remove );
    echo 'Success stories: ' . count( $rows ) . ' row(s) checked, ' . count( $remove )
        . " duplicate story(ies) removed; {$left} unique story(ies) remain.\n";

    return count( $remove );
};

/**
 * Rewrites the public call-to-action wording wherever it is stored in content columns,
 * so the buttons read "Let's Work Together" on the live site and not only in the PHP
 * fallbacks. Idempotent: only the two exact phrases are replaced, so prose, URLs
 * (contribute-with-us.php) and unrelated labels are never touched.
 *
 * Wording is cosmetic, so a failing column is reported as a warning instead of
 * failing the deployment.
 *
 * Returns the number of rows updated.
 */
$syncPublicLabels = static function () use ( $pdo ): int {
    $target  = "Let\u{2019}s Work Together";
    $phrases = [ 'Contribute With Us', 'Contribute Now' ];

    $tables = $pdo->query(
        "SELECT table_name FROM information_schema.tables
          WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'
            AND table_name NOT LIKE '%excel_backup%'
          ORDER BY table_name"
    )->fetchAll( PDO::FETCH_COLUMN );

    if ( !$tables ) {
        return 0;
    }

    $columnStmt = $pdo->prepare(
        "SELECT column_name FROM information_schema.columns
          WHERE table_schema = DATABASE() AND table_name = ?
            AND data_type IN ('varchar','char','tinytext','text','mediumtext','longtext','json')
          ORDER BY ordinal_position"
    );

    $updated = 0;

    foreach ( $tables as $table ) {
        $columnStmt->execute( [ $table ] );

        foreach ( $columnStmt->fetchAll( PDO::FETCH_COLUMN ) as $column ) {
            foreach ( $phrases as $phrase ) {
                try {
                    $update = $pdo->prepare(
                        "UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, ?, ?) WHERE `{$column}` LIKE ?"
                    );
                    $update->execute( [ $phrase, $target, '%' . $phrase . '%' ] );
                    $updated += $update->rowCount();
                } catch ( Throwable $error ) {
                    fwrite( STDERR, "WARNING: could not update `{$table}`.`{$column}`: " . $error->getMessage() . "\n" );
                }
            }
        }
    }

    echo 'CTA label sync: ' . $updated . ' row(s) rewritten to "' . $target . '"' . ( $updated ? '.' : ' (already correct).') . "\n";

    return $updated;
};

/**
 * Corrects public-facing content that was saved into the database with the
 * wrong value, so it cannot drift back on the next admin edit.
 *
 * Four problems are handled here:
 *
 *   - The contribute page and the get-involved index are served as .php, but
 *     stored buttons still carried the .html paths, which 404. Hence the button
 *     did nothing.
 *   - The chairman's title was saved as "CHAIRMAIN" (and once as "Chairmain" in
 *     a photo caption), so it renders misspelled on the homepage, the chairman
 *     section and the gallery.
 *   - The organisation's name was stored as "World Relief Committee" in the
 *     site settings, the chairman's title, the bank account titles, the news
 *     copy and several section descriptions. Every occurrence in the database
 *     is the organisation name - verified: no occurrence is followed by
 *     anything other than " Committee" - so the rewrite is exact and cannot
 *     touch unrelated prose. The templates now say "Welfare & Relief Committee",
 *     and this keeps stored content in step with them.
 *
 * Runs on every deployment, like the label sync above.
 *
 * Returns the number of values rewritten.
 */
$syncPublicContent = static function () use ( $pdo ): int {
    $replacements = [
        // Broken routes.
        [ 'get-involved/contribute-with-us.html', 'get-involved/contribute-with-us.php' ],
        [ 'get-involved/index.html', 'get-involved/index.php' ],
        // Misspellings (every case variant that occurs in the data).
        [ 'CHAIRMAIN', 'CHAIRMAN' ],
        [ 'Chairmain', 'Chairman' ],
        [ 'chairmain', 'chairman' ],
        // The organisation's name, as it is spelled in the templates. Two
        // rules rather than one so every database converges regardless of
        // which spelling it currently holds: the original "World Relief"
        // found on live, and the interim "Welfare Relief" a database may
        // already carry. Applying them in this order is safe and idempotent
        // - rewriting the first rule produces "Welfare & Relief", which the
        // second rule deliberately no longer matches.
        [ 'World Relief', 'Welfare & Relief' ],
        [ 'Welfare Relief', 'Welfare & Relief' ],
    ];

    $tables = $pdo->query(
        "SELECT table_name FROM information_schema.tables
          WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'
            AND table_name NOT LIKE '%excel_backup%'
          ORDER BY table_name"
    )->fetchAll( PDO::FETCH_COLUMN );

    if ( !$tables ) {
        return 0;
    }

    $columnStmt = $pdo->prepare(
        "SELECT column_name FROM information_schema.columns
          WHERE table_schema = DATABASE() AND table_name = ?
            AND data_type IN ('varchar','char','tinytext','text','mediumtext','longtext','json')
          ORDER BY ordinal_position"
    );

    $updated = 0;

    foreach ( $tables as $table ) {
        $columnStmt->execute( [ $table ] );

        foreach ( $columnStmt->fetchAll( PDO::FETCH_COLUMN ) as $column ) {
            foreach ( $replacements as [ $from, $to ] ) {
                try {
                    $update = $pdo->prepare(
                        "UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, ?, ?) WHERE `{$column}` LIKE ?"
                    );
                    $update->execute( [ $from, $to, '%' . $from . '%' ] );
                    $updated += $update->rowCount();
                } catch ( Throwable $error ) {
                    fwrite( STDERR, "WARNING: could not update `{$table}`.`{$column}`: " . $error->getMessage() . "\n" );
                }
            }
        }
    }

    // The hero slider's "View Gallery" button points at /gallery, which has never
    // existed. Its three sibling slides on the same slider use bare fragments
    // (#news, #photos, #videos) because they are already on /news/, so this one
    // gets #photos too: it scrolls to the gallery without reloading the page.
    //
    // This must be an exact value UPDATE, not a REPLACE: the substring "/gallery"
    // also appears inside every photo_gallery.image_path (assets/images/gallery/…),
    // and rewriting those would break every image on the site.
    $galleryFixed = 0;

    try {
        $gallery = $pdo->prepare( "UPDATE `hero_sliders` SET `cta_link` = ? WHERE `cta_link` = ?" );
        $gallery->execute( [ '#photos', '/gallery' ] );
        $galleryFixed = $gallery->rowCount();
    } catch ( Throwable $error ) {
        fwrite( STDERR, 'WARNING: could not retarget the hero slider gallery button: ' . $error->getMessage() . "\n" );
    }

    echo 'Content sync: ' . $updated . ' stored value(s) rewritten' . ( $updated ? '.' : ' (already correct).' ) . "\n";
    echo 'Hero slider gallery button: ' . ( $galleryFixed ? $galleryFixed . ' row(s) retargeted to #photos.' : 'already correct.' ) . "\n";

    return $updated + $galleryFixed;
};

try {
    $sqlFile = __DIR__ . '/replace_projects_from_wrc_excel.sql';
    if ( !is_file( $sqlFile ) ) {
        throw new RuntimeException( 'The project import SQL file is missing: database/replace_projects_from_wrc_excel.sql' );
    }

    foreach ( [ 'projects', 'project_donors' ] as $requiredTable ) {
        if ( !$tableExists( $requiredTable ) ) {
            $database  = (string) $pdo->query( 'SELECT DATABASE()' )->fetchColumn();
            throw new RuntimeException( "The `{$requiredTable}` table does not exist in database `{$database}`." );
        }
    }

    // The import SQL and the frontend both rely on projects.category.
    if ( !$columnExists( 'projects', 'category' ) ) {
        $pdo->exec( "ALTER TABLE `projects` ADD COLUMN `category` VARCHAR(100) NOT NULL DEFAULT 'general' AFTER `status`" );
        echo "Added the missing `projects.category` column required by the portfolio import.\n";
    }

    // about_hero was created without the two heading columns that every other
    // hero table has. The admin editor offers "Section Title" and "Section
    // Subtitle" for it, but admin/pages.php -> saveHeroSection() had no column
    // to write them to, so an edit to the About page heading looked saved and
    // then quietly reverted on the next page load. Add the columns so the value
    // has somewhere to live, and seed the title from the wording the public
    // page falls back to so nothing visible changes for visitors.
    if ( $tableExists( 'about_hero' ) ) {
        if ( !$columnExists( 'about_hero', 'section_title' ) ) {
            $pdo->exec( "ALTER TABLE `about_hero` ADD COLUMN `section_title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `id`" );
            $pdo->exec( "UPDATE `about_hero` SET `section_title` = 'About Welfare & Relief Committee' WHERE `section_title` = ''" );
            echo "Added the missing `about_hero.section_title` column used by the admin editor.\n";
        }
        if ( !$columnExists( 'about_hero', 'section_subtitle' ) ) {
            $pdo->exec( "ALTER TABLE `about_hero` ADD COLUMN `section_subtitle` TEXT NULL AFTER `section_title`" );
            echo "Added the missing `about_hero.section_subtitle` column used by the admin editor.\n";
        }
    }

    // Maintenance that runs on every deployment, including the "already matches"
    // short-circuit below: keeps stored CTA wording in sync with the templates,
    // rewrites links and misspellings that were saved into the database, keeps
    // the photo gallery free of byte-identical duplicate images, keeps the
    // "How we work" timeline free of duplicated steps, and keeps the success
    // stories free of duplicated people.
    $syncPublicLabels();
    $syncPublicContent();
    $dedupePhotoGallery();
    $dedupeProcessSteps();
    $dedupeSuccessStories();

    $reasons = $portfolioStatus();
    if ( !$reasons ) {
        echo 'Project portfolio already matches the ' . PORTFOLIO_SIZE . " approved Excel entries; no changes made.\n";
        exit( 0 );
    }

    echo 'Live portfolio differs from the approved list: ' . implode( '; ', $reasons ) . ".\n";

    $sql = file_get_contents( $sqlFile );
    if ( $sql === false ) {
        throw new RuntimeException( 'Unable to read the project import SQL file.' );
    }

    // Fail before touching any data if a referenced image is not on the server.
    $missingImages = [];
    if ( preg_match_all( "/'((?:assets|uploads)\/[^']+\.(?:png|jpe?g|gif|webp|svg))'/i", $sql, $matches ) ) {
        foreach ( array_unique( $matches[ 1 ] ) as $imagePath ) {
            $imageFile = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . ltrim( $imagePath, '/\\' );
            if ( !is_file( $imageFile ) ) {
                $missingImages[] = $imagePath;
            }
        }
    }
    if ( $missingImages ) {
        throw new RuntimeException( 'Missing project image file(s): ' . implode( ', ', $missingImages ) );
    }

    // Backups are named after the run time; the name is made unique so that two
    // deployments landing in the same second can never collide.
    $stamp   = gmdate( 'Ymd_His' );
    $suffix  = '';
    $attempt = 1;
    while ( $tableExists( 'projects' . BACKUP_SUFFIX . $stamp . $suffix )
        || $tableExists( 'project_donors' . BACKUP_SUFFIX . $stamp . $suffix ) ) {
        $suffix = '_' . $attempt++;
    }
    $stamp         .= $suffix;
    $projectBackup  = 'projects' . BACKUP_SUFFIX . $stamp;
    $partnerBackup  = 'project_donors' . BACKUP_SUFFIX . $stamp;

    $pdo->exec( "CREATE TABLE `{$projectBackup}` LIKE projects" );
    $pdo->exec( "INSERT INTO `{$projectBackup}` SELECT * FROM projects" );
    $pdo->exec( "CREATE TABLE `{$partnerBackup}` LIKE project_donors" );
    $pdo->exec( "INSERT INTO `{$partnerBackup}` SELECT * FROM project_donors" );
    echo "Backed up the current rows to `{$projectBackup}` and `{$partnerBackup}`.\n";

    $sql = preg_replace( '/^\s*--.*$/m', '', $sql );
    $statements = preg_split( '/;\s*(?=\R|$)/', $sql, -1, PREG_SPLIT_NO_EMPTY );
    if ( !is_array( $statements ) ) {
        throw new RuntimeException( 'Unable to parse the project import SQL file.' );
    }

    $backupStatement = '/^(?:CREATE\s+TABLE|INSERT\s+INTO)\s+`?(?:projects|project_donors)`?' . preg_quote( BACKUP_SUFFIX, '/' ) . '/i';
    $executed        = 0;

    $pdo->beginTransaction();
    try {
        foreach ( $statements as $statement ) {
            $statement = trim( $statement );
            if ( $statement === '' ) {
                continue;
            }
            // Transaction control is handled here, and the backup snapshots in the
            // SQL file have already been taken above.
            if ( preg_match( '/^(?:START\s+TRANSACTION|COMMIT|ROLLBACK)$/i', $statement ) ) {
                continue;
            }
            if ( preg_match( $backupStatement, $statement ) ) {
                continue;
            }

            $pdo->exec( $statement );
            $executed++;
        }

        $verify = $portfolioStatus();
        if ( $verify ) {
            throw new RuntimeException( 'Imported project data failed integrity checks: ' . implode( '; ', $verify ) );
        }

        $pdo->commit();
    } catch ( Throwable $error ) {
        if ( $pdo->inTransaction() ) {
            $pdo->rollBack();
        }
        throw $error;
    }

    $pruned = $pruneBackups();

    $stored   = (int) $pdo->query( 'SELECT COUNT(*) FROM projects' )->fetchColumn();
    $partners = (int) $pdo->query( 'SELECT COUNT(*) FROM project_donors' )->fetchColumn();

    echo "Applied {$executed} import statement(s); live portfolio now holds {$stored} projects and {$partners} partner records.\n";
    if ( $pruned ) {
        echo "Pruned {$pruned} older backup table(s); the newest " . BACKUP_KEEP . " snapshot(s) are kept.\n";
    }

    echo 'Imported and verified all ' . PORTFOLIO_SIZE . " WRC Excel projects. Previous project records were backed up.\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    fwrite( STDERR, 'Project import failed: ' . $error->getMessage() . PHP_EOL );
    exit( 1 );
}
