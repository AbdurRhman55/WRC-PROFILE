<?php
/**
 * WRC - Partner-driven project galleries.
 *
 * Creates and maintains the two tables that power the public Photo Gallery
 * flow:
 *
 *   partner_projects  links a partner to the project slugs it delivered.
 *   project_media     ordered gallery photographs for a project, each with a
 *                     caption and a short context note.
 *
 * The script is intentionally idempotent and safe to run on every deployment:
 *   - missing columns/tables are created once,
 *   - partners get a stable slug,
 *   - donor names from project_donors are promoted into real partner records,
 *   - project links are inserted without removing anything an admin added,
 *   - every project gets one cover image the first time it is seen.
 *
 * Projects are referenced by slug rather than id because the Excel import
 * deletes and re-inserts the projects table, which changes auto-increment ids.
 * A slug survives that, so uploaded gallery photographs are never orphaned.
 *
 * Standalone:  php database/seed_media_gallery.php
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}
require_once dirname( __DIR__ ) . '/includes/functions.php';

if ( !function_exists( 'wrcMediaColumnExists' ) ) {
    function wrcMediaColumnExists( PDO $pdo, string $table, string $column ): bool {
        try {
            $statement = $pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $statement->execute( [ $table, $column ] );
            return (int) $statement->fetchColumn() > 0;
        } catch ( Throwable $error ) {
            return false;
        }
    }
}

if ( !function_exists( 'wrcMediaIndexExists' ) ) {
    function wrcMediaIndexExists( PDO $pdo, string $table, string $index ): bool {
        try {
            $statement = $pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.STATISTICS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?'
            );
            $statement->execute( [ $table, $index ] );
            return (int) $statement->fetchColumn() > 0;
        } catch ( Throwable $error ) {
            return false;
        }
    }
}

if ( !function_exists( 'wrcMediaUniquePartnerSlug' ) ) {
    function wrcMediaUniquePartnerSlug( PDO $pdo, string $name, int $excludeId = 0 ): string {
        $base = function_exists( 'createSlug' ) ? createSlug( $name ) : strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $name ) );
        $base = trim( (string) $base, '-' );
        if ( $base === '' ) {
            $base = 'partner';
        }

        $slug   = $base;
        $suffix = 2;
        while ( true ) {
            $statement = $pdo->prepare( 'SELECT COUNT(*) FROM partners WHERE slug = ? AND id <> ?' );
            $statement->execute( [ $slug, $excludeId ] );
            if ( (int) $statement->fetchColumn() === 0 ) {
                return $slug;
            }
            $slug = $base . '-' . $suffix++;
        }
    }
}

if ( !function_exists( 'wrcMediaPartnerTypeFor' ) ) {
    function wrcMediaPartnerTypeFor( string $name ): string {
        $name = strtolower( $name );

        $governmentHints = [ 'department', 'authority', 'tevta', 'govt', 'government', 'ministry', 'commission', 'phhsa' ];
        foreach ( $governmentHints as $hint ) {
            if ( strpos( $name, $hint ) !== false ) {
                return 'government';
            }
        }

        return 'funding';
    }
}

if ( !function_exists( 'wrcMergeDuplicatePartners' ) ) {
    /**
     * Fold partner records that describe the same organisation into one row.
     *
     * Older deployments created a second "Health Department" beside
     * "Department of Health" (and "Smart Aid" beside "SmartAID") because donor
     * promotion only compared exact names. The dashboard then showed two cards
     * for one organisation and its project links were split between them.
     *
     * Each alias group keeps the record that already owns the gallery, adopts
     * any profile detail it is missing, moves the duplicate's project links
     * across, and deletes the duplicate. Idempotent: a clean database returns 0.
     */
    function wrcMergeDuplicatePartners( PDO $pdo ): int {
        if ( !function_exists( 'wrcPartnerAliasKey' ) ) {
            return 0;
        }

        $rows = $pdo->query( 'SELECT * FROM partners ORDER BY id ASC' )->fetchAll( PDO::FETCH_ASSOC );
        if ( count( $rows ) < 2 ) {
            return 0;
        }

        $hasLinks = wrcTableExists( 'partner_projects' );
        $linkCounts = [];
        if ( $hasLinks ) {
            foreach ( $pdo->query( 'SELECT partner_id, COUNT(*) AS c FROM partner_projects GROUP BY partner_id' ) as $row ) {
                $linkCounts[ (int) $row['partner_id'] ] = (int) $row['c'];
            }
        }

        $groups = [];
        foreach ( $rows as $row ) {
            $key = wrcPartnerAliasKey( (string) ( $row['name'] ?? '' ) );
            if ( $key === '' ) {
                continue;
            }
            $groups[ $key ][] = $row;
        }

        $placeholder = 'WRC collaborator on documented humanitarian projects.';
        $score = static function ( array $row ) use ( $linkCounts ): array {
            return [
                $linkCounts[ (int) $row['id'] ] ?? 0,
                trim( (string) ( $row['cover_image'] ?? '' ) ) !== '' ? 1 : 0,
                trim( (string) ( $row['logo_path'] ?? '' ) ) !== '' ? 1 : 0,
                trim( (string) ( $row['description'] ?? '' ) ) !== '' ? 1 : 0,
                (int) ( $row['is_active'] ?? 0 ),
                -(int) $row['id'],
            ];
        };

        $profileColumns = [];
        foreach ( [ 'logo_path', 'website_url', 'description', 'cover_image' ] as $column ) {
            if ( wrcMediaColumnExists( $pdo, 'partners', $column ) ) {
                $profileColumns[] = $column;
            }
        }

        $moveLinks   = $hasLinks ? $pdo->prepare( 'INSERT IGNORE INTO partner_projects (partner_id, project_slug, sort_order) SELECT ?, project_slug, sort_order FROM partner_projects WHERE partner_id = ?' ) : null;
        $dropLinks   = $hasLinks ? $pdo->prepare( 'DELETE FROM partner_projects WHERE partner_id = ?' ) : null;
        $dropPartner = $pdo->prepare( 'DELETE FROM partners WHERE id = ?' );

        $merged = 0;

        foreach ( $groups as $group ) {
            if ( count( $group ) < 2 ) {
                continue;
            }

            usort( $group, static function ( $a, $b ) use ( $score ) {
                return $score( $b ) <=> $score( $a );
            } );
            $canonical = array_shift( $group );

            // Keep the best profile detail from any spelling of the organisation.
            $fill = [];
            foreach ( $profileColumns as $column ) {
                $current = trim( (string) ( $canonical[ $column ] ?? '' ) );
                $needsValue = $current === '' || ( $column === 'description' && $current === $placeholder );
                if ( !$needsValue ) {
                    continue;
                }
                foreach ( $group as $duplicate ) {
                    $value = trim( (string) ( $duplicate[ $column ] ?? '' ) );
                    if ( $value === '' || ( $column === 'description' && $value === $placeholder ) ) {
                        continue;
                    }
                    $fill[ $column ] = $value;
                    break;
                }
            }
            if ( $fill ) {
                $set = implode( ', ', array_map( static function ( $column ) {
                    return '`' . $column . '` = ?';
                }, array_keys( $fill ) ) );
                $statement = $pdo->prepare( 'UPDATE partners SET ' . $set . ' WHERE id = ?' );
                $statement->execute( array_merge( array_values( $fill ), [ (int) $canonical['id'] ] ) );
            }

            foreach ( $group as $duplicate ) {
                $duplicateId = (int) $duplicate['id'];
                if ( $duplicateId < 1 || $duplicateId === (int) $canonical['id'] ) {
                    continue;
                }
                if ( $moveLinks && $dropLinks ) {
                    $moveLinks->execute( [ (int) $canonical['id'], $duplicateId ] );
                    $dropLinks->execute( [ $duplicateId ] );
                }
                $dropPartner->execute( [ $duplicateId ] );
                $merged++;
            }
        }

        return $merged;
    }
}

