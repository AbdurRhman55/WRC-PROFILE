<?php
/**
 * WRC Website - Helper Functions
 * Utility functions for data handling, sanitization, and rendering
 */

// =============================================
// SANITIZATION
// =============================================

function sanitize(?string $input): string {
    if ($input === null) return '';
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitizeArray(array $input): array {
    return array_map('sanitize', $input);
}

// =============================================
// DATABASE HELPERS
// =============================================

/**
 * Get a single setting value from site_settings
 */
function getSetting(string $key, string $default = ''): string {
    global $pdo;
    static $cache = [];

    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();

    $cache[$key] = $result ? $result['setting_value'] : $default;
    return $cache[$key];
}

/**
 * Get all settings for a specific group
 */
function getSettingsByGroup(string $group): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_group = ?");
    $stmt->execute([$group]);
    $results = $stmt->fetchAll();

    $settings = [];
    foreach ($results as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

/**
 * Fetch a single row by ID
 */
function getById(string $table, int $id): ?array {
    global $pdo;
    $allowed = [
        'programs', 'projects', 'news_articles', 'resources', 'team_members',
        'history_milestones', 'history_projects', 'partners', 'provinces', 'campaigns', 'contact_submissions',
        'newsletter_subscribers', 'donation_tiers', 'bank_accounts', 'job_listings',
        'sdg_goals', 'sdg_contributions', 'impact_stats', 'organizational_structure',
        'hero_sliders', 'homepage_sections', 'site_settings'
    ];

    if (!in_array($table, $allowed)) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Fetch multiple rows with optional conditions
 * $conditions can be:
 *   - associative array: ['is_active' => 1] → WHERE `is_active` = 1
 *   - raw string: 'is_active = 1 ORDER BY sort_order ASC LIMIT 3' → WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 3
 */
function getRows(string $table, $conditions = [], string $orderBy = 'sort_order ASC', int $limit = 0): array {
    global $pdo;
    $allowed = [
        'programs', 'projects', 'news_articles', 'resources', 'team_members',
        'history_milestones', 'history_projects', 'partners', 'provinces', 'campaigns', 'contact_submissions',
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
        'news_hero', 'resources_hero',
        'get_involved_hero', 'get_involved_cta',
        'contribute_hero', 'contribute_impact', 'contribute_impact_items',
        'contribute_transparency', 'contribute_transparency_items',
        'contribute_why', 'contribute_why_stats',
    ];

    if (!in_array($table, $allowed)) {
        return [];
    }

    $sql = "SELECT * FROM `{$table}`";
    $params = [];

    if (is_string($conditions) && !empty($conditions)) {
        // String conditions are used only by internal hardcoded calls (safe)
        $sql .= " WHERE {$conditions}";
    } elseif (is_array($conditions) && !empty($conditions)) {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "`{$key}` = ?";
            $params[] = $value;
        }
        $sql .= " WHERE " . implode(' AND ', $where);
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
    } else {
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Fetch a single row by conditions
 */
function getRow(string $table, array $conditions = []): ?array {
    $rows = getRows($table, $conditions, '', 1);
    return !empty($rows) ? $rows[0] : null;
}

/**
 * Fetch active programs
 */
function getActivePrograms(): array {
    return getRows('programs', ['is_active' => 1], 'sort_order ASC');
}

/**
 * Fetch a program by slug
 */
function getProgramBySlug(string $slug): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Fetch interventions for a program
 */
function getProgramInterventions(int $programId): array {
    return getRows('program_interventions', ['program_id' => $programId], 'sort_order ASC');
}

/**
 * Fetch focus areas for a program
 */
function getProgramFocusAreas(int $programId): array {
    return getRows('program_focus_areas', ['program_id' => $programId], 'sort_order ASC');
}

/**
 * Fetch active projects
 */
function getActiveProjects(): array {
    return getRows('projects', ['is_active' => 1, 'status' => 'active'], 'sort_order ASC');
}

/**
 * Fetch all active projects (active + completed)
 */
function getAllProjects(): array {
    return getRows('projects', ['is_active' => 1], 'sort_order ASC');
}

/**
 * Fetch a project by slug
 */
function getProjectBySlug(string $slug): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Fetch published news articles
 */
function getPublishedNews(int $limit = 0): array {
    return getRows('news_articles', ['is_published' => 1], 'published_at DESC', $limit);
}

/**
 * Fetch news by category
 */
function getNewsByCategory(string $category, int $limit = 0): array {
    return getRows('news_articles', ['is_published' => 1, 'category' => $category], 'published_at DESC', $limit);
}

/**
 * Fetch active campaigns
 */
function getActiveCampaigns(): array {
    return getRows('campaigns', ['is_active' => 1, 'status' => 'active'], 'sort_order ASC');
}

/**
 * Fetch team members by role type
 */
function getTeamByRole(string $roleType): array {
    return getRows('team_members', ['is_active' => 1, 'role_type' => $roleType], 'sort_order ASC');
}

/**
 * Fetch history milestones
 */
function getHistoryMilestones(): array {
    return getRows('history_milestones', [], 'sort_order ASC');
}

/**
 * Fetch active partners
 */
function getActivePartners(): array {
    return getRows('partners', ['is_active' => 1], 'sort_order ASC');
}

/**
 * Fetch impact stats by page
 */
function getImpactStatsByPage(string $page): array {
    return getRows('impact_stats', ['display_page' => $page], 'sort_order ASC');
}

/**
 * Fetch active resources by type
 */
function getResourcesByType(string $type): array {
    return getRows('resources', ['is_active' => 1, 'resource_type' => $type], 'sort_order ASC');
}

/**
 * Fetch provinces
 */
function getProvinces(): array {
    return getRows('provinces', ['is_active' => 1], 'sort_order ASC');
}

/**
 * Fetch donation tiers
 */
function getDonationTiers(): array {
    return getRows('donation_tiers', ['is_active' => 1], 'sort_order ASC');
}

/**
 * Fetch active job listings
 */
function getActiveJobs(): array {
    return getRows('job_listings', ['is_active' => 1], 'posted_at DESC');
}

// =============================================
// SLUG GENERATION
// =============================================

function createSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// =============================================
// FORMATTING
// =============================================

function formatNumber(int $number): string {
    if ($number >= 1000000) {
        return number_format($number / 1000000, 1) . 'M+';
    }
    if ($number >= 1000) {
        return number_format($number / 1000, 0) . 'K+';
    }
    return $number . '+';
}

function formatDate(string $date, string $format = 'M d, Y'): string {
    return date($format, strtotime($date));
}

// =============================================
// IMAGE HELPERS
// =============================================

function getImageUrl(string $path): string {
    if (empty($path)) {
        return 'assets/images/hero.jpg';
    }
    // If path already starts with assets/ or http, return as-is
    if (strpos($path, 'assets/') === 0 || strpos($path, 'http') === 0) {
        return $path;
    }
    return 'assets/images/' . $path;
}

/**
 * Upload an image file
 */
function uploadImage(array $file, string $destination = 'uploads/'): ?string {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

    if (!in_array($file['type'], $allowed)) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $ext;
    $path = SITE_ROOT . '/' . $destination;

    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $path . $filename)) {
        return $destination . $filename;
    }

    return null;
}

function handleUpload(array $file, string $type = 'images'): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $maxSize = $type === 'images' ? 5 * 1024 * 1024 : 20 * 1024 * 1024;
    if ($file['size'] > $maxSize) return null;

    $allowedImages = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    $allowedFiles = ['application/pdf', 'application/zip', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain', 'text/csv'];

    if ($type === 'images' && !in_array($file['type'], $allowedImages)) return null;
    if ($type === 'files' && !in_array($file['type'], $allowedFiles)) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid($type === 'images' ? 'img_' : 'file_') . '.' . $ext;

    $dir = $type === 'images' ? 'assets/images/uploads/' : 'assets/files/uploads/';
    $fullPath = __DIR__ . '/../' . $dir;

    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $fullPath . $filename)) {
        return $dir . $filename;
    }

    return null;
}

