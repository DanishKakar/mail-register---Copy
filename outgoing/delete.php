<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

if ($currentUser['role'] === 'viewer') {
    http_response_code(403);
    die('تاسو د حذف صلاحیت نلرئ.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('list.php');
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
$stmt = db()->prepare('DELETE FROM outgoing_letters WHERE id = :id');
$stmt->execute(['id' => $id]);

flash_set('success', 'ریکارډ حذف شو.');
redirect('list.php');
