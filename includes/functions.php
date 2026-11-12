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

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// BLOG
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

/**
* Blog landing-page copy managed from the dashboard.
*
* A missing table (first deploy before the migration has run) must not break
* the public site, so the defaults below are always a complete, usable page.
*
* @return array<string,mixed>
*/
function getBlogSettings(): array {
    static $settings = null;
    if ( $settings !== null ) {
        return $settings;
    }

    $defaults = [
        'hero_badge'     => 'Insights & Field Notes',
        'hero_title'     => 'Stories and ideas from the field',
        'hero_subtitle'  => 'Practical lessons, programme insight and human stories from WRC teams working across Pakistan.',
        'hero_image'     => 'assets/images/hero/news-hero.jpg',
        'intro_eyebrow'  => 'The WRC Blog',
        'intro_title'    => 'What we are learning, as we learn it',
        'intro_text'     => 'Long-form reflections from our programme, monitoring and emergency teams. Written for partners, practitioners and anyone who wants to understand how lasting change is built.',
        'cta_text'       => 'Partner with WRC',
        'cta_link'       => 'get-involved/contribute-with-us.php',
        'posts_per_page' => 6,
    ];

    $settings = $defaults;

    if ( isset( $GLOBALS[ 'pdo' ] ) && $GLOBALS[ 'pdo' ] instanceof PDO ) {
        try {
            $row = $GLOBALS[ 'pdo' ]->query( 'SELECT * FROM blog_settings ORDER BY id ASC LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
            if ( $row ) {
                // Keep every stored value, including an intentionally empty one,
                // so clearing a dashboard field is honoured on the public page.
                $settings = array_merge( $defaults, $row );
            }
        } catch ( Throwable $error ) {
            $settings = $defaults;
        }
    }

    return $settings;
}

/**
* Split a stored comma-separated tag list into clean, unique labels.
*
* @return list<string>
*/
function getBlogTagList( ?string $tags ): array {
    $clean = [];
    foreach ( explode( ',', (string) $tags ) as $tag ) {
        $tag = trim( preg_replace( '/\s+/', ' ', $tag ) );
        if ( $tag === '' ) {
            continue;
        }
        $key = strtolower( $tag );
        if ( !isset( $clean[ $key ] ) ) {
            $clean[ $key ] = $tag;
        }
    }
    return array_values( $clean );
}

/**
* Estimated reading time in minutes (220 words per minute, minimum 1).
*/
function getBlogReadingTime( string $content ): int {
    $text  = trim( strip_tags( $content ) );
    $words = $text === '' ? [] : preg_split( '/\s+/', $text );
    return max( 1, (int) ceil( count( $words ) / 220 ) );
}

/**
* A safe fallback summary when an editor leaves the excerpt blank.
*/
function getBlogExcerpt( array $post, int $limit = 180 ): string {
    $excerpt = trim( (string) ( $post[ 'excerpt' ] ?? '' ) );
    if ( $excerpt === '' ) {
        $excerpt = trim( strip_tags( (string) ( $post[ 'content' ] ?? '' ) ) );
    }
    if ( mb_strlen( $excerpt ) > $limit ) {
        $excerpt = mb_substr( $excerpt, 0, $limit );
        $excerpt = rtrim( $excerpt, " \t\n\r.,;:" ) . '…';
    }
    return $excerpt;
}

/**
* Fetch published blog posts with optional topic/search filters and paging.
*
* @return list<array<string,mixed>>
*/
function getBlogPosts( int $limit = 0, int $offset = 0, string $tag = '', string $search = '', int $excludeId = 0 ): array {
    global $pdo;

    $where  = [ "category = 'blog'", 'is_published = 1', 'slug IS NOT NULL', "slug <> ''" ];
    $params = [];

    if ( $tag !== '' ) {
        $where[]  = 'FIND_IN_SET(?, tags)';
        $params[] = $tag;
    }
    if ( $search !== '' ) {
        $where[]  = '(title LIKE ? OR excerpt LIKE ? OR content LIKE ? OR author LIKE ?)';
        $needle   = '%' . $search . '%';
        $params[] = $needle;
        $params[] = $needle;
        $params[] = $needle;
        $params[] = $needle;
    }
    if ( $excludeId > 0 ) {
        $where[]  = 'id <> ?';
        $params[] = $excludeId;
    }

    $sql = 'SELECT * FROM news_articles WHERE ' . implode( ' AND ', $where )
         . ' ORDER BY is_featured DESC, published_at DESC, id DESC';

    if ( $limit > 0 ) {
        $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . max( 0, $offset );
    }

    $stmt = $pdo->prepare( $sql );
    $stmt->execute( $params );
    return $stmt->fetchAll( PDO::FETCH_ASSOC );
}

/**
* Count published blog posts for the same filters used by getBlogPosts().
*/
function countBlogPosts( string $tag = '', string $search = '', int $excludeId = 0 ): int {
    global $pdo;

    $where  = [ "category = 'blog'", 'is_published = 1', 'slug IS NOT NULL', "slug <> ''" ];
    $params = [];

    if ( $tag !== '' ) {
        $where[]  = 'FIND_IN_SET(?, tags)';
        $params[] = $tag;
    }
    if ( $search !== '' ) {
        $where[]  = '(title LIKE ? OR excerpt LIKE ? OR content LIKE ? OR author LIKE ?)';
        $needle   = '%' . $search . '%';
        $params[] = $needle;
        $params[] = $needle;
        $params[] = $needle;
        $params[] = $needle;
    }
    if ( $excludeId > 0 ) {
        $where[]  = 'id <> ?';
        $params[] = $excludeId;
    }

    $stmt = $pdo->prepare( 'SELECT COUNT(*) FROM news_articles WHERE ' . implode( ' AND ', $where ) );
    $stmt->execute( $params );
    return (int) $stmt->fetchColumn();
}

/**
* The post pinned to the top of the blog. Falls back to the newest post when
* no editor has marked one as featured yet.
*/
function getFeaturedBlogPost(): ?array {
    global $pdo;

    try {
        $stmt = $pdo->query(
            "SELECT * FROM news_articles
              WHERE category = 'blog' AND is_published = 1
                AND slug IS NOT NULL AND slug <> ''
              ORDER BY is_featured DESC, published_at DESC, id DESC
              LIMIT 1"
        );
        $post = $stmt ? $stmt->fetch( PDO::FETCH_ASSOC ) : false;
        return $post ?: null;
    } catch ( Throwable $error ) {
        return null;
    }
}

/**
* Topic counts across all published blog posts, ordered by popularity.
*
* @return array<string,int>
*/
function getBlogTags(): array {
    $counts = [];
    foreach ( getBlogPosts() as $post ) {
        foreach ( getBlogTagList( $post[ 'tags' ] ?? '' ) as $tag ) {
            $counts[ $tag ] = ( $counts[ $tag ] ?? 0 ) + 1;
        }
    }

    uksort( $counts, static function ( $a, $b ) use ( $counts ) {
        if ( $counts[ $a ] !== $counts[ $b ] ) {
            return $counts[ $b ] <=> $counts[ $a ];
        }
        return strcasecmp( $a, $b );
    } );

    return $counts;
}

/**
* Fetch one published blog post by slug.
*/
function getBlogPostBySlug( string $slug ): ?array {
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT * FROM news_articles
          WHERE slug = ? AND category = 'blog' AND is_published = 1
          LIMIT 1"
    );
    $stmt->execute( [ $slug ] );
    $post = $stmt->fetch( PDO::FETCH_ASSOC );
    return $post ?: null;
}