// =============================================
// RESPONSE HELPERS
// =============================================

function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

function isPost(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function getPost(string $key, string $default = ''): string {
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

function getPostRaw(string $key, string $default = ''): string {
    return trim($_POST[$key] ?? $default);
}

function getPostInt(string $key, int $default = 0): int {
    return isset($_POST[$key]) ? (int) $_POST[$key] : $default;
}

function getPostFloat(string $key, float $default = 0.0): float {
    return isset($_POST[$key]) ? (float) $_POST[$key] : $default;
}

function getGet(string $key, string $default = ''): string {
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

function insertRow(string $table, array $data): int {
    global $pdo;
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $stmt = $pdo->prepare("INSERT INTO `$table` ($columns) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
    return (int) $pdo->lastInsertId();
}

function updateRow(string $table, array $data, array $conditions = []): bool {
    global $pdo;
    $set = [];
    $values = [];
    foreach ($data as $col => $val) {
        $set[] = "`$col` = ?";
        $values[] = $val;
    }
    $where = [];
    foreach ($conditions as $col => $val) {
        $where[] = "`$col` = ?";
        $values[] = $val;
    }
    $sql = "UPDATE `$table` SET " . implode(', ', $set);
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

function deleteRow(string $table, array $conditions = []): bool {
    global $pdo;
    $where = [];
    $values = [];
    foreach ($conditions as $col => $val) {
        $where[] = "`$col` = ?";
        $values[] = $val;
    }
    $sql = "DELETE FROM `$table`";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}
