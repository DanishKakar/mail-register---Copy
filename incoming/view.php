<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT i.*, u.full_name AS created_by_name, sent_dep.name AS sent_to_department, origin_dep.name AS origin_department FROM incoming_letters i LEFT JOIN users u ON u.id = i.created_by LEFT JOIN departments sent_dep ON sent_dep.id = i.sent_to_dep_id LEFT JOIN departments origin_dep ON origin_dep.id = i.origin_dep_id WHERE i.id = :id');
$stmt->execute(['id' => $id]);
$r = $stmt->fetch();
if (!$r) { flash_set('error', 'ریکارډ ونه موندل شو.'); redirect('list.php'); }

$activePage = 'incoming';
$pageTitle  = 'د وارده کتنه - ' . APP_NAME;
require __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <h1>د وارده مکتوب کتنه (#<?= e($r['serial_no']) ?>)</h1>
    <div class="row-actions">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-secondary">سمول</a>
        <a href="list.php" class="btn btn-secondary">بیرته لیست ته</a>
    </div>
</div>

<div class="card">
    <div class="form-grid">
        <div><label>مسلسل او مشترک نمبر</label><div class="card-item"><?= e($r['serial_no']) ?></div></div>
        <div><label>نیټه (د ثبت)</label><div class="card-item"><?= e($r['incoming_date']) ?></div></div>
        <div><label>نیټه (د مکتوب)</label><div class="card-item"><?= e($r['letter_date']) ?></div></div>
        <div><label>د وارده مکتوب نمبر</label><div class="card-item"><?= e($r['incoming_no']) ?></div></div>
        <div><label>مرسله الیه</label><div class="card-item"><?= e($r['sent_to_department'] ?? '—') ?></div></div>
        <div><label>مبداء</label><div class="card-item"><?= e($r['origin_department'] ?? '—') ?></div></div>
        <div><label>عدد</label><div class="card-item"><?= e((string)$r['doc_count']) ?></div></div>
        <div><label>د اوراقو نمبر</label><div class="card-item"><?= e($r['pages_no']) ?></div></div>
        <div><label>د اقدام او مراجعت نمبر</label><div class="card-item"><?= e($r['action_no']) ?></div></div>
        <div><label>دوسیه نمبر</label><div class="card-item"><?= e($r['dossier_no']) ?></div></div>
    </div>

    <label>د مطلب خلاصه (موضوع)</label>
    <div class="card-item"><?= nl2br(e($r['subject'])) ?></div>

    <label>ملاحظات</label>
    <div class="card-item"><?= nl2br(e($r['remarks'])) ?></div>

    <p class="text-muted" style="margin-top:20px;">
        ثبت شوی لخوا: <?= e($r['created_by_name'] ?? '—') ?> | نیټه: <?= e($r['created_at']) ?>
        <?php if ($r['updated_at'] !== $r['created_at']): ?> | وروستی سمون: <?= e($r['updated_at']) ?><?php endif; ?>
    </p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
