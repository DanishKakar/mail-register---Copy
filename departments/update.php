<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: list.php");
    exit;

}


// CSRF Check
if (
    isset($_POST['csrf_token']) &&
    isset($_SESSION['csrf_token']) &&
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {

    die('Invalid CSRF Token');

}


$id = (int)($_POST['id'] ?? 0);

$name = trim($_POST['name'] ?? '');

$description = trim($_POST['description'] ?? '');



if ($id <= 0 || $name === '') {


    $_SESSION['error'] = 'معلومات ناسمې دي';


    header("Location: list.php");

    exit;

}




$stmt = db()->prepare("
    UPDATE departments
    SET
        name = :name,
        description = :description
    WHERE id = :id
");



$stmt->execute([

    ':name' => $name,

    ':description' => $description,

    ':id' => $id

]);



$_SESSION['success'] = 'ریاست / آمریت په بریالیتوب سره تازه شو';



header("Location: list.php");

exit;
