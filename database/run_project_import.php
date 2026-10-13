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

if ( PHP_SAPI !== 'cli' ) {
    http_response_code( 404 );
    exit;
}

require_once dirname( __DIR__ ) . '/includes/config.php';

global $pdo;

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
