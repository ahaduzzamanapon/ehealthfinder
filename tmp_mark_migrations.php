<?php
// Mark Laravel's built-in migrations as already run
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=ehealthfinder;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create migrations table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL
) ENGINE=InnoDB");

// Delete all existing migration records
$pdo->exec("TRUNCATE TABLE `migrations`");

// Mark all existing migrations as done in batch 1
$migrations = [
    '0001_01_01_000000_create_users_table',
    '0001_01_01_000001_create_cache_table',
    '0001_01_01_000002_create_jobs_table',
    '2026_03_30_054140_create_page_visits_table',
];

$stmt = $pdo->prepare("INSERT INTO `migrations` (`migration`, `batch`) VALUES (?, 1)");
foreach ($migrations as $m) {
    $stmt->execute([$m]);
    echo "Marked as run: $m\n";
}

echo "\nDone! Now you can run: php artisan migrate\n";
