<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ADMIN_PATH', dirname(__FILE__));

function isLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
}

function getAdminUser(): ?array {
    if (!isLoggedIn()) return null;
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $rows = $stmt->fetchAll();
    return !empty($rows) ? $rows[0] : null;
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function generateCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function uploadWidget(string $name, string $label, string $currentValue = '', string $type = 'image', ?string $id = null): void {
    $inputId = $id ?? $name;
    ?>
    <div class="form-group">
      <label class="block text-sm font-semibold text-slate-700 mb-1.5"><?= htmlspecialchars($label) ?></label>
      <input type="text" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($currentValue) ?>"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all upload-path-input"
        id="<?= $inputId ?>" placeholder="Path or upload a file">
      <div class="upload-widget mt-2" data-type="<?= $type ?>" data-target="<?= $inputId ?>">
        <div class="upload-zone">
          <input type="file" accept="<?= $type === 'image' ? 'image/*' : '.pdf,.zip,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv' ?>">
          <div class="upload-placeholder">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <p>Click or drag to upload <?= $type === 'image' ? 'image' : 'file' ?></p>
          </div>
        </div>
      </div>
    </div>
    <?php
}

function generateSlug(string $text): string {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function checkSlugUnique(string $table, string $slug, int $excludeId = 0): bool {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `slug` = ?";
    $params = [$slug];
    if ($excludeId > 0) {
        $sql .= " AND `id` != ?";
        $params[] = $excludeId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() === 0;
}
