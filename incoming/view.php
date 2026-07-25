<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT i.*, u.full_name AS created_by_name FROM incoming_letters i LEFT JOIN users u ON u.id = i.created_by WHERE i.id = :id');
$stmt->execute(['id' => $id]);
$r = $stmt->fetch();
if (!$r) { flash_set('error', 'ریکارډ ونه موندل شو.'); redirect('list.php'); }

$activePage = 'incoming';
$pageTitle  = 'د وارده کتنه - ' . APP_NAME;
require __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <h1>د وارده مکتوب کتنه (#<?= $id ?>)</h1>
    <div class="row-actions">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-secondary">سمول</a>
        <a href="list.php" class="btn btn-secondary">بیرته لیست ته</a>
    </div>
</div>

<div class="card">
    <div class="form-grid">
        <div><label>مسلسل او مشترک لمبر</label><div><?= e($r['serial_no']) ?></div></div>
        <div><label>نیټه (د ثبت)</label><div><?= e($r['incoming_date']) ?></div></div>
        <div><label>نیټه (د مکتوب)</label><div><?= e($r['letter_date']) ?></div></div>
        <div><label>د وارده مکتوب لمبر</label><div><?= e($r['incoming_no']) ?></div></div>
        <div><label>مرسله الیه</label><div><?= e($r['sent_from']) ?></div></div>
        <div><label>مبداء</label><div><?= e($r['origin']) ?></div></div>
        <div><label>عدد</label><div><?= e((string)$r['doc_count']) ?></div></div>
        <div><label>د اوراقو لمبر</label><div><?= e($r['pages_no']) ?></div></div>
        <div><label>د اقدام او مراجعت لمبر</label><div><?= e($r['action_no']) ?></div></div>
        <div><label>دوسیه لمبر</label><div><?= e($r['dossier_no']) ?></div></div>
    </div>

    <label>د مطلب خلاصه (موضوع)</label>
    <div><?= nl2br(e($r['subject'])) ?></div>

    <label>ملاحظات</label>
    <div><?= nl2br(e($r['remarks'])) ?></div>

    <p class="text-muted" style="margin-top:20px;">
        ثبت شوی لخوا: <?= e($r['created_by_name'] ?? '—') ?> | نیټه: <?= e($r['created_at']) ?>
        <?php if ($r['updated_at'] !== $r['created_at']): ?> | وروستی سمون: <?= e($r['updated_at']) ?><?php endif; ?>
    </p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
