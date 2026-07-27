<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';


if ($currentUser['role'] !== 'admin') {
    http_response_code(403);
    die('تاسو د Backup اجازه نلرئ.');
}


// Use shared DB connection from config
$pdo = null;
try {
    $pdo = db();
} catch (Exception $e) {
    http_response_code(500);
    error_log('Backup: could not obtain DB connection: ' . $e->getMessage());
    die('د ډیټابیس سره وصلیدل ناکام شول. مهرباني وکړئ وروسته بیا هڅه وکړئ.');
}

$db = $pdo->query('SELECT DATABASE()')->fetchColumn() ?: DB_NAME;
$filename = $db . '_backup_' . date('Y-m-d_H-i-s') . '.sql';

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');


echo "-- Database Backup\n";
echo "-- Created: ".date('Y-m-d H:i:s')."\n\n";


$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Ensure `users` and `departments` are exported first to satisfy FK order
$preferred = ['users', 'departments'];
$ordered = [];
foreach ($preferred as $p) {
    $idx = array_search($p, $tables, true);
    if ($idx !== false) {
        $ordered[] = $tables[$idx];
        unset($tables[$idx]);
    }
}
// Append remaining tables preserving original order
$tables = array_merge($ordered, array_values($tables));


foreach ($tables as $table) {


    echo "DROP TABLE IF EXISTS `$table`;\n\n";


    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
    echo ($create[1] ?? '') . ";\n\n";


    $rows = $pdo->query(
        "SELECT * FROM `$table`"
    );


    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {


        $values = array_map(function($value) use ($pdo) {

            if ($value === null) {
                return "NULL";
            }

            return $pdo->quote($value);

        }, array_values($row));


        echo "INSERT INTO `$table` VALUES ("
            .implode(',', $values)
            .");\n";

    }


    echo "\n\n";

}


exit;
