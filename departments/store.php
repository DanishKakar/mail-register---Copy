<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

// CSRF Check
if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    die('Invalid CSRF Token');
}



$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');


// Validation
if ($name === '') {

    $_SESSION['error'] = 'د ریاست / آمریت نوم ضروري دی';

    $_SESSION['old'] = [
        'name' => $name,
        'description' => $description
    ];

    header("Location: add.php");
    exit;
}


// Insert
$stmt = db()->prepare("
    INSERT INTO departments
    (
        name,
        description
    )
    VALUES
    (
        :name,
        :description
    )
");


$stmt->execute([
    ':name' => $name,
    ':description' => $description
]);


// Success message
$_SESSION['success'] = 'ریاست / آمریت په بریالیتوب سره ثبت شو';


header("Location: list.php");
exit;
