<?php
/**
* WRC Website - Helper Functions
* Utility functions for data handling, sanitization, and rendering
*/

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// SANITIZATION
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

function sanitize( ?string $input ): string {
    if ( $input === null ) return '';
    return htmlspecialchars( trim( $input ), ENT_QUOTES, 'UTF-8' );
}

function sanitizeArray( array $input ): array {
    return array_map( 'sanitize', $input );
}

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// DATABASE HELPERS
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

/**
* Return the web path of this project, regardless of whether it is deployed
* at the domain root or inside a subdirectory.
*/

function siteBaseUrl(): string {
    static $baseUrl = null;

    if ( $baseUrl !== null ) {
        return $baseUrl;
    }

    $scriptFilename = realpath( $_SERVER[ 'SCRIPT_FILENAME' ] ?? '' );
    $siteRoot = realpath( SITE_ROOT );
    $scriptName = str_replace( '\\', '/', $_SERVER[ 'SCRIPT_NAME' ] ?? '' );

    if ( $scriptFilename !== false && $siteRoot !== false && $scriptName !== '' ) {
        $scriptFilename = rtrim( str_replace( '\\', '/', $scriptFilename ), '/' );
        $siteRoot = rtrim( str_replace( '\\', '/', $siteRoot ), '/' );
        $relativeScript = ltrim( substr( $scriptFilename, strlen( $siteRoot ) ), '/' );

        if ( $relativeScript !== '' && strpos( $scriptFilename, $siteRoot . '/' ) === 0 ) {
            $scriptDirectory = rtrim( str_replace( '\\', '/', dirname( $scriptName ) ), '/' );
            $relativeDirectory = dirname( $relativeScript );
            $relativeDirectory = $relativeDirectory === '.' ? '' : str_replace( '\\', '/', $relativeDirectory );

            if ( $relativeDirectory !== '' && substr( $scriptDirectory, -strlen( '/' . $relativeDirectory ) ) === '/' . $relativeDirectory ) {
                $scriptDirectory = substr( $scriptDirectory, 0, -strlen( '/' . $relativeDirectory ) );
            }

            $baseUrl = rtrim( $scriptDirectory, '/' ) . '/';
            return $baseUrl === '/' ? '/' : $baseUrl;
        }
    }

    $baseUrl = '/';
    return $baseUrl;
}

/** Build an app-root-relative URL from a stored project-relative path. */

function siteUrl( string $path = '' ): string {
    $path = trim( $path );
    if ( $path === '' ) {
        return siteBaseUrl();
    }

    if ( preg_match( '~^(?:https?:)?//|^(?:data|blob):~i', $path ) ) {
        return $path;
    }

    return siteBaseUrl() . ltrim( $path, '/' );
}

/**
* Get a single setting value from site_settings
*/

function getSetting( string $key, string $default = '' ): string {
    global $pdo;
    static $cache = [];

    if ( isset( $cache[ $key ] ) ) {
        return $cache[ $key ];
    }

    $stmt = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
    $stmt->execute( [ $key ] );
    $result = $stmt->fetch();

    $cache[ $key ] = $result ? $result[ 'setting_value' ] : $default;
    return $cache[ $key ];
}

/**
* Get all settings for a specific group
*/

function getSettingsByGroup( string $group ): array {
    global $pdo;
    $stmt = $pdo->prepare( 'SELECT setting_key, setting_value FROM site_settings WHERE setting_group = ?' );
    $stmt->execute( [ $group ] );
    $results = $stmt->fetchAll();

    $settings = [];
    foreach ( $results as $row ) {
        $settings[ $row[ 'setting_key' ] ] = $row[ 'setting_value' ];
    }
    return $settings;
}

/**
* Fetch a single row by ID
*/

