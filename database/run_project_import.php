<?php
/**
 * CLI-only, backed-up importer for the authoritative WRC Excel project list.
 * Run by the deployment SSH step using the live site's existing DB config.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/config.php';

global $pdo;

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
sort($expectedSlugs);

$projectBackup = 'projects_excel_backup_20261013';
$partnerBackup = 'project_donors_excel_backup_20261013';

$tableExists = static function (string $table) use ($pdo): bool {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
};

$portfolioMatches = static function () use ($pdo, $expectedSlugs): bool {
    $slugs = $pdo->query('SELECT slug FROM projects ORDER BY slug')->fetchAll(PDO::FETCH_COLUMN);
    $activeCount = (int) $pdo->query('SELECT COUNT(*) FROM projects WHERE is_active = 1')->fetchColumn();
    $pricedCount = (int) $pdo->query('SELECT COUNT(*) FROM projects WHERE budget_amount IS NOT NULL')->fetchColumn();
    $partnerCount = (int) $pdo->query('SELECT COUNT(*) FROM project_donors')->fetchColumn();
    sort($slugs);

    return $slugs === $expectedSlugs
        && $activeCount === 20
        && $pricedCount === 0
        && $partnerCount === 20;
};

try {
    $sqlFile = __DIR__ . '/replace_projects_from_wrc_excel.sql';
    if (!is_file($sqlFile)) {
        throw new RuntimeException('The project import SQL file is missing.');
    }

    if ($portfolioMatches()) {
        echo "Project portfolio already matches the 20 approved Excel entries; no changes made.\n";
        exit(0);
    }

    $hasProjectBackup = $tableExists($projectBackup);
    $hasPartnerBackup = $tableExists($partnerBackup);
    if ($hasProjectBackup !== $hasPartnerBackup) {
        throw new RuntimeException('Only one backup table exists; refusing to alter project data.');
    }
    if ($hasProjectBackup && $hasPartnerBackup) {
        throw new RuntimeException('Project backups already exist but the live portfolio differs; refusing to overwrite it. Review the live database backups first.');
    }

    $pdo->exec("CREATE TABLE `{$projectBackup}` LIKE projects");
    $pdo->exec("INSERT INTO `{$projectBackup}` SELECT * FROM projects");
    $pdo->exec("CREATE TABLE `{$partnerBackup}` LIKE project_donors");
    $pdo->exec("INSERT INTO `{$partnerBackup}` SELECT * FROM project_donors");

    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new RuntimeException('Unable to read the project import SQL file.');
    }
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $statements = preg_split('/;\s*(?=\R|$)/', $sql, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($statements)) {
        throw new RuntimeException('Unable to parse the project import SQL file.');
    }

    $pdo->beginTransaction();
    $committed = false;

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement === '' || preg_match('/^START\s+TRANSACTION$/i', $statement)) {
            continue;
        }

        if (preg_match('/^COMMIT$/i', $statement)) {
            if (!$portfolioMatches()) {
                throw new RuntimeException('Imported project data failed integrity checks.');
            }
            $imagePaths = $pdo->query('SELECT hero_image FROM projects')->fetchAll(PDO::FETCH_COLUMN);
            foreach ($imagePaths as $imagePath) {
                $imageFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim((string) $imagePath, '/\\');
                if ($imagePath === '' || !is_file($imageFile)) {
                    throw new RuntimeException('A project image file is missing: ' . (string) $imagePath);
                }
            }
            $pdo->commit();
            $committed = true;
            continue;
        }

        if (preg_match('/^(CREATE TABLE|INSERT INTO)\s+`?projects_excel_backup_20261013`?/i', $statement)
            || preg_match('/^(CREATE TABLE|INSERT INTO)\s+`?project_donors_excel_backup_20261013`?/i', $statement)) {
            continue;
        }

        $pdo->exec($statement);
    }

    if (!$committed) {
        throw new RuntimeException('The import SQL did not complete its transaction.');
    }

    echo "Imported and verified all 20 WRC Excel projects. Previous project records were backed up.\n";
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Project import failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
