<?php
/**
 * WRC - Live Database Migration Runner
 * Execute this file from browser once, then delete it.
 */
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="background:#111;color:#0f0;padding:20px;font-family:monospace;font-size:15px;line-height:1.6;">';
echo "=== WRC MIGRATION: IMPORTING PROJECTS INTO LIVE DATABASE ===\n\n";

echo "Current Database: " . DB_NAME . "\n";

try {
    $sqlFile = __DIR__ . '/database/migrate_projects_to_live.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: " . $sqlFile);
    }

    $sql = file_get_contents($sqlFile);
    
    // Disable FK checks and run
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec($sql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "✅ SUCCESS: All project tables & records imported successfully!\n\n";

    // Verify
    $projectsCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $activeCount = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'active' AND is_active = 1")->fetchColumn();
    $completedCount = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'completed' AND is_active = 1")->fetchColumn();
    $donorsCount = $pdo->query("SELECT COUNT(*) FROM project_donors")->fetchColumn();

    echo "---------------- VERIFICATION ----------------\n";
    echo "Total Projects in DB:       " . $projectsCount . "\n";
    echo "Active Projects:            " . $activeCount . "\n";
    echo "Completed Projects:         " . $completedCount . "\n";
    echo "Project Donors Linked:      " . $donorsCount . "\n";
    echo "----------------------------------------------\n\n";
    echo "🎉 Live website projects page is now ACTIVE and CONNECTED!\n";
    echo "⚠️ Security Tip: Please delete this file and project-diagnostic.php after verifying.\n";

} catch (Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo '</pre>';
