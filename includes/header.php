<header class="topbar">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
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
            <a href="../backup.php" title="Database Backup">
                <i class="fa-solid fa-database" style="font-size:20px;"></i>
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

    // Close when clicking a link inside the menu
    menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            menu.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });

    // Close when clicking outside the header
    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Close on Escape
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