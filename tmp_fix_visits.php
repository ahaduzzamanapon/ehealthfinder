<?php
// Add unique index on (ip, visited_date) to page_visits
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=ehealthfinder;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->exec("ALTER TABLE `page_visits` ADD UNIQUE KEY `page_visits_ip_date_unique` (`ip`, `visited_date`)");
    echo "Unique index added on (ip, visited_date)\n";
} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . "\n";
}
