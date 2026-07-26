<?php
/** @var array $currentUser */
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="ps" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">


</head>


<body>
<header class="topbar">
    <div class="topbar-brand">📖 <?= e(APP_NAME) ?></div>
    <nav class="topbar-nav">
        <a href="<?= BASE_URL ?>index.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">کورپاڼه</a>
        <a href="<?= BASE_URL ?>outgoing/list.php" class="<?= $activePage === 'outgoing' ? 'active' : '' ?>">صادره مکتوبونه</a>
        <a href="<?= BASE_URL ?>incoming/list.php" class="<?= $activePage === 'incoming' ? 'active' : '' ?>">وارده مکتوبونه</a>
        <a href="<?= BASE_URL ?>departments/list.php" class="<?= $activePage === 'departments' ? 'active' : '' ?>">ادارات</a>
        <?php if ($currentUser['role'] === 'admin'): ?>
            <a href="<?= BASE_URL ?>users.php" class="<?= $activePage === 'users' ? 'active' : '' ?>">کاروونکي</a>
        <?php endif; ?>
        <a href="../backup.php" title="Database Backup">
            <i class="fa-solid fa-database" style="font-size:20px;"></i>
        </a>

    </nav>
    <div class="topbar-user">
        <span><?= e($currentUser['full_name']) ?> (<?= e($currentUser['role']) ?>)</span>
        <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline btn-sm">وتل</a>
    </div>
</header>
<main class="page-content">
<?php
$success = flash_get('success');
$error   = flash_get('error');
?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
