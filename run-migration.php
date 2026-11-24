<?php
/**
 * WRC - Browser-based Migration Runner
 * Access via: https://yourdomain.com/run-migration.php
 * DELETE THIS FILE AFTER USE!
 */
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WRC Migration Runner</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f8fafc; }
        .card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        h1 { color: #1e293b; }
        .btn { display: inline-block; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-size: 14px; transition: all 0.2s; }
        .btn-primary { background: #ca0000; color: white; }
        .btn-primary:hover { background: #a30000; }
        .btn-success { background: #16a34a; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .output { background: #0f172a; color: #22c55e; padding: 16px; border-radius: 8px; font-family: monospace; font-size: 13px; line-height: 1.6; white-space: pre-wrap; max-height: 400px; overflow: auto; }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .step { margin: 8px 0; }
        .step::before { content: "▶ "; color: #ca0000; }
        .step.done::before { content: "✓ "; color: #16a34a; }
        .step.error::before { content: "✗ "; color: #ef4444; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 WRC Migration Runner</h1>
        <p class="warning"><strong>⚠️ Security:</strong> Delete this file immediately after running migrations!</p>
    </div>

    <div class="card">
        <h3>Available Migrations</h3>
        <div class="step" id="step1">Our Work Dashboard Tables</div>
        <div class="step" id="step2">Our Work Dashboard Seed Data</div>
        <div class="step" id="step3">Verify Tables</div>

        <div style="margin-top: 20px;">
            <button class="btn btn-primary" onclick="runAll()" id="runBtn">Run All Migrations</button>
            <button class="btn btn-danger" onclick="deleteThisFile()" style="margin-left: 10px;" id="delBtn">Delete This File</button>
        </div>
    </div>

    <div class="card" id="outputCard" style="display:none;">
        <h3>Output</h3>
        <div class="output" id="output"></div>
    </div>

    <script>
        const output = document.getElementById('output');
        const outputCard = document.getElementById('outputCard');
        const runBtn = document.getElementById('runBtn');
        const steps = {1: 'step1', 2: 'step2', 3: 'step3'};

        function log(msg, type = 'info') {
            const prefix = type === 'success' ? '✅ ' : type === 'error' ? '❌ ' : type === 'warn' ? '⚠️ ' : 'ℹ️ ';
            output.textContent += prefix + msg + '\n';
            output.scrollTop = output.scrollHeight;
        }

        function markStep(num, status) {
            const el = document.getElementById(steps[num]);
            el.className = 'step ' + (status === 'done' ? 'done' : 'error');
        }

        async function runAll() {
            runBtn.disabled = true;
            runBtn.textContent = 'Running...';
            outputCard.style.display = 'block';
            output.textContent = '';

            try {
                // Step 1: Migration
                log('Running migration_our_work_dashboard.sql...');
                markStep(1, 'running');
                const r1 = await fetch('run-migration.php?action=migrate');
                const t1 = await r1.text();
                log(t1);
                if (t1.includes('ERROR')) { markStep(1, 'error'); throw new Error('Migration failed'); }
                markStep(1, 'done');

                // Step 2: Seed
                log('Running seed_our_work_dashboard.php...');
                markStep(2, 'running');
                const r2 = await fetch('run-migration.php?action=seed');
                const t2 = await r2.text();
                log(t2);
                if (t2.includes('ERROR') || t2.includes('failed')) { markStep(2, 'error'); throw new Error('Seed failed'); }
                markStep(2, 'done');

                // Step 3: Verify
                log('Verifying tables...');
                markStep(3, 'running');
                const r3 = await fetch('run-migration.php?action=verify');
                const t3 = await r3.text();
                log(t3);
                markStep(3, 'done');

                log('\n🎉 All done! Now go to Admin → Pages → Our Work → "Impact Dashboard"', 'success');

            } catch (e) {
                log('\n❌ Error: ' + e.message, 'error');
            }
            runBtn.disabled = false;
            runBtn.textContent = 'Run All Migrations';
        }

        async function deleteThisFile() {
            if (!confirm('Delete this migration runner file? (Recommended for security)')) return;
            const r = await fetch('run-migration.php?action=delete');
            const t = await r.text();
            log(t, 'success');
            document.getElementById('delBtn').style.display = 'none';
        }
    </script>
</body>
</html>

<?php
// Handle AJAX actions
if (isset($_GET['action'])) {
    header('Content-Type: text/plain; charset=utf-8');

    try {
        if ($_GET['action'] === 'migrate') {
            $sqlFile = __DIR__ . '/database/migration_our_work_dashboard.sql';
            if (!file_exists($sqlFile)) throw new Exception("SQL file not found");
            $sql = file_get_contents($sqlFile);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec($sql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            echo "SUCCESS: Migration completed!\n";

        } elseif ($_GET['action'] === 'seed') {
            $seedFile = __DIR__ . '/database/seed_our_work_dashboard.php';
            if (!file_exists($seedFile)) throw new Exception("Seed file not found");
            include $seedFile;
            echo "SUCCESS: Seed completed!\n";

        } elseif ($_GET['action'] === 'verify') {
            $tables = $pdo->query('SHOW TABLES LIKE "our_work_dashboard%"')->fetchAll(PDO::FETCH_COLUMN);
            echo "Tables found:\n";
            foreach ($tables as $t) echo "  ✓ $t\n";
            if (empty($tables)) echo "  (none)\n";

        } elseif ($_GET['action'] === 'delete') {
            $file = __FILE__;
            if (unlink($file)) {
                echo "SUCCESS: Migration runner deleted!";
            } else {
                echo "ERROR: Could not delete file (permission issue). Delete manually.";
            }
            exit;
        }
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    exit;
}
?>