if ( !function_exists( 'wrcSetupMediaGallery' ) ) {
    /**
     * @return array<string,int> Counters for the deployment log.
     */
    function wrcSetupMediaGallery( PDO $pdo ): array {
        $result = [
            'tables'   => 0,
            'partners' => 0,
            'links'    => 0,
            'media'    => 0,
            'merged'   => 0,
        ];

        // ---------------------------------------------------------------------
        // 1. Schema
        // ---------------------------------------------------------------------
        if ( !wrcMediaColumnExists( $pdo, 'partners', 'slug' ) ) {
            $pdo->exec( "ALTER TABLE partners ADD COLUMN slug VARCHAR(191) NULL AFTER name" );
            $result['tables']++;
        }
        if ( !wrcMediaColumnExists( $pdo, 'partners', 'description' ) ) {
            $pdo->exec( "ALTER TABLE partners ADD COLUMN description TEXT NULL AFTER partner_type" );
            $result['tables']++;
        }
        if ( !wrcMediaColumnExists( $pdo, 'partners', 'cover_image' ) ) {
            $pdo->exec( "ALTER TABLE partners ADD COLUMN cover_image VARCHAR(500) NULL AFTER logo_path" );
            $result['tables']++;
        }

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS partner_projects (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                partner_id INT UNSIGNED NOT NULL,
                project_slug VARCHAR(150) NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY partner_project (partner_id, project_slug),
                KEY project_slug (project_slug),
                KEY partner_id (partner_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS project_media (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                project_slug VARCHAR(150) NOT NULL,
                image_path VARCHAR(500) NOT NULL,
                caption VARCHAR(255) NULL,
                context TEXT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY project_order (project_slug, sort_order),
                KEY active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        // ---------------------------------------------------------------------
        // 2. Stable partner slugs
        // ---------------------------------------------------------------------
        $partners = $pdo->query( 'SELECT id, name, slug, description, cover_image FROM partners ORDER BY id' )->fetchAll( PDO::FETCH_ASSOC );
        $updatePartner = $pdo->prepare( 'UPDATE partners SET slug = ? WHERE id = ?' );
        foreach ( $partners as $partner ) {
            if ( trim( (string) ( $partner['slug'] ?? '' ) ) !== '' ) {
                continue;
            }
            $slug = wrcMediaUniquePartnerSlug( $pdo, (string) $partner['name'], (int) $partner['id'] );
            $updatePartner->execute( [ $slug, (int) $partner['id'] ] );
            $partner['slug'] = $slug;
            $result['partners']++;
        }

        if ( !wrcMediaIndexExists( $pdo, 'partners', 'partner_slug' ) ) {
            try {
                $pdo->exec( 'ALTER TABLE partners ADD UNIQUE KEY partner_slug (slug)' );
            } catch ( Throwable $error ) {
                // A duplicate slug left by an old manual edit should never stop
                // a deployment. The public pages fall back to the id lookup.
            }
        }

        // ---------------------------------------------------------------------
        // 3. Promote donor names from project_donors into partner records
        // ---------------------------------------------------------------------
        $aliases = [
            'health department' => 'Department of Health',
        ];

        $findPartner = $pdo->prepare( 'SELECT * FROM partners WHERE LOWER(name) = LOWER(?) LIMIT 1' );
        $insertPartner = $pdo->prepare(
            'INSERT INTO partners (name, slug, logo_path, website_url, partner_type, description, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $partnerIdByName = [];

        foreach ( $partners as $partner ) {
            $partnerIdByName[ strtolower( (string) $partner['name'] ) ] = (int) $partner['id'];
        }

        $donors = $pdo->query(
            'SELECT donor_name,
                    COUNT(*) AS project_count,
                    GROUP_CONCAT(DISTINCT donor_type ORDER BY donor_type SEPARATOR ", ") AS donor_types
               FROM project_donors
              GROUP BY donor_name
              ORDER BY donor_name'
        )->fetchAll( PDO::FETCH_ASSOC );

        $nextSort = (int) $pdo->query( 'SELECT COALESCE(MAX(sort_order), 0) FROM partners' )->fetchColumn() + 10;

        foreach ( $donors as $donor ) {
            $donorName = trim( (string) $donor['donor_name'] );
            if ( $donorName === '' ) {
                continue;
            }

            $lookup = strtolower( $donorName );
            $lookup = $aliases[ $lookup ] ?? $lookup;

            $findPartner->execute( [ $lookup ] );
            $existing = $findPartner->fetch( PDO::FETCH_ASSOC );

            if ( $existing ) {
                $partnerIdByName[ strtolower( $donorName ) ] = (int) $existing['id'];
                $partnerIdByName[ $lookup ]                 = (int) $existing['id'];
                continue;
            }

            $type = wrcMediaPartnerTypeFor( $donorName );
            $temp = [
                'name'         => $donorName,
                'slug'         => wrcMediaUniquePartnerSlug( $pdo, $donorName ),
                'partner_type' => $type,
            ];
            $insertPartner->execute( [
                $temp['name'],
                $temp['slug'],
                '',
                '',
                $type,
                'WRC collaborator on documented humanitarian projects.',
                $nextSort,
            ] );
            $nextSort += 10;

            $newId = (int) $pdo->lastInsertId();
            $partnerIdByName[ strtolower( $donorName ) ] = $newId;
            $partnerIdByName[ $lookup ]                  = $newId;
            $result['partners']++;
        }

        // ---------------------------------------------------------------------
        // 4. Link projects to partners
        // ---------------------------------------------------------------------
        $projectRows = $pdo->query(
            'SELECT id, slug, title, category, district, location_details, description, overview, hero_image
               FROM projects
              WHERE is_active = 1
              ORDER BY sort_order ASC, id ASC'
        )->fetchAll( PDO::FETCH_ASSOC );

        $projectBySlug = [];
        foreach ( $projectRows as $project ) {
            $projectBySlug[ (string) $project['slug'] ] = $project;
        }

        $donorRows = $pdo->query( 'SELECT project_id, donor_name FROM project_donors ORDER BY id ASC' )->fetchAll( PDO::FETCH_ASSOC );
        $projectSlugById = [];
        foreach ( $pdo->query( 'SELECT id, slug FROM projects' )->fetchAll( PDO::FETCH_ASSOC ) as $row ) {
            $projectSlugById[ (int) $row['id'] ] = (string) $row['slug'];
        }

        $linkExists = $pdo->prepare( 'SELECT COUNT(*) FROM partner_projects WHERE partner_id = ? AND project_slug = ?' );
        $insertLink = $pdo->prepare( 'INSERT INTO partner_projects (partner_id, project_slug, sort_order) VALUES (?, ?, ?)' );

        foreach ( $donorRows as $donorRow ) {
            $projectSlug = $projectSlugById[ (int) $donorRow['project_id'] ] ?? '';
            if ( $projectSlug === '' ) {
                continue;
            }
            $key = strtolower( trim( (string) $donorRow['donor_name'] ) );
            $partnerId = $partnerIdByName[ $aliases[ $key ] ?? $key ] ?? 0;
            if ( $partnerId < 1 ) {
                continue;
            }

            $linkExists->execute( [ $partnerId, $projectSlug ] );
            if ( (int) $linkExists->fetchColumn() > 0 ) {
                continue;
            }

            $insertLink->execute( [ $partnerId, $projectSlug, (int) $donorRow['project_id'] ] );
            $result['links']++;
        }

        // ---------------------------------------------------------------------
        // 5. Friendly defaults for seeded partners (never overwrite edits)
        // ---------------------------------------------------------------------
        $partnerProjects = [];
        foreach ( $pdo->query(
            'SELECT pp.partner_id, pr.slug, pr.category, pr.district, pr.location_details, pr.hero_image
               FROM partner_projects pp
               JOIN projects pr ON pr.slug = pp.project_slug
              WHERE pr.is_active = 1
              ORDER BY pr.sort_order ASC, pr.id ASC'
        )->fetchAll( PDO::FETCH_ASSOC ) as $row ) {
            $partnerProjects[ (int) $row['partner_id'] ][] = $row;
        }

        $updateDefaults = $pdo->prepare( 'UPDATE partners SET description = ?, cover_image = ? WHERE id = ?' );
        $allPartners = $pdo->query( 'SELECT id, name, slug, description, cover_image FROM partners ORDER BY id' )->fetchAll( PDO::FETCH_ASSOC );
        foreach ( $allPartners as $partner ) {
            $id = (int) $partner['id'];
            $linked = $partnerProjects[ $id ] ?? [];
            if ( !$linked ) {
                continue;
            }

            $cover = '';
            foreach ( $linked as $linkedProject ) {
                if ( !empty( $linkedProject['hero_image'] ) && imageExists( (string) $linkedProject['hero_image'] ) ) {
                    $cover = (string) $linkedProject['hero_image'];
                    break;
                }
            }

            $description = trim( (string) ( $partner['description'] ?? '' ) );
            $placeholder = 'WRC collaborator on documented humanitarian projects.';
            if ( $description === '' || $description === $placeholder ) {
                $sectors   = array_values( array_unique( array_filter( array_column( $linked, 'category' ) ) ) );
                $districts = array_values( array_unique( array_filter( array_column( $linked, 'district' ) ) ) );
                $description = $partner['name'] . ' works with WRC on ' . count( $linked ) . ' documented project' . ( count( $linked ) === 1 ? '' : 's' );
                if ( $sectors ) {
                    $description .= ' in ' . implode( ', ', array_slice( $sectors, 0, 3 ) );
                }
                if ( $districts ) {
                    $description .= ' across ' . implode( ', ', array_slice( $districts, 0, 3 ) );
                }
                $description .= '.';
            }

            $currentCover = trim( (string) ( $partner['cover_image'] ?? '' ) );
            $updateDefaults->execute( [
                $description,
                $currentCover !== '' ? $currentCover : $cover,
                $id,
            ] );
        }

        // ---------------------------------------------------------------------
        // 6. Seed one meaningful photograph per project
        // ---------------------------------------------------------------------
        $mediaCount = $pdo->prepare( 'SELECT COUNT(*) FROM project_media WHERE project_slug = ?' );
        $insertMedia = $pdo->prepare(
            'INSERT INTO project_media (project_slug, image_path, caption, context, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, 1)'
        );

        $donorsByProject = [];
        foreach ( $pdo->query( 'SELECT project_id, donor_name FROM project_donors ORDER BY id' )->fetchAll( PDO::FETCH_ASSOC ) as $row ) {
            $pid = (int) $row['project_id'];
            $donorsByProject[ $pid ][] = (string) $row['donor_name'];
        }

        $sectorPhrases = [
            'health'             => 'medical services, health screening and patient care',
            'education'          => 'education support, training sessions and learning activities',
            'livelihood'         => 'skills training, enterprise support and livelihood activities',
            'food security'      => 'food distribution and nutrition support for families',
            'emergency response' => 'emergency relief, protective kits and rapid response assistance',
            'wash'               => 'clean water, sanitation infrastructure and hygiene promotion',
        ];

        foreach ( $projectRows as $project ) {
            $slug = (string) $project['slug'];
            $mediaCount->execute( [ $slug ] );
            if ( (int) $mediaCount->fetchColumn() > 0 ) {
                continue;
            }

            $hero = trim( (string) ( $project['hero_image'] ?? '' ) );
            if ( $hero === '' || !imageExists( $hero ) ) {
                continue;
            }

            $sector = strtolower( trim( (string) ( $project['category'] ?? '' ) ) );
            $phrase = $sectorPhrases[ $sector ] ?? 'humanitarian services and community-level support';
            $place  = trim( (string) ( $project['district'] ?? '' ) );
            if ( $place === '' ) {
                $place = trim( (string) ( $project['location_details'] ?? '' ) );
            }

            $context = 'This photograph documents ' . $phrase . ' delivered under ' . $project['title'];
            if ( $place !== '' ) {
                $context .= ' in ' . $place;
            }
            $projectDonors = array_values( array_unique( $donorsByProject[ (int) $project['id'] ] ?? [] ) );
            if ( $projectDonors ) {
                $context .= ', in partnership with ' . implode( ', ', $projectDonors );
            }
            $context .= '.';

            $insertMedia->execute( [
                $slug,
                $hero,
                'Field documentation — ' . $project['title'],
                $context,
                1,
            ] );
            $result['media']++;
        }

        // ---------------------------------------------------------------------
        // 7. One card per organisation
        // ---------------------------------------------------------------------
        // Runs last so it can see every link created above. The dashboard and
        // the public gallery both read this table, so merging here fixes both.
        $result['merged'] = wrcMergeDuplicatePartners( $pdo );

        return $result;
    }
}

// Auto-run when this file is included from the deployment script.
if ( !defined( 'WRC_MEDIA_SETUP_AUTO_RAN' ) && isset( $pdo ) && $pdo instanceof PDO ) {
    define( 'WRC_MEDIA_SETUP_AUTO_RAN', 1 );

    if ( PHP_SAPI === 'cli' && realpath( $_SERVER['SCRIPT_FILENAME'] ?? '' ) === realpath( __FILE__ ) ) {
        echo "=== WRC partner galleries ===\n";
    }

    try {
        $mediaStats = wrcSetupMediaGallery( $pdo );

        if ( PHP_SAPI === 'cli' ) {
            echo 'Schema changes: ' . $mediaStats['tables'] . "\n";
            echo 'Partner slugs/profiles created: ' . $mediaStats['partners'] . "\n";
            echo 'Project links added: ' . $mediaStats['links'] . "\n";
            echo 'Project photographs seeded: ' . $mediaStats['media'] . "\n";
            echo 'Duplicate partner records merged: ' . $mediaStats['merged'] . "\n";
        }
    } catch ( Throwable $error ) {
        if ( PHP_SAPI === 'cli' ) {
            fwrite( STDERR, 'Partner gallery setup failed: ' . $error->getMessage() . PHP_EOL );
        }
        throw $error;
    }
}