/**
* Related posts for the blog detail page.
*
* @return list<array<string,mixed>>
*/
function getRelatedBlogPosts( int $excludeId, int $limit = 3 ): array {
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT * FROM news_articles
          WHERE category = 'blog' AND is_published = 1 AND id <> ?
            AND slug IS NOT NULL AND slug <> ''
          ORDER BY is_featured DESC, published_at DESC, id DESC
          LIMIT " . max( 1, $limit )
    );
    $stmt->execute( [ $excludeId ] );
    return $stmt->fetchAll( PDO::FETCH_ASSOC );
}

/**
* Canonical URL for a blog post.
*/
function getBlogPostUrl( array $post ): string {
    return siteBaseUrl() . 'news/blog-detail.php?slug=' . rawurlencode( (string) ( $post[ 'slug' ] ?? '' ) );
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

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// PARTNER-DRIVEN PROJECT GALLERIES
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

/**
 * Partner categories used by the public gallery and the dashboard. Adding a
 * new type here makes it available in the partner editor automatically.
 *
 * @return array<string,array<string,string>>
 */
function wrcPartnerTypeMap(): array {
    return [
        'funding' => [
            'label' => 'Funding Partners',
            'short' => 'Funding',
            'icon'  => 'fa-hand-holding-dollar',
            'blurb' => 'Donors, foundations and development agencies that finance WRC programmes.',
        ],
        'government' => [
            'label' => 'Government Partners',
            'short' => 'Government',
            'icon'  => 'fa-building-columns',
            'blurb' => 'Federal, provincial and district institutions that co-deliver public services.',
        ],
        'implementation' => [
            'label' => 'Implementation Partners',
            'short' => 'Implementation',
            'icon'  => 'fa-people-carry-box',
            'blurb' => 'Local and international organisations that deliver projects alongside WRC teams.',
        ],
        'collaborator' => [
            'label' => 'Collaborators',
            'short' => 'Collaborator',
            'icon'  => 'fa-handshake',
            'blurb' => 'Civil society, academic and community partners supporting our mission.',
        ],
    ];
}

function wrcPartnerTypeInfo( string $type ): array {
    $types = wrcPartnerTypeMap();

    return $types[ $type ] ?? [
        'label' => ucfirst( str_replace( '_', ' ', $type ) ),
        'short' => ucfirst( $type ),
        'icon'  => 'fa-handshake',
        'blurb' => 'Organisations working with WRC to serve communities.',
    ];
}

/** Two-letter monogram used when a partner has no usable logo or cover. */
function wrcPartnerInitials( string $name ): string {
    $words = preg_split( '/\s+/', trim( preg_replace( '/[^\p{L}\p{N}\s]/u', ' ', $name ) ) );
    $words = array_values( array_filter( (array) $words ) );

    if ( !$words ) {
        return 'WR';
    }

    $first = mb_substr( $words[ 0 ], 0, 1 );
    $last  = count( $words ) > 1 ? mb_substr( $words[ count( $words ) - 1 ], 0, 1 ) : mb_substr( $words[ 0 ], 1, 1 );

    return mb_strtoupper( $first . $last );
}

/** Cached SHOW TABLES check so a half-deployed database never fatals a page. */
function wrcTableExists( string $table ): bool {
    global $pdo;
    static $cache = [];

    if ( array_key_exists( $table, $cache ) ) {
        return $cache[ $table ];
    }

    try {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $statement->execute( [ $table ] );
        return $cache[ $table ] = (int) $statement->fetchColumn() > 0;
    } catch ( Throwable $error ) {
        return $cache[ $table ] = false;
    }
}

/**
 * Resolve a gallery image path to a URL. Bare filenames are treated as admin
 * uploads, while assets/ and uploads/ paths are passed through unchanged.
 */
function wrcMediaUrl( string $path, string $fallback = 'assets/images/hero/hero-slider-1.jpg' ): string {
    $path = trim( $path );

    if ( $path !== '' && !preg_match( '~^(?:https?:)?//|^(?:data|blob):|^(?:assets|uploads)/~i', $path ) ) {
        $path = 'uploads/images/' . ltrim( $path, '/' );
    }

    return getImageUrl( $path, $fallback );
}

/**
 * Comparison key that folds spelling variants of the same organisation.
 * Punctuation, casing and a few known legacy names are normalised away.
 */
function wrcPartnerAliasKey( string $name ): string {
    $key = strtolower( trim( $name ) );
    $key = preg_replace( '/&(?:amp;)?/i', 'and', $key );
    $key = preg_replace( '/[^a-z0-9]+/', '', (string) $key );

    $aliases = [
        'healthdepartment' => 'departmentofhealth',
        'thefederallyadministeredtribalareasdevelopmentauthority' => 'fatadevelopmentauthority',
        'fatada' => 'fatadevelopmentauthority',
        'zakatfoundationofamericazfa' => 'zakatfoundationofamerica',
    ];

    return $aliases[ $key ] ?? (string) $key;
}

/**
 * All public partner cards, with the counts the gallery landing page needs.
 * A partner is only listed when it leads to at least one project or
 * photograph; logo-only records would render empty "0 projects / 0 photos"
 * cards on the public gallery.
 */
function wrcGalleryPartners(): array {
    global $pdo;

    if ( !wrcTableExists( 'partners' ) ) {
        return [];
    }

    $hasLinks = wrcTableExists( 'partner_projects' );
    $hasMedia = wrcTableExists( 'project_media' );

    if ( $hasLinks ) {
        $projectCount = '(SELECT COUNT(*) FROM partner_projects pp JOIN projects pr ON pr.slug = pp.project_slug AND pr.is_active = 1 WHERE pp.partner_id = p.id)';
        $photoCount   = $hasMedia
            ? '(SELECT COUNT(*) FROM project_media pm WHERE pm.is_active = 1 AND pm.project_slug IN (SELECT pp2.project_slug FROM partner_projects pp2 WHERE pp2.partner_id = p.id))'
            : '0';
        $sql = "SELECT p.*, {$projectCount} AS project_count, {$photoCount} AS photo_count
                  FROM partners p
                 WHERE p.is_active = 1
                 ORDER BY project_count DESC, p.sort_order ASC, p.name ASC";
    } else {
        $sql = 'SELECT p.*, 0 AS project_count, 0 AS photo_count
                  FROM partners p
                 WHERE p.is_active = 1
                 ORDER BY p.sort_order ASC, p.name ASC';
    }

    $partners = $pdo->query( $sql )->fetchAll( PDO::FETCH_ASSOC );

    if ( $hasLinks ) {
        $coverStatement = $pdo->prepare(
            "SELECT pr.hero_image
               FROM partner_projects pp
               JOIN projects pr ON pr.slug = pp.project_slug
              WHERE pp.partner_id = ? AND pr.is_active = 1 AND pr.hero_image IS NOT NULL AND pr.hero_image <> ''
              ORDER BY pp.sort_order ASC, pr.sort_order ASC
              LIMIT 1"
        );
    }

    $visible = [];
    foreach ( $partners as $partner ) {
        $projectCount = (int) $partner['project_count'];
        $photoCount   = (int) $partner['photo_count'];

        // Hide partners that would show an empty card. This is evaluated from
        // the live counts, so deactivating a project removes its partner card
        // automatically without any extra dashboard work.
        if ( $projectCount === 0 && $photoCount === 0 ) {
            continue;
        }

        $cover = trim( (string) ( $partner['cover_image'] ?? '' ) );
        if ( $cover === '' && $hasLinks && isset( $coverStatement ) ) {
            $coverStatement->execute( [ (int) $partner['id'] ] );
            $cover = (string) ( $coverStatement->fetchColumn() ?: '' );
        }

        $partner['cover_path']  = $cover;
        $partner['type_info']   = wrcPartnerTypeInfo( (string) ( $partner['partner_type'] ?? 'collaborator' ) );
        $partner['initials']    = wrcPartnerInitials( (string) $partner['name'] );
        $visible[] = $partner;
    }

    // Older deployments accumulated the same organisation more than once
    // (for example a logo-only "Health Department" beside the donor-linked
    // record, or "Smart Aid" beside "SmartAID"). Fold those into the record
    // that actually has a gallery so visitors never see duplicate cards.
    $unique = [];
    foreach ( $visible as $partner ) {
        $key = wrcPartnerAliasKey( (string) $partner['name'] );

        if ( !isset( $unique[ $key ] ) ) {
            $unique[ $key ] = $partner;
            continue;
        }

        $score = static function ( array $row ): array {
            return [
                (int) $row['project_count'],
                (int) $row['photo_count'],
                trim( (string) ( $row['cover_image'] ?? '' ) ) !== '' ? 1 : 0,
                trim( (string) ( $row['logo_path'] ?? '' ) ) !== '' ? 1 : 0,
                -(int) $row['id'],
            ];
        };

        if ( $score( $partner ) > $score( $unique[ $key ] ) ) {
            $unique[ $key ] = $partner;
        }
    }

    return array_values( $unique );
}

/** One active partner with project/photo counts. */
function wrcPartnerBySlug( string $slug ): ?array {
    global $pdo;

    if ( $slug === '' || !wrcTableExists( 'partners' ) ) {
        return null;
    }

    $statement = $pdo->prepare( 'SELECT * FROM partners WHERE slug = ? AND is_active = 1 LIMIT 1' );
    $statement->execute( [ $slug ] );
    $partner = $statement->fetch( PDO::FETCH_ASSOC );

    if ( !$partner ) {
        return null;
    }

    $hasLinks = wrcTableExists( 'partner_projects' );
    $hasMedia = wrcTableExists( 'project_media' );

    $projectCount = 0;
    $photoCount   = 0;

    if ( $hasLinks ) {
        $count = $pdo->prepare(
            'SELECT COUNT(*) FROM partner_projects pp JOIN projects pr ON pr.slug = pp.project_slug AND pr.is_active = 1 WHERE pp.partner_id = ?'
        );
        $count->execute( [ (int) $partner['id'] ] );
        $projectCount = (int) $count->fetchColumn();

        if ( $hasMedia ) {
            $count = $pdo->prepare(
                'SELECT COUNT(*) FROM project_media pm
                  WHERE pm.is_active = 1 AND pm.project_slug IN (
                        SELECT pp2.project_slug FROM partner_projects pp2 WHERE pp2.partner_id = ?
                  )'
            );
            $count->execute( [ (int) $partner['id'] ] );
            $photoCount = (int) $count->fetchColumn();
        }
    }

    $partner['project_count'] = $projectCount;
    $partner['photo_count']   = $photoCount;
    $partner['type_info']     = wrcPartnerTypeInfo( (string) ( $partner['partner_type'] ?? 'collaborator' ) );
    $partner['initials']      = wrcPartnerInitials( (string) $partner['name'] );

    return $partner;
}

/** Projects linked to one partner, ready to render as gallery cards. */
function wrcPartnerProjects( int $partnerId ): array {
    global $pdo;

    if ( $partnerId < 1 || !wrcTableExists( 'partner_projects' ) ) {
        return [];
    }

    $hasMedia = wrcTableExists( 'project_media' );
    $mediaSql = $hasMedia
        ? '(SELECT COUNT(*) FROM project_media pm WHERE pm.project_slug = pr.slug AND pm.is_active = 1)'
        : '0';

    $statement = $pdo->prepare(
        "SELECT pr.*, {$mediaSql} AS media_count
           FROM partner_projects pp
           JOIN projects pr ON pr.slug = pp.project_slug
          WHERE pp.partner_id = ? AND pr.is_active = 1
          ORDER BY pp.sort_order ASC, pr.sort_order ASC, pr.id ASC"
    );
    $statement->execute( [ $partnerId ] );

    return $statement->fetchAll( PDO::FETCH_ASSOC );
}

/**
 * Partner records for a project. Falls back to the donor sub-table so a project
 * imported without partner links still shows who supported it.
 */
function wrcProjectPartners( string $projectSlug ): array {
    global $pdo;

    if ( $projectSlug === '' ) {
        return [];
    }

    if ( wrcTableExists( 'partner_projects' ) ) {
        $statement = $pdo->prepare(
            'SELECT p.*
               FROM partner_projects pp
               JOIN partners p ON p.id = pp.partner_id
              WHERE pp.project_slug = ? AND p.is_active = 1
              ORDER BY p.sort_order ASC, p.name ASC'
        );
        $statement->execute( [ $projectSlug ] );
        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        if ( $rows ) {
            foreach ( $rows as &$row ) {
                $row['type_info'] = wrcPartnerTypeInfo( (string) ( $row['partner_type'] ?? 'collaborator' ) );
                $row['initials']  = wrcPartnerInitials( (string) $row['name'] );
            }
            unset( $row );

            return $rows;
        }
    }

    if ( !wrcTableExists( 'project_donors' ) ) {
        return [];
    }

    $statement = $pdo->prepare(
        'SELECT DISTINCT pd.donor_name
           FROM project_donors pd
           JOIN projects pr ON pr.id = pd.project_id
          WHERE pr.slug = ?
          ORDER BY pd.sort_order ASC, pd.donor_name ASC'
    );
    $statement->execute( [ $projectSlug ] );

    $fallback = [];
    foreach ( $statement->fetchAll( PDO::FETCH_COLUMN ) as $name ) {
        $name = trim( (string) $name );
        if ( $name === '' ) {
            continue;
        }
        $fallback[] = [
            'id'           => 0,
            'name'         => $name,
            'slug'         => '',
            'partner_type' => 'funding',
            'cover_image'  => '',
            'logo_path'    => '',
            'initials'     => wrcPartnerInitials( $name ),
            'type_info'    => wrcPartnerTypeInfo( 'funding' ),
        ];
    }

    return $fallback;
}

/** Active gallery rows for a project, newest ordering preserved. */
function wrcProjectMedia( string $projectSlug ): array {
    global $pdo;

    if ( $projectSlug === '' || !wrcTableExists( 'project_media' ) ) {
        return [];
    }

    $statement = $pdo->prepare(
        'SELECT * FROM project_media
          WHERE project_slug = ? AND is_active = 1
          ORDER BY sort_order ASC, id ASC'
    );
    $statement->execute( [ $projectSlug ] );

    $media = [];
    foreach ( $statement->fetchAll( PDO::FETCH_ASSOC ) as $row ) {
        $path = trim( (string) ( $row['image_path'] ?? '' ) );
        if ( $path === '' || !imageExists( $path ) ) {
            continue;
        }
        $row['image_path'] = $path;
        $media[] = $row;
    }

    return $media;
}

/** Best available cover: uploaded cover, first project photo, then logo. */
function wrcPartnerCoverPath( array $partner, array $projects = [] ): string {
    $cover = trim( (string) ( $partner['cover_image'] ?? '' ) );
    if ( $cover !== '' && imageExists( $cover ) ) {
        return $cover;
    }

    foreach ( $projects as $project ) {
        $hero = trim( (string) ( $project['hero_image'] ?? '' ) );
        if ( $hero !== '' && imageExists( $hero ) ) {
            return $hero;
        }
    }

    return '';
}

/** Human year label from a project row: "2023" or "2021–2023". */
function wrcProjectYearLabel( array $project ): string {
    $start = !empty( $project['start_date'] ) ? (int) substr( (string) $project['start_date'], 0, 4 ) : 0;
    $end   = !empty( $project['end_date'] ) ? (int) substr( (string) $project['end_date'], 0, 4 ) : 0;

    if ( $start && $end ) {
        return $start === $end ? (string) $start : $start . '–' . $end;
    }

    return (string) ( $start ?: $end );
}

/** Status badge metadata shared by the partner and project gallery pages. */
function wrcProjectStatusMeta( string $status ): array {
    $map = [
        'active'    => [ 'label' => 'Ongoing', 'classes' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500' ],
        'completed' => [ 'label' => 'Completed', 'classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500' ],
        'upcoming'  => [ 'label' => 'Upcoming', 'classes' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500' ],
    ];

    return $map[ $status ] ?? [ 'label' => ucfirst( $status ), 'classes' => 'bg-slate-50 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400' ];
}


/**
* Fetch impact stats by page
*/

function getImpactStatsByPage( string $page ): array {
    return getRows( 'impact_stats', [ 'display_page' => $page ], 'sort_order ASC' );
}

/**
 * Maps a project category (projects.category) onto the four published
 * programme sectors. Keys are lowercase so stored categories match regardless
 * of capitalisation.
 *
 * Health  = Health + Emergency Response
 * Skills  = Education + Livelihood
 * Food    = Food Security
 * WASH    = WASH / Water
 *
 * @return array<string,string>
 */
function wrcBeneficiarySectorMap(): array {
    return [
        'health'              => 'health',
        'emergency response'  => 'health',
        'emergency'           => 'health',
        'education'           => 'skills',
        'livelihood'          => 'skills',
        'livelihoods'         => 'skills',
        'skills'              => 'skills',
        'food security'       => 'food',
        'food'                => 'food',
        'wash'                => 'wash',
        'water'               => 'wash',
        'water & sanitation'  => 'wash',
    ];
}

/**
 * Canonical beneficiary figures published across the website.
 *
 * The `projects` table is the single source of truth: every active project
 * carries its own beneficiaries_count, and adding or editing a project in the
 * dashboard changes these figures automatically. Sector totals follow the
 * approved programme mapping; the total is every active project combined.
 *
 * Results are cached for the rest of the request. Pass $refresh = true after
 * changing projects (the sync below does this).
 *
 * @return array{health:int,skills:int,food:int,wash:int,total:int}
 */
function wrcBeneficiaryFigures( bool $refresh = false ): array {
    static $cached = null;

    if ( is_array( $cached ) && !$refresh ) {
        return $cached;
    }

    // Safety net for a fresh install whose projects have not been imported yet.
    $known = [
        'health' => 1035830,
        'skills' => 2778,
        'food'   => 5100,
        'wash'   => 2500,
        'total'  => 1046208,
    ];

    $figures = [ 'health' => 0, 'skills' => 0, 'food' => 0, 'wash' => 0, 'total' => 0 ];

    try {
        $pdo = $GLOBALS['pdo'] ?? null;
        if ( !( $pdo instanceof PDO ) ) {
            return $cached = $known;
        }

        $projectCount = (int) $pdo->query( 'SELECT COUNT(*) FROM projects WHERE is_active = 1' )->fetchColumn();
        if ( $projectCount === 0 ) {
            return $cached = $known;
        }

        $rows = $pdo->query(
            'SELECT category, SUM(beneficiaries_count) AS total
               FROM projects
              WHERE is_active = 1 AND beneficiaries_count > 0
              GROUP BY category'
        )->fetchAll( PDO::FETCH_ASSOC );

        $sectorMap = wrcBeneficiarySectorMap();

        foreach ( $rows as $row ) {
            $count = (int) ( $row['total'] ?? 0 );
            if ( $count <= 0 ) {
                continue;
            }

            $figures['total'] += $count;

            $category = strtolower( trim( (string) ( $row['category'] ?? '' ) ) );
            if ( isset( $sectorMap[ $category ] ) ) {
                $figures[ $sectorMap[ $category ] ] += $count;
            }
        }
    } catch ( Throwable $error ) {
        return $cached = $known;
    }

    return $cached = $figures;
}

/** The published headline number of beneficiaries reached. */
function wrcBeneficiaryTotal(): int {
    return wrcBeneficiaryFigures()[ 'total' ];
}

/**
 * Project-derived headline figures for the homepage hero strip.
 *
 * Every number is calculated from the live projects table, so publishing,
 * completing or deactivating a project updates the homepage automatically.
 * Beneficiaries come from the canonical figures so the whole site agrees.
 *
 * @return array{projects:int,beneficiaries:int,districts:int,partners:int,sectors:int,provinces:int}
 */
function wrcProjectImpactSummary( bool $refresh = false ): array {
    static $cached = null;

    if ( is_array( $cached ) && !$refresh ) {
        return $cached;
    }

    $figures = wrcBeneficiaryFigures( $refresh );

    $summary = [
        'projects'      => 0,
        'beneficiaries' => (int) $figures[ 'total' ],
        'districts'     => 0,
        'partners'      => 0,
        'sectors'       => 0,
        'provinces'     => 0,
    ];

    $pdo = $GLOBALS[ 'pdo' ] ?? null;
    if ( !( $pdo instanceof PDO ) ) {
        return $cached = $summary;
    }

    try {
        $row = $pdo->query(
            "SELECT COUNT(*) AS projects,
                    COUNT(DISTINCT NULLIF(category, '')) AS sectors,
                    COUNT(DISTINCT NULLIF(province_id, 0)) AS provinces
               FROM projects
              WHERE is_active = 1"
        )->fetch( PDO::FETCH_ASSOC );

        $summary[ 'projects' ]  = (int) ( $row[ 'projects' ] ?? 0 );
        $summary[ 'sectors' ]   = (int) ( $row[ 'sectors' ] ?? 0 );
        $summary[ 'provinces' ] = (int) ( $row[ 'provinces' ] ?? 0 );

        // A single district field can hold several places, e.g.
        // "Buner, Swabi & Nowshera" or "Malakand & Swat". Split before counting.
        $locations = $pdo->query(
            "SELECT district FROM projects
              WHERE is_active = 1 AND district IS NOT NULL AND district <> ''"
        )->fetchAll( PDO::FETCH_COLUMN );

        $districts = [];
        foreach ( $locations as $location ) {
            $parts = preg_split( '/\s*(?:,|&|\band\b|\/)\s*/i', (string) $location );
            foreach ( (array) $parts as $part ) {
                $part = trim( $part );
                if ( $part !== '' ) {
                    $districts[ strtolower( $part ) ] = true;
                }
            }
        }
        $summary[ 'districts' ] = count( $districts );

        $partnerRows = $pdo->query(
            "SELECT DISTINCT p.id, p.name
               FROM partners p
               INNER JOIN partner_projects pp ON pp.partner_id = p.id
               INNER JOIN projects pr ON pr.slug = pp.project_slug AND pr.is_active = 1
              WHERE p.is_active = 1"
        )->fetchAll( PDO::FETCH_ASSOC );

        // Fold spelling variants exactly as the public gallery does, so the
        // headline count matches the cards visitors can actually see.
        $partnerKeys = [];
        foreach ( $partnerRows as $partnerRow ) {
            $partnerKeys[ wrcPartnerAliasKey( (string) $partnerRow[ 'name' ] ) ] = true;
        }
        $summary[ 'partners' ] = count( $partnerKeys );
    } catch ( Throwable $error ) {
        return $cached = $summary;
    }

    return $cached = $summary;
}

/** Format a beneficiary count consistently: 1,046,208. */
function wrcFormatBeneficiaries( int $number ): string {
    return number_format( $number );
}

/**
 * Resolve a stat label to one of the beneficiary figures.
 *
 * Returns the integer figure so callers can choose their own formatting
 * (counter targets must stay as raw digits, table values are comma-formatted).
 */
function wrcBeneficiaryFigureForLabel( string $label, array $figures ): ?int {
    $key = strtolower( preg_replace( '/\s+/', ' ', trim( $label ) ) );

    $map = [
        'beneficiaries reached'     => 'total',
        'beneficiaries'             => 'total',
        'total beneficiaries'       => 'total',
        'lives impacted'            => 'total',
        'people reached'            => 'total',
        'people impacted'           => 'total',
        'health'                    => 'health',
        'health beneficiaries'      => 'health',
        'healthcare visits'         => 'health',
        'healthcare'                => 'health',
        'skills'                    => 'skills',
        'skills beneficiaries'      => 'skills',
        'education'                 => 'skills',
        'education beneficiaries'   => 'skills',
        'children enrolled'         => 'skills',
        'vocational training'       => 'skills',
        'food'                      => 'food',
        'food beneficiaries'        => 'food',
        'food packages distributed' => 'food',
        'food security'             => 'food',
        'wash'                      => 'wash',
        'wash beneficiaries'        => 'wash',
        'water wells installed'     => 'wash',
        'clean water'               => 'wash',
        'water & sanitation'        => 'wash',
    ];

    if ( !isset( $map[ $key ] ) ) {
        return null;
    }

    return (int) $figures[ $map[ $key ] ];
}

/**
 * Apply the live figures to a single stats entry (array) in any stored shape.
 * Existing value keys are rewritten; unknown labels are returned untouched.
 */
function wrcBeneficiaryApplyStatEntry( array $entry, array $figures ): array {
    $label = (string) ( $entry['label'] ?? $entry['stat_label'] ?? $entry['metric_label'] ?? '' );
    if ( $label === '' ) {
        return $entry;
    }

    $figure = wrcBeneficiaryFigureForLabel( $label, $figures );
    if ( $figure === null ) {
        return $entry;
    }

    foreach ( [ 'value', 'stat_value', 'metric_value', 'target' ] as $key ) {
        if ( !array_key_exists( $key, $entry ) ) {
            continue;
        }
        // Impact counters run parseInt() on data-target, so they stay raw digits.
        $entry[ $key ] = $key === 'target' ? (string) $figure : number_format( $figure );
    }

    return $entry;
}

/**
 * Walk a decoded JSON document (Impact content, homepage sections, geographic
 * content) and refresh every stats entry plus "over N people" prose.
 */
function wrcBeneficiaryApplyToTree( $node, array $figures ) {
    if ( is_string( $node ) ) {
        return preg_replace(
            '/over\s+[\d,]+\s+(?:people|lives)/i',
            'over ' . number_format( $figures['total'] ) . ' people',
            $node
        );
    }

    if ( !is_array( $node ) ) {
        return $node;
    }

    if ( isset( $node['label'] ) || isset( $node['stat_label'] ) || isset( $node['metric_label'] ) ) {
        $node = wrcBeneficiaryApplyStatEntry( $node, $figures );
    }

    foreach ( $node as $key => $child ) {
        if ( is_array( $child ) || is_string( $child ) ) {
            $node[ $key ] = wrcBeneficiaryApplyToTree( $child, $figures );
        }
    }

    return $node;
}

/**
 * Split the published total across the province cards.
 *
 * Projects that name a province contribute directly to it. Any remainder
 * (projects with no province set, or projects not yet imported) is shared in
 * proportion to the existing card values, so the cards always add up to the
 * published total exactly.
 *
 * @return array<int,int> province id => beneficiary count
 */
function wrcBeneficiaryProvinceSplit( PDO $pdo, int $total ): array {
    $provinces = $pdo->query(
        'SELECT id, beneficiaries_count FROM provinces ORDER BY sort_order ASC, id ASC'
    )->fetchAll( PDO::FETCH_ASSOC );

    if ( !$provinces ) {
        return [];
    }

    $assigned    = [];
    $assignedSum = 0;

    $rows = $pdo->query(
        'SELECT province_id, SUM(beneficiaries_count) AS total
           FROM projects
          WHERE is_active = 1 AND province_id IS NOT NULL AND beneficiaries_count > 0
          GROUP BY province_id'
    )->fetchAll( PDO::FETCH_ASSOC );

    foreach ( $rows as $row ) {
        $id = (int) $row['province_id'];
        $assigned[ $id ] = (int) $row['total'];
        $assignedSum += (int) $row['total'];
    }

    $remaining = max( 0, $total - $assignedSum );

    // Share the remainder across provinces that have no projects of their own.
    // If every province already has projects, spread it across all of them.
    $pool = [];
    foreach ( $provinces as $province ) {
        if ( !isset( $assigned[ (int) $province['id'] ] ) ) {
            $pool[] = $province;
        }
    }
    if ( !$pool ) {
        $pool = $provinces;
    }

    $weights   = [];
    $weightSum = 0;

    foreach ( $provinces as $province ) {
        $weights[ (int) $province['id'] ] = max( 0, (int) $province['beneficiaries_count'] );
    }
    foreach ( $pool as $province ) {
        $weightSum += $weights[ (int) $province['id'] ];
    }

    $targets = [];
    $allocated = 0;
    $poolIds = [];
    foreach ( $pool as $province ) {
        $poolIds[ (int) $province['id'] ] = true;
    }
    $lastPoolId = (int) $pool[ count( $pool ) - 1 ]['id'];

    foreach ( $provinces as $province ) {
        $id   = (int) $province['id'];
        $share = 0;

        if ( $remaining > 0 && isset( $poolIds[ $id ] ) ) {
            if ( $id === $lastPoolId ) {
                $share = $remaining - $allocated;
            } elseif ( $weightSum > 0 ) {
                $share = (int) round( $remaining * $weights[ $id ] / $weightSum );
            } else {
                $share = (int) round( $remaining / count( $pool ) );
            }
            $allocated += $share;
        }

        $targets[ $id ] = ( $assigned[ $id ] ?? 0 ) + $share;
    }

    return $targets;
}

/**
 * Re-publish the project-derived beneficiary figures into every
 * dashboard-managed table that stores a copy.
 *
 * Runs after a project is created, edited or deleted, and on every deployment.
 * Only beneficiary-labelled values are rewritten, so budgets, partner counts,
 * districts and other metrics are never touched. Failures are reported but
 * never interrupt the caller.
 *
 * @return array<int,string> list of what was updated
 */
function wrcSyncBeneficiaryStats( PDO $pdo ): array {
    $figures = wrcBeneficiaryFigures( true );
    $total   = number_format( $figures['total'] );
    $applied = [];

    $tableExists = static function ( string $table ) use ( $pdo ): bool {
        static $cache = [];
        if ( !isset( $cache[ $table ] ) ) {
            try {
                $statement = $pdo->prepare(
                    'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
                );
                $statement->execute( [ $table ] );
                $cache[ $table ] = (int) $statement->fetchColumn() > 0;
            } catch ( Throwable $error ) {
                $cache[ $table ] = false;
            }
        }
        return $cache[ $table ];
    };

    $updateLabelRows = static function ( string $table, string $labelColumn, string $valueColumn ) use ( $pdo, $figures, $tableExists ): int {
        if ( !$tableExists( $table ) ) {
            return 0;
        }

        $rows   = $pdo->query( "SELECT id, `{$labelColumn}` AS label FROM `{$table}`" )->fetchAll( PDO::FETCH_ASSOC );
        $update = $pdo->prepare( "UPDATE `{$table}` SET `{$valueColumn}` = ? WHERE id = ?" );
        $count  = 0;

        foreach ( $rows as $row ) {
            $figure = wrcBeneficiaryFigureForLabel( (string) $row['label'], $figures );
            if ( $figure === null ) {
                continue;
            }
            $update->execute( [ number_format( $figure ), $row['id'] ] );
            $count += $update->rowCount();
        }

        return $count;
    };

    // ---------------------------------------------------- Impact page JSON --
    if ( $tableExists( 'impact_content' ) ) {
        try {
            $row = $pdo->query( 'SELECT id, content FROM impact_content ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
            if ( $row && trim( (string) $row['content'] ) !== '' ) {
                $decoded = json_decode( (string) $row['content'], true );
                if ( is_array( $decoded ) ) {
                    $updated = wrcBeneficiaryApplyToTree( $decoded, $figures );
                    if ( $updated !== $decoded ) {
                        $pdo->prepare( 'UPDATE impact_content SET content = ? WHERE id = ?' )
                            ->execute( [ json_encode( $updated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $row['id'] ] );
                        $applied[] = 'Impact page counters';
                    }
                }
            }
        } catch ( Throwable $error ) {
            // Keep going: a broken content row must not block the other pages.
        }
    }

    // ------------------------------------------- impact_stats (all pages) ---
    if ( $updateLabelRows( 'impact_stats', 'stat_label', 'stat_value' ) ) {
        $applied[] = 'impact stat tables';
    }

    // ------------------------------------------------ single-metric strips --
    $labelTables = [
        [ 'geographic_presence_stats', 'stat_label', 'stat_value' ],
        [ 'history_stats_items', 'stat_label', 'stat_value' ],
        [ 'contribute_why_stats', 'stat_label', 'stat_value' ],
        [ 'about_transparency_stats', 'stat_label', 'stat_value' ],
        [ 'projects_dashboard_metrics', 'metric_label', 'metric_value' ],
    ];

    foreach ( $labelTables as [ $table, $labelColumn, $valueColumn ] ) {
        try {
            if ( $updateLabelRows( $table, $labelColumn, $valueColumn ) ) {
                $applied[] = $table;
            }
        } catch ( Throwable $error ) {
            // Table may not exist on an older install; skip it.
        }
    }

    // ------------------------------------------ homepage / JSON sections ----
    if ( $tableExists( 'homepage_sections' ) ) {
        try {
            $sections = $pdo->query(
                'SELECT id, section_key, section_subtitle, content FROM homepage_sections'
            )->fetchAll( PDO::FETCH_ASSOC );

            $updateSection = $pdo->prepare(
                'UPDATE homepage_sections SET section_subtitle = ?, content = ? WHERE id = ?'
            );

            foreach ( $sections as $section ) {
                $subtitle = (string) ( $section['section_subtitle'] ?? '' );
                $content  = (string) ( $section['content'] ?? '' );
                $changed  = false;

                $newSubtitle = preg_replace(
                    '/over\s+[\d,]+\s+(?:people|lives)/i',
                    'over ' . $total . ' people',
                    $subtitle
                );
                if ( $newSubtitle !== null && $newSubtitle !== $subtitle ) {
                    $subtitle = $newSubtitle;
                    $changed  = true;
                }

                if ( trim( $content ) !== '' ) {
                    $decoded = json_decode( $content, true );
                    if ( is_array( $decoded ) ) {
                        $updated = wrcBeneficiaryApplyToTree( $decoded, $figures );
                        if ( $updated !== $decoded ) {
                            $content = json_encode( $updated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                            $changed = true;
                        }
                    }
                }

                if ( $changed ) {
                    $updateSection->execute( [ $subtitle, $content, $section['id'] ] );
                    $applied[] = 'homepage section: ' . ( $section['section_key'] ?? $section['id'] );
                }
            }
        } catch ( Throwable $error ) {
            // Non-fatal.
        }
    }

    // --------------------------------------------- where-we-work content -----
    if ( $tableExists( 'where_we_work_content' ) ) {
        try {
            $row = $pdo->query( 'SELECT id, content FROM where_we_work_content ORDER BY id LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
            if ( $row && trim( (string) $row['content'] ) !== '' ) {
                $decoded = json_decode( (string) $row['content'], true );
                if ( is_array( $decoded ) ) {
                    $updated = wrcBeneficiaryApplyToTree( $decoded, $figures );
                    if ( $updated !== $decoded ) {
                        $pdo->prepare( 'UPDATE where_we_work_content SET content = ? WHERE id = ?' )
                            ->execute( [ json_encode( $updated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $row['id'] ] );
                        $applied[] = 'where-we-work content';
                    }
                }
            }
        } catch ( Throwable $error ) {
            // Non-fatal.
        }
    }

    // ---------------------------------------------------------- provinces ---
    try {
        $targets = wrcBeneficiaryProvinceSplit( $pdo, $figures['total'] );
        if ( $targets ) {
            $existing = [];
            foreach ( $pdo->query( 'SELECT id, beneficiaries_count FROM provinces' )->fetchAll( PDO::FETCH_ASSOC ) as $province ) {
                $existing[ (int) $province['id'] ] = (int) $province['beneficiaries_count'];
            }

            $updateProvince = $pdo->prepare( 'UPDATE provinces SET beneficiaries_count = ? WHERE id = ?' );
            $changed = 0;

            foreach ( $targets as $id => $count ) {
                if ( ( $existing[ $id ] ?? null ) === (int) $count ) {
                    continue;
                }
                $updateProvince->execute( [ $count, $id ] );
                $changed++;
            }

            if ( $changed ) {
                $applied[] = 'province beneficiary split';
            }
        }
    } catch ( Throwable $error ) {
        // Non-fatal.
    }

    // ------------------------------------------------ legacy SDG copy -------
    if ( $tableExists( 'sdg_contributions' ) ) {
        try {
            $sdgCopy = [
                3 => number_format( $figures['health'] ) . ' health beneficiaries reached through free medical camps, clinical services and emergency care across 4 provinces.',
                4 => number_format( $figures['skills'] ) . ' skills beneficiaries trained through education, vocational and livelihood programmes.',
                5 => number_format( $figures['wash'] ) . ' WASH beneficiaries reached with clean water, sanitation and hygiene support.',
                2 => number_format( $figures['food'] ) . ' food beneficiaries reached through emergency food relief and nutrition support.',
            ];

            $current = [];
            foreach ( $pdo->query( 'SELECT sdg_goal_id, contribution_text FROM sdg_contributions' )->fetchAll( PDO::FETCH_ASSOC ) as $row ) {
                $current[ (int) $row['sdg_goal_id'] ] = (string) $row['contribution_text'];
            }

            $updateSdg = $pdo->prepare( 'UPDATE sdg_contributions SET contribution_text = ? WHERE sdg_goal_id = ?' );
            $changed   = 0;

            foreach ( $sdgCopy as $goalId => $text ) {
                if ( ( $current[ $goalId ] ?? null ) === $text ) {
                    continue;
                }
                $updateSdg->execute( [ $text, $goalId ] );
                $changed++;
            }

            if ( $changed ) {
                $applied[] = 'SDG contribution copy';
            }
        } catch ( Throwable $error ) {
            // Non-fatal.
        }
    }

    // ------------------------------------------------ annual impact news ----
    if ( $tableExists( 'news_articles' ) ) {
        try {
            $article = $pdo->query( 'SELECT id, title, content FROM news_articles WHERE id = 8 LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
            if ( $article && strpos( (string) $article['title'], 'Beneficiaries Reached' ) !== false ) {
                $newTitle = 'WRC Releases 2025 Annual Impact Report: ' . $total . ' Beneficiaries Reached';
                $newBody  = '<p>WRC\'s annual impact report for 2025 confirms that <strong>' . $total . ' beneficiaries</strong> were reached through integrated development interventions across Pakistan.</p>'
                    . '<h2>Key Highlights</h2><ul>'
                    . '<li>' . $total . ' beneficiaries reached across all programme sectors</li>'
                    . '<li>Health programmes serving ' . number_format( $figures['health'] ) . ' beneficiaries</li>'
                    . '<li>Skills and livelihood initiatives reaching ' . number_format( $figures['skills'] ) . ' beneficiaries</li>'
                    . '<li>Food security assistance reaching ' . number_format( $figures['food'] ) . ' beneficiaries</li>'
                    . '<li>WASH projects reaching ' . number_format( $figures['wash'] ) . ' beneficiaries</li>'
                    . '</ul><p>The report demonstrates WRC\'s commitment to transparency and accountability, with detailed financial breakdowns and programme outcome measurements. Independent evaluations confirm the effectiveness of our integrated approach to community development.</p>';

                if ( $newTitle !== (string) $article['title'] || $newBody !== (string) $article['content'] ) {
                    $pdo->prepare( 'UPDATE news_articles SET title = ?, content = ? WHERE id = ?' )
                        ->execute( [ $newTitle, $newBody, $article['id'] ] );
                    $applied[] = 'annual impact article';
                }
            }
        } catch ( Throwable $error ) {
            // Non-fatal.
        }
    }

    return $applied;
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
 * Split a stored image path from its optional focus point.
 *
 * The dashboard can save "path#x-y" so a wide or tall photo keeps its subject
 * in frame. The fragment is not part of the filename: it must never reach the
 * filesystem check or the image request, or the browser asks for a file that
 * cannot exist and the photo silently falls back to a placeholder.
 *
 * @return array{path:string,x:int,y:int}
 */
function splitImageFocus( string $path ): array {
    $path = trim( $path );

    if ( $path !== '' && preg_match( '/^(.*)#(\d{1,3})-(\d{1,3})$/', $path, $m ) && $m[ 1 ] !== '' ) {
        return [
            'path' => $m[ 1 ],
            'x'    => max( 0, min( 100, (int) $m[ 2 ] ) ),
            'y'    => max( 0, min( 100, (int) $m[ 3 ] ) ),
        ];
    }

    return [ 'path' => $path, 'x' => 50, 'y' => 50 ];
}

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

    // The dashboard stores an optional focus point as "path#x-y". That suffix
    // is metadata, not part of the filename, so it must not be checked on disk.
    $relative = ltrim( str_replace( '\\', '/', splitImageFocus( $path )[ 'path' ] ), '/' );

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

    // Drop the dashboard's focus-point suffix before building the URL. It is
    // not part of the file and would otherwise 404 for every focused image.
    $path = splitImageFocus( $path )[ 'path' ];

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

/**
 * Builds an international tel: value from a display number.
 *
 * "+92 (091) 9222780" becomes "+92919222780": separators are dropped and the
 * domestic trunk 0 after the country code is removed so the link dials from
 * any country.
 */
function phoneHref( ?string $phone ): string {
    $digits = preg_replace( '/\D+/', '', (string) $phone );
    if ( $digits === '' ) {
        return '';
    }
    if ( strpos( $digits, '0092' ) === 0 ) {
        $digits = substr( $digits, 2 );
    } elseif ( strpos( $digits, '920' ) === 0 ) {
        $digits = '92' . substr( $digits, 3 );
    } elseif ( $digits[ 0 ] === '0' ) {
        $digits = '92' . substr( $digits, 1 );
    }
    return '+' . $digits;
}

function isPost(): bool {
    return $_SERVER[ 'REQUEST_METHOD' ] === 'POST';
}

/**
 * Returns the posted value exactly as the author typed it.
 *
 * This deliberately does NOT run sanitize()/htmlspecialchars() on the way in.
 * Escaping belongs at the point of output, and every template that renders
 * stored content already escapes via sanitize() or htmlspecialchars(). Doing
 * it here as well double-encoded on every save: a plain apostrophe was stored
 * as "&#039;", and with repeated edits "&" compounded into "&amp;", then
 * "&amp;amp;", and further - which is what left visitors reading the literal
 * text "&amp;" in place of an ampersand. trim() is all that is wanted here.
 *
 * @see sanitize() - the matching escape, applied when rendering.
 */
function getPost( string $key, string $default = '' ): string {
    return isset( $_POST[ $key ] ) ? trim( (string) $_POST[ $key ] ) : $default;
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
