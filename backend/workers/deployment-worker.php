<?php
// Prevent direct access via web browser if possible, or ensure it's safe
if (php_sapi_name() !== 'cli' && !isset($_GET['key'])) {
    // Optional: Add a secret key check for web-cron triggering
    die('Access denied');
}

// Manual requires for stability
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/DeploymentService.php';

// Lock file to prevent concurrent execution
$lockFile = sys_get_temp_dir() . '/deployment-worker.lock';
$fp = fopen($lockFile, 'w');

if (!flock($fp, LOCK_EX | LOCK_NB)) {
    echo "Worker is already running.\n";
    exit(0);
}

try {
    echo "Worker started at " . date('Y-m-d H:i:s') . "\n";

    $deploymentService = new \App\Services\DeploymentService();
    $deploymentService->processQueue();

    echo "Worker finished at " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "Worker Error: " . $e->getMessage() . "\n";
    error_log("Worker Error: " . $e->getMessage());
} finally {
    flock($fp, LOCK_UN);
    fclose($fp);
}
