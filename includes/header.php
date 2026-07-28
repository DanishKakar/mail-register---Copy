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
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
</head>
<body>
<header class="topbar">
    <div class="topbar-brand">📖 <?= e(APP_NAME) ?></div>

    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>

    <div class="topbar-menu" id="topbarMenu">
        <nav class="topbar-nav">
            <a href="<?= BASE_URL ?>index.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">کورپاڼه</a>
            <a href="<?= BASE_URL ?>outgoing/list.php" class="<?= $activePage === 'outgoing' ? 'active' : '' ?>">صادره</a>
            <a href="<?= BASE_URL ?>incoming/list.php" class="<?= $activePage === 'incoming' ? 'active' : '' ?>">وارده</a>
            <a href="<?= BASE_URL ?>receipts/list.php" class="<?= $activePage === 'receipts' ? 'active' : '' ?>"> رسیدات</a>
            <a href="<?= BASE_URL ?>departments/list.php" class="<?= $activePage === 'departments' ? 'active' : '' ?>">ادارات</a>
            <?php if ($currentUser['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>users.php" class="<?= $activePage === 'users' ? 'active' : '' ?>">کاروونکي</a>
            <?php endif; ?>

            <a href="../backup.php" class="backup-btn" title="بیک اپ اخیستل">
                <i class="fa-solid fa-cloud-arrow-down"></i>
            </a>
        </nav>
        <div class="topbar-user">
            <span><?= e($currentUser['full_name']) ?> (<?= e($currentUser['role']) ?>)</span>
            <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline btn-sm">وتل</a>
        </div>
    </div>
</header>

<script>
(function () {
    const toggle = document.getElementById('menuToggle');
    const menu = document.getElementById('topbarMenu');

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = menu.classList.toggle('open');
        toggle.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen);
    });

    menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            menu.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });

    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            menu.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>

<main class="page-content">
<?php
$success = flash_get('success');
$error   = flash_get('error');
?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>