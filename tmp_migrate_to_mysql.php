<?php
/**
 * SQLite → MySQL Migration Script
 * Run with: php tmp_migrate_to_mysql.php
 *
 * Config করুন নিচের settings:
 */
$MYSQL_HOST   = '127.0.0.1';
$MYSQL_PORT   = '3306';
$MYSQL_DB     = 'ehealthfinder';   // ← আপনার MySQL database নাম
$MYSQL_USER   = 'root';
$MYSQL_PASS   = '';                // ← password নেই

$SQLITE_PATH  = __DIR__ . '/database.sqlite';

// ─────────────────────────────────────────────
echo "Connecting to SQLite...\n";
$sqlite = new PDO("sqlite:$SQLITE_PATH");
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Connecting to MySQL...\n";
try {
    $mysql = new PDO("mysql:host=$MYSQL_HOST;port=$MYSQL_PORT;charset=utf8mb4", $MYSQL_USER, $MYSQL_PASS);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("MySQL connection failed: " . $e->getMessage() . "\n");
}

// Create DB if not exists
$mysql->exec("CREATE DATABASE IF NOT EXISTS `$MYSQL_DB` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysql->exec("USE `$MYSQL_DB`");
echo "Database `$MYSQL_DB` ready.\n\n";
$mysql->exec("SET FOREIGN_KEY_CHECKS=0");

// ─────────────────────────────────────────────
// CREATE TABLES
// ─────────────────────────────────────────────

$ddl = [

'locations' => "CREATE TABLE IF NOT EXISTS `locations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'specialties' => "CREATE TABLE IF NOT EXISTS `specialties` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'generics' => "CREATE TABLE IF NOT EXISTS `generics` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'hospitals' => "CREATE TABLE IF NOT EXISTS `hospitals` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(500) NULL,
  `location_id` BIGINT UNSIGNED NULL,
  INDEX `hospitals_location_id_index` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'doctors' => "CREATE TABLE IF NOT EXISTS `doctors` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `url` TEXT NULL,
  `name` VARCHAR(500) NULL,
  `degrees` TEXT NULL,
  `experience` TEXT NULL,
  `designation` TEXT NULL,
  `workplace` TEXT NULL,
  `about_text` MEDIUMTEXT NULL,
  `image_path` TEXT NULL,
  `location_id` BIGINT UNSIGNED NULL,
  `specialty_id` BIGINT UNSIGNED NULL,
  INDEX `doctors_location_id_index` (`location_id`),
  INDEX `doctors_specialty_id_index` (`specialty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'chambers' => "CREATE TABLE IF NOT EXISTS `chambers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `doctor_id` BIGINT UNSIGNED NULL,
  `hospital_id` BIGINT UNSIGNED NULL,
  `address` TEXT NULL,
  `visiting_hour` TEXT NULL,
  `appointment_number` TEXT NULL,
  INDEX `chambers_doctor_id_index` (`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'brands' => "CREATE TABLE IF NOT EXISTS `brands` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `medex_id` INT NULL,
  `name` VARCHAR(500) NULL,
  `dosage_form` VARCHAR(255) NULL,
  `generic_id` BIGINT UNSIGNED NULL,
  `company` VARCHAR(500) NULL,
  `price` VARCHAR(100) NULL,
  `is_antibiotic` TINYINT(1) DEFAULT 0,
  `image_path` TEXT NULL,
  `indications_en` MEDIUMTEXT NULL,
  `indications_bn` MEDIUMTEXT NULL,
  `mode_of_action_en` MEDIUMTEXT NULL,
  `mode_of_action_bn` MEDIUMTEXT NULL,
  `dosage_en` MEDIUMTEXT NULL,
  `dosage_bn` MEDIUMTEXT NULL,
  `administration_en` MEDIUMTEXT NULL,
  `administration_bn` MEDIUMTEXT NULL,
  `interaction_en` MEDIUMTEXT NULL,
  `interaction_bn` MEDIUMTEXT NULL,
  `contraindications_en` MEDIUMTEXT NULL,
  `contraindications_bn` MEDIUMTEXT NULL,
  `side_effects_en` MEDIUMTEXT NULL,
  `side_effects_bn` MEDIUMTEXT NULL,
  `pregnancy_cat_en` TEXT NULL,
  `pregnancy_cat_bn` TEXT NULL,
  `precautions_en` MEDIUMTEXT NULL,
  `precautions_bn` MEDIUMTEXT NULL,
  `pediatric_uses_en` MEDIUMTEXT NULL,
  `pediatric_uses_bn` MEDIUMTEXT NULL,
  `storage_conditions_en` TEXT NULL,
  `storage_conditions_bn` TEXT NULL,
  `compound_summary_en` MEDIUMTEXT NULL,
  `compound_summary_bn` MEDIUMTEXT NULL,
  INDEX `brands_generic_id_index` (`generic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'users' => "CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'migrations' => "CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'page_visits' => "CREATE TABLE IF NOT EXISTS `page_visits` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip` VARCHAR(45) NULL,
  `url` VARCHAR(1000) NULL,
  `user_agent` TEXT NULL,
  `visited_date` DATE NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX `page_visits_date_index` (`visited_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'sessions' => "CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  INDEX `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'cache' => "CREATE TABLE IF NOT EXISTS `cache` (
  `key` VARCHAR(255) NOT NULL PRIMARY KEY,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

];

foreach ($ddl as $tbl => $sql) {
    $mysql->exec($sql);
    echo "  ✓ Table `$tbl` created\n";
}
echo "\n";

// ─────────────────────────────────────────────
// COPY DATA
// ─────────────────────────────────────────────

function copyTable(PDO $src, PDO $dst, string $table, int $chunkSize = 500): void
{
    $total = $src->query("SELECT COUNT(*) FROM \"$table\"")->fetchColumn();
    echo "Copying `$table` ($total rows)...";

    if ($total == 0) { echo " (empty, skipped)\n"; return; }

    // Get columns
    $cols = $src->query("PRAGMA table_info(\"$table\")")->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'name');
    $colList  = implode(',', array_map(fn($c) => "`$c`", $colNames));
    $placeholders = implode(',', array_fill(0, count($colNames), '?'));

    $dst->exec("TRUNCATE TABLE `$table`");
    $stmt = $dst->prepare("INSERT INTO `$table` ($colList) VALUES ($placeholders)");

    $offset = 0;
    $copied = 0;
    while ($offset < $total) {
        $rows = $src->query("SELECT * FROM \"$table\" LIMIT $chunkSize OFFSET $offset")->fetchAll(PDO::FETCH_NUM);
        $dst->beginTransaction();
        foreach ($rows as $row) {
            // Convert empty strings to null for nullable columns
            $row = array_map(fn($v) => ($v === '') ? null : $v, $row);
            $stmt->execute($row);
            $copied++;
        }
        $dst->commit();
        $offset += $chunkSize;
        echo ".";
    }
    echo " $copied rows ✓\n";
}

$tablesToCopy = ['locations','specialties','generics','hospitals','doctors','chambers','brands'];

foreach ($tablesToCopy as $tbl) {
    copyTable($sqlite, $mysql, $tbl);
}

$mysql->exec("SET FOREIGN_KEY_CHECKS=1");

echo "\n✅ Migration complete! All data is now in MySQL `$MYSQL_DB`.\n";
echo "\nNext: Update your .env to use MySQL:\n";
echo "  DB_CONNECTION=mysql\n";
echo "  DB_HOST=$MYSQL_HOST\n";
echo "  DB_PORT=$MYSQL_PORT\n";
echo "  DB_DATABASE=$MYSQL_DB\n";
echo "  DB_USERNAME=$MYSQL_USER\n";
echo "  DB_PASSWORD=\n";
