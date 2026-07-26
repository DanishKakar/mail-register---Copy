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

if ($id <= 0) {

    $_SESSION['error'] = 'ناسم ID';

    header("Location: list.php");
    exit;

}

// Delete record

$stmt = db()->prepare("
    DELETE FROM departments
    WHERE id = :id
");

$stmt->execute([
    ':id' => $id
]);

$_SESSION['success'] = 'ریاست / آمریت په بریالیتوب سره حذف شو';

header("Location: list.php");

exit;