function getById( string $table, int $id ): ?array {
    global $pdo;
    $allowed = [
        'programs', 'projects', 'news_articles', 'resources', 'team_members',
        'partners', 'provinces', 'campaigns', 'contact_submissions',
        'newsletter_subscribers', 'donation_tiers', 'bank_accounts', 'job_listings',
        'sdg_goals', 'sdg_contributions', 'impact_stats', 'organizational_structure',
        'hero_sliders', 'homepage_sections', 'site_settings',
        'photo_gallery', 'video_gallery', 'success_stories', 'events',
        'project_donors', 'get_involved_hero', 'get_involved_cta',
        'get_involved_volunteer', 'get_involved_partner', 'get_involved_membership',
        'contact_hero', 'contact_content',
    ];

    if ( !in_array( $table, $allowed ) ) {
        return null;
    }

    $stmt = $pdo->prepare( "SELECT * FROM `{$table}` WHERE id = ?" );
    $stmt->execute( [ $id ] );
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
* Fetch multiple rows with optional conditions
* $conditions can be:
*   - associative array: [ 'is_active' => 1 ] → WHERE `is_active` = 1
*   - raw string: 'is_active = 1 ORDER BY sort_order ASC LIMIT 3' → WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 3
*/

function getRows( string $table, $conditions = [], string $orderBy = 'sort_order ASC', int $limit = 0 ): array {
    global $pdo;
    $allowed = [
        'programs', 'projects', 'news_articles', 'resources', 'team_members',
        'partners', 'provinces', 'campaigns', 'contact_submissions',
        'newsletter_subscribers', 'donation_tiers', 'bank_accounts', 'job_listings',
        'sdg_goals', 'sdg_contributions', 'impact_stats', 'organizational_structure',
        'hero_sliders', 'homepage_sections', 'site_settings', 'program_interventions',
        'program_focus_areas', 'program_impact_stats', 'project_donors', 'province_programs',
        'process_steps', 'success_stories', 'events', 'photo_gallery', 'video_gallery',
        'about_hero', 'about_mission', 'about_mission_items',
        'about_governance', 'about_governance_items',
        'about_transparency', 'about_transparency_stats', 'about_cta',
        'chairman_message', 'geographic_presence', 'geographic_presence_stats', 'cta_banner',
        'our_work_hero', 'our_work_editorial', 'our_work_editorial_features',
        'our_work_process', 'our_work_process_steps',
        'our_work_pillars', 'our_work_pillars_items',
        'our_work_featured_projects', 'our_work_cta',
        'projects_hero', 'projects_dashboard', 'projects_dashboard_metrics', 'projects_dashboard_sectors',
        'projects_cta', 'causes_hero', 'causes_impact', 'causes_impact_stats',
        'who_we_are_hero', 'who_we_are_impact', 'who_we_are_impact_items',
        'why_we_exist', 'why_we_exist_items',
        'history_hero', 'history_stats', 'history_stats_items', 'history_cta',
        'vision_mission_hero', 'vision_mission_content',
        'org_structure_hero', 'org_structure_content',
        'policies_hero', 'policies_content',
        'annual_reports_hero', 'annual_reports_content',
        'contact_hero', 'contact_content',
        'impact_hero', 'impact_content',
        'where_we_work_hero', 'where_we_work_content', 'where_we_work_districts',
        'district_coordinates',
        'news_hero', 'resources_hero',
        'get_involved_hero', 'get_involved_cta',
        'get_involved_volunteer', 'get_involved_partner', 'get_involved_membership',
        'contribute_hero', 'contribute_impact', 'contribute_impact_items',
        'contribute_transparency', 'contribute_transparency_items',
        'contribute_why', 'contribute_why_stats',
    ];

    if ( !in_array( $table, $allowed ) ) {
        return [];
    }

    $sql = "SELECT * FROM `{$table}`";
    $params = [];

    if ( is_string( $conditions ) && !empty( $conditions ) ) {
        // String conditions are used only by internal hardcoded calls ( safe )
        $sql .= " WHERE {$conditions}";
    } elseif ( is_array( $conditions ) && !empty( $conditions ) ) {
        $where = [];
        foreach ( $conditions as $key => $value ) {
            $where[] = "`{$key}` = ?";
            $params[] = $value;
        }
        $sql .= ' WHERE ' . implode( ' AND ', $where );
        if ( !empty( $orderBy ) ) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ( $limit > 0 ) {
            $sql .= " LIMIT {$limit}";
        }
    } else {
        if ( !empty( $orderBy ) ) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ( $limit > 0 ) {
            $sql .= " LIMIT {$limit}";
        }
    }

    $stmt = $pdo->prepare( $sql );
    $stmt->execute( $params );
    return $stmt->fetchAll();
}

/**
* Fetch a single row by conditions
*/

function getRow( string $table, array $conditions = [] ): ?array {
    $rows = getRows( $table, $conditions, '', 1 );
    return !empty( $rows ) ? $rows[ 0 ] : null;
}

/**
* Whether a column exists on a table.
*
* Used before writing optional columns so an editor never throws ( or worse,
* silently drops a value ) while a migration is still landing on the server.
* Results are memoised per request; schema does not change mid-request.
*/

function tableHasColumn( string $table, string $column ): bool {
    global $pdo;
    static $cache = [];
    $key = $table . '.' . $column;
    if ( isset( $cache[ $key ] ) ) {
        return $cache[ $key ];
    }
    try {
        $stmt = $pdo->prepare( 'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?' );
        $stmt->execute( [ $table, $column ] );
        $cache[ $key ] = ( bool ) $stmt->fetchColumn();
    } catch ( Throwable $e ) {
        $cache[ $key ] = false;
    }
    return $cache[ $key ];
}

/**
* Fetch active programs
*/

function getActivePrograms(): array {
    return getRows( 'programs', [ 'is_active' => 1 ], 'sort_order ASC' );
}

/**
* Fetch a program by slug
*/

function getProgramBySlug( string $slug ): ?array {
    global $pdo;
    $stmt = $pdo->prepare( 'SELECT * FROM programs WHERE slug = ? AND is_active = 1' );
    $stmt->execute( [ $slug ] );
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
* Fetch interventions for a program
*/

function getProgramInterventions( int $programId ): array {
    return getRows( 'program_interventions', [ 'program_id' => $programId ], 'sort_order ASC' );
}

/**
* Fetch focus areas for a program
*/

function getProgramFocusAreas( int $programId ): array {
    return getRows( 'program_focus_areas', [ 'program_id' => $programId ], 'sort_order ASC' );
}

/**
* Fetch active projects
*/

function getActiveProjects(): array {
    return getRows( 'projects', [ 'is_active' => 1, 'status' => 'active' ], 'sort_order ASC' );
}

/**
* Fetch all active projects ( active + completed )
*/

function getAllProjects(): array {
    return getRows( 'projects', [ 'is_active' => 1 ], 'sort_order ASC' );
}

/**
* Fetch a project by slug
*/

function getProjectBySlug( string $slug ): ?array {
    global $pdo;
    $stmt = $pdo->prepare( 'SELECT * FROM projects WHERE slug = ? AND is_active = 1' );
    $stmt->execute( [ $slug ] );
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
* Fetch published news articles
*/

function getPublishedNews( int $limit = 0 ): array {
    return getRows( 'news_articles', [ 'is_published' => 1 ], 'published_at DESC', $limit );
}

/**
* Fetch news by category
*/

function getNewsByCategory( string $category, int $limit = 0 ): array {
    return getRows( 'news_articles', [ 'is_published' => 1, 'category' => $category ], 'published_at DESC', $limit );
}

/**
* Fetch active campaigns
*/

function getActiveCampaigns(): array {
    return getRows( 'campaigns', [ 'is_active' => 1, 'status' => 'active' ], 'sort_order ASC' );
}

/**
* Fetch team members by role type
*/

function getTeamByRole( string $roleType ): array {
    return getRows( 'team_members', [ 'is_active' => 1, 'role_type' => $roleType ], 'sort_order ASC' );
}

/**
* Fetch active partners
*/

function getActivePartners(): array {
    return getRows( 'partners', [ 'is_active' => 1 ], 'sort_order ASC' );
}

/**
* Fetch impact stats by page
*/

function getImpactStatsByPage( string $page ): array {
    return getRows( 'impact_stats', [ 'display_page' => $page ], 'sort_order ASC' );
}

/**
* Fetch active resources by type
*/

function getResourcesByType( string $type ): array {
    return getRows( 'resources', [ 'is_active' => 1, 'resource_type' => $type ], 'sort_order ASC' );
}

/**
* Fetch provinces
*/

function getProvinces(): array {
    return getRows( 'provinces', [ 'is_active' => 1 ], 'sort_order ASC' );
}

/**
* Fetch donation tiers
*/

function getDonationTiers(): array {
    return getRows( 'donation_tiers', [ 'is_active' => 1 ], 'sort_order ASC' );
}

/**
* Fetch active job listings
*/

function getActiveJobs(): array {
    return getRows( 'job_listings', [ 'is_active' => 1 ], 'posted_at DESC' );
}

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// SLUG GENERATION
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

function createSlug( string $text ): string {
    $text = strtolower( trim( $text ) );
    $text = preg_replace( '/[^a-z0-9-]/', '-', $text );
    $text = preg_replace( '/-+/', '-', $text );
    $text = trim( $text, '-' );
    return $text;
}

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// FORMATTING
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

function formatNumber( int $number ): string {
    if ( $number >= 1000000 ) {
        return number_format( $number / 1000000, 1 ) . 'M+';
    }
    if ( $number >= 1000 ) {
        return number_format( $number / 1000, 0 ) . 'K+';
    }
    return $number . '+';
}

function formatDate( string $date, string $format = 'M d, Y' ): string {
    return date( $format, strtotime( $date ) );
}

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// IMAGE HELPERS
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

/**
 * Whether a stored image path still resolves to a file on disk.
 *
 * Admin uploads used to live inside the deploy tree, so every pipeline sync
 * wiped the files the admin had just uploaded while the database kept their
 * paths. A stale path renders as a broken image icon, so callers use this to
 * fall back to something sensible instead.
 *
 * Remote and inline (data:/blob:) images cannot be verified locally and are
 * assumed to be present.
 */
function imageExists( string $path ): bool {
    if ( trim( $path ) === '' ) {
        return false;
    }

    if ( preg_match( '~^(?:https?:)?//|^(?:data|blob):~i', $path ) ) {
        return true;
    }

    static $memo = [];
    if ( array_key_exists( $path, $memo ) ) {
        return $memo[ $path ];
    }

    $relative = ltrim( str_replace( '\\', '/', $path ), '/' );

    // functions.php lives in includes/, so the project root is one level up.
    return $memo[ $path ] = is_file( dirname( __DIR__ ) . '/' . $relative );
}

/**
 * @param string $path     Stored path, absolute URL, or empty.
 * @param string $fallback Project-relative image used when $path is empty or
 *                         no longer exists on disk. Must be git-tracked, or it
 *                         disappears on the next deploy as well.
 */
function getImageUrl( string $path, string $fallback = 'assets/images/hero.jpg' ): string {
    if ( empty( $path ) ) {
        return siteUrl( $fallback );
    }

    if ( preg_match( '~^(?:https?:)?//|^(?:data|blob):~i', $path ) ) {
        return $path;
    }

    if ( strpos( $path, 'assets/' ) !== 0 && strpos( $path, 'uploads/' ) !== 0 ) {
        $path = 'assets/images/' . ltrim( $path, '/' );
    }

    // The stored path can outlive its file (see imageExists()). Serving a URL
    // that no longer resolves is a hard 404 and a broken image, so fall back
    // to a guaranteed-present, git-tracked asset instead.
    if ( !imageExists( $path ) ) {
        return siteUrl( $fallback );
    }

    return siteUrl( $path );
}

/**
 * Year a gallery photo was taken, or null when it cannot be determined.
 *
 * Resolution order: the date embedded in the filename, then the EXIF capture
 * timestamp. Lookups are memoised in a small JSON file next to the gallery so
 * EXIF is read at most once per image; if that folder is not writable the
 * helper simply recomputes (the filename half needs no I/O at all).
 *
 * @return int|null
 */
function photoYear( string $path ) {
    static $cache = null;
    static $writable = null;

    if ( $path === '' ) {
        return null;
    }

    $cacheFile = dirname( __DIR__ ) . '/assets/images/gallery/.photo-years.json';

    if ( $cache === null ) {
        $cache = [];
        if ( is_file( $cacheFile ) ) {
            $stored = json_decode( (string) file_get_contents( $cacheFile ), true );
            if ( is_array( $stored ) ) {
                $cache = $stored;
            }
        }
    }

    if ( array_key_exists( $path, $cache ) ) {
        return $cache[ $path ] === null ? null : (int) $cache[ $path ];
    }

    $year = photoYearForPath( $path );

    $cache[ $path ] = $year;

    if ( $writable !== false && is_dir( dirname( $cacheFile ) ) ) {
        if ( @file_put_contents( $cacheFile, json_encode( $cache ) ) === false ) {
            $writable = false;
        }
    }

    return $year;
}

/**
 * Resolve the capture year for one path, without consulting the cache.
 *
 * Only the last 30 years are accepted, and year matches must sit on digit
 * boundaries, so numbers inside Facebook-style filenames (…88620210164_n.jpg)
 * are never mistaken for a capture date.
 *
 * @return int|null
 */
function photoYearForPath( string $path ): ?int {
    $name = basename( $path );
    $now  = (int) date( 'Y' );
    $min  = $now - 30;

    $plausible = static function ( $year ) use ( $min, $now ): ?int {
        $year = (int) $year;
        return ( $year >= $min && $year <= $now ) ? $year : null;
    };

    // Compact camera date: 20120211_162050.jpg, IMG-20230315-WA0012.jpg
    if ( preg_match( '/(?<!\d)((?:19|20)\d{2})(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])(?!\d)/', $name, $m ) ) {
        $year = $plausible( $m[1] );
        if ( $year !== null ) {
            return $year;
        }
    }

    // Separated date: WhatsApp Image 2020-08-19 at 1.08.53 PM.jpeg
    if ( preg_match( '/(?<!\d)((?:19|20)\d{2})[-_.\/](0[1-9]|1[0-2])[-_.\/](0[1-9]|[12]\d|3[01])(?!\d)/', $name, $m ) ) {
        $year = $plausible( $m[1] );
        if ( $year !== null ) {
            return $year;
        }
    }

    // Standalone year token: IMG-2023-something.jpg
    if ( preg_match( '/(?<!\d)((?:19|20)\d{2})(?!\d)/', $name, $m ) ) {
        $year = $plausible( $m[1] );
        if ( $year !== null ) {
            return $year;
        }
    }

    // EXIF capture timestamp, for camera originals with no date in the name.
    if ( function_exists( 'exif_read_data' ) ) {
        $absolute = dirname( __DIR__ ) . '/' . ltrim( $path, '/' );
        if ( is_file( $absolute ) ) {
            $tags = @exif_read_data( $absolute, 'ANY_TAG', true );
            if ( is_array( $tags ) ) {
                foreach ( [ 'EXIF' => [ 'DateTimeOriginal', 'DateTimeDigitized' ], 'IFD0' => [ 'DateTime' ] ] as $group => $keys ) {
                    foreach ( $keys as $key ) {
                        $value = $tags[ $group ][ $key ] ?? null;
                        if ( is_string( $value ) && preg_match( '/^((?:19|20)\d{2})[:\-]/', $value, $m ) ) {
                            $year = $plausible( $m[1] );
                            if ( $year !== null ) {
                                return $year;
                            }
                        }
                    }
                }
            }
        }
    }

    return null;
}

/**
* Upload an image file
*/

function uploadImage( array $file, string $destination = 'uploads/' ): ?string {
    $allowed = [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml' ];

    if ( !in_array( $file[ 'type' ], $allowed ) ) {
        return null;
    }

    if ( $file[ 'error' ] !== UPLOAD_ERR_OK ) {
        return null;
    }

    $ext = pathinfo( $file[ 'name' ], PATHINFO_EXTENSION );
    $filename = uniqid( 'img_' ) . '.' . $ext;
    $path = SITE_ROOT . '/' . $destination;

    if ( !is_dir( $path ) ) {
        mkdir( $path, 0755, true );
    }

    if ( move_uploaded_file( $file[ 'tmp_name' ], $path . $filename ) ) {
        return $destination . $filename;
    }

    return null;
}

function handleUpload( array $file, string $type = 'images' ): ?string {
    if ( $file[ 'error' ] !== UPLOAD_ERR_OK ) return null;

    $maxSize = $type === 'images' ? 5 * 1024 * 1024 : 20 * 1024 * 1024;
    if ( $file[ 'size' ] > $maxSize ) return null;

    $allowedImages = [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml' ];
    $allowedFiles = [ 'application/pdf', 'application/zip', 'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/plain', 'text/csv' ];

    if ( $type === 'images' && !in_array( $file[ 'type' ], $allowedImages ) ) return null;
    if ( $type === 'files' && !in_array( $file[ 'type' ], $allowedFiles ) ) return null;

    $ext = pathinfo( $file[ 'name' ], PATHINFO_EXTENSION );
    $filename = uniqid( $type === 'images' ? 'img_' : 'file_' ) . '.' . $ext;

    // uploads/ is git-ignored and outside the deploy source list, so admin
    // uploads survive every push. See admin/ajax-upload.php for the reasoning.
    $dir = $type === 'images' ? 'uploads/images/' : 'uploads/files/';
    $fullPath = __DIR__ . '/../' . $dir;

    if ( !is_dir( $fullPath ) ) {
        mkdir( $fullPath, 0755, true );
    }

    if ( move_uploaded_file( $file[ 'tmp_name' ], $fullPath . $filename ) ) {
        return $dir . $filename;
    }

    return null;
}

/**
* Safely store an attachment uploaded through a PUBLIC form ( CV, evidence ).
*
* Unlike handleUpload() this never trusts the browser-supplied content type:
* it enforces an extension allow-list, sniffs the real MIME type server side
* and rejects anything that could be executed by the web server.
*
* @return string|null Relative path such as 'assets/files/uploads/file_ab12.pdf'
*/

function uploadPublicAttachment( array $file ): ?string {
    if ( ( $file[ 'error' ] ?? UPLOAD_ERR_NO_FILE ) !== UPLOAD_ERR_OK ) return null;

    $size = ( int ) ( $file[ 'size' ] ?? 0 );
    if ( $size <= 0 || $size > 20 * 1024 * 1024 ) return null;

    $tmp = $file[ 'tmp_name' ] ?? '';
    if ( $tmp === '' || !is_uploaded_file( $tmp ) ) return null;

    $allowedExt = [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt', 'csv', 'zip' ];
    $ext = strtolower( pathinfo( ( string ) ( $file[ 'name' ] ?? '' ), PATHINFO_EXTENSION ) );
    if ( !in_array( $ext, $allowedExt, true ) ) return null;

    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'image/jpeg',
        'image/png',
        'text/plain',
        'text/csv',
    ];

    $mime = '';
    if ( function_exists( 'finfo_open' ) ) {
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        if ( $finfo ) {
            $mime = ( string ) finfo_file( $finfo, $tmp );
            finfo_close( $finfo );
        }
    }
    if ( $mime === '' ) {
        $mime = ( string ) ( $file[ 'type' ] ?? '' );
    }
    if ( !in_array( $mime, $allowedMimes, true ) ) return null;

    $dir = 'uploads/files/';
    $fullPath = __DIR__ . '/../' . $dir;
    if ( !is_dir( $fullPath ) && !@mkdir( $fullPath, 0755, true ) ) return null;

    $filename = 'file_' . bin2hex( random_bytes( 8 ) ) . '.' . $ext;
    if ( !move_uploaded_file( $tmp, $fullPath . $filename ) ) return null;

    return $dir . $filename;
}

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// RESPONSE HELPERS
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

function jsonResponse( array $data, int $statusCode = 200 ): void {
    http_response_code( $statusCode );
    header( 'Content-Type: application/json' );
    echo json_encode( $data );
    exit;
}

function redirect( string $url ): void {
    header( "Location: {$url}" );
    exit;
}

function isPost(): bool {
    return $_SERVER[ 'REQUEST_METHOD' ] === 'POST';
}

function getPost( string $key, string $default = '' ): string {
    return isset( $_POST[ $key ] ) ? sanitize( $_POST[ $key ] ) : $default;
}

function getPostRaw( string $key, string $default = '' ): string {
    return trim( $_POST[ $key ] ?? $default );
}

function getPostInt( string $key, int $default = 0 ): int {
    return isset( $_POST[ $key ] ) ? ( int ) $_POST[ $key ] : $default;
}

function getPostFloat( string $key, float $default = 0.0 ): float {
    return isset( $_POST[ $key ] ) ? ( float ) $_POST[ $key ] : $default;
}

function getGet( string $key, string $default = '' ): string {
    return isset( $_GET[ $key ] ) ? sanitize( $_GET[ $key ] ) : $default;
}

function insertRow( string $table, array $data ): int {
    global $pdo;
    $columns = implode( ', ', array_keys( $data ) );
    $placeholders = implode( ', ', array_fill( 0, count( $data ), '?' ) );
    $stmt = $pdo->prepare( "INSERT INTO `$table` ($columns) VALUES ($placeholders)" );
    $stmt->execute( array_values( $data ) );
    return ( int ) $pdo->lastInsertId();
}

function updateRow( string $table, array $data, array $conditions = [] ): bool {
    global $pdo;
    $set = [];
    $values = [];
    foreach ( $data as $col => $val ) {
        $set[] = "`$col` = ?";
        $values[] = $val;
    }
    $where = [];
    foreach ( $conditions as $col => $val ) {
        $where[] = "`$col` = ?";
        $values[] = $val;
    }
    $sql = "UPDATE `$table` SET " . implode( ', ', $set );
    if ( !empty( $where ) ) {
        $sql .= ' WHERE ' . implode( ' AND ', $where );
    }
    $stmt = $pdo->prepare( $sql );
    return $stmt->execute( $values );
}

function deleteRow( string $table, array $conditions = [] ): bool {
    global $pdo;
    $where = [];
    $values = [];
    foreach ( $conditions as $col => $val ) {
        $where[] = "`$col` = ?";
        $values[] = $val;
    }
    $sql = "DELETE FROM `$table`";
    if ( !empty( $where ) ) {
        $sql .= ' WHERE ' . implode( ' AND ', $where );
    }
    $stmt = $pdo->prepare( $sql );
    return $stmt->execute( $values );
}
