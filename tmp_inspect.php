<?php
$db = new SQLite3('database.sqlite');
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
$names = [];
while ($row = $tables->fetchArray(SQLITE3_ASSOC)) {
    $names[] = $row['name'];
}

$output = "";
foreach ($names as $name) {
    $count = $db->querySingle("SELECT COUNT(*) FROM \"$name\"");
    $output .= "TABLE: $name ($count rows)\n";
    $info = $db->query("PRAGMA table_info(\"$name\")");
    while ($col = $info->fetchArray(SQLITE3_ASSOC)) {
        $pk = $col['pk'] ? " [PK]" : "";
        $nn = $col['notnull'] ? " NOT NULL" : "";
        $output .= "  {$col['name']} {$col['type']}{$pk}{$nn}\n";
    }
    $output .= "\n";
}
file_put_contents('tmp_schema.txt', $output);
echo "Done. Written to tmp_schema.txt\n";
