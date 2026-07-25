<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';


if ($currentUser['role'] !== 'admin') {
    http_response_code(403);
    die('تاسو د Backup اجازه نلرئ.');
}


// Database settings
$host = DB_HOST;
$db   = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;


$filename = $db . '_backup_' . date('Y-m-d_H-i-s') . '.sql';


header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="'.$filename.'"');


$pdo = new PDO(
    "mysql:host=$host;dbname=$db;charset=utf8mb4",
    $user,
    $pass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);


echo "-- Database Backup\n";
echo "-- Created: ".date('Y-m-d H:i:s')."\n\n";


$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);


foreach ($tables as $table) {


    echo "DROP TABLE IF EXISTS `$table`;\n\n";


    $create = $pdo->query(
        "SHOW CREATE TABLE `$table`"
    )->fetch();


    echo $create['Create Table'].";\n\n";


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
