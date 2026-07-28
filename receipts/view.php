<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT i.*, u.full_name AS created_by_name, sent_dep.name AS sent_to_department, origin_dep.name AS origin_department FROM receipts i LEFT JOIN users u ON u.id = i.created_by LEFT JOIN departments sent_dep ON sent_dep.id = i.sent_to_dep_id LEFT JOIN departments origin_dep ON origin_dep.id = i.origin_dep_id WHERE i.id = :id');
$stmt->execute(['id' => $id]);
$r = $stmt->fetch();
if (!$r) { flash_set('error', 'ریکارډ ونه موندل شو.'); redirect('list.php'); }

$activePage = 'receipts';
$pageTitle  = 'د رسید کتنه - ' . APP_NAME;
require __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <h1>د رسیـــد مکتوب کتنه (#<?= e($r['serial_no']) ?>)</h1>
    <div class="row-actions">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-secondary">سمول</a>
        <a href="list.php" class="btn btn-secondary">بیرته لیست ته</a>
    </div>
</div>

<div class="card">
    <div class="form-grid">
        <div><label>مسلسل نمبر</label><div class="card-item"><?= e($r['serial_no']) ?></div></div>
        <div><label>آرشیف</label><div class="card-item"><?= e($r['archive']) ?></div></div>
        <div><label>شعبه</label><div class="card-item"><?= e($r['office']) ?></div></div>
        <div><label>اسم | نوم</label><div class="card-item"><?= e($r['name']) ?></div></div>
        <div><label>نیټه (د ثبت)</label><div class="card-item"><?= e($r['incoming_date']) ?></div></div>
        <div><label>نیټه (د مکتوب)</label><div class="card-item"><?= e($r['letter_date']) ?></div></div>
        <div><label>مرسل الیه</label><div class="card-item"><?= e($r['sent_to_department'] ?? '—') ?></div></div>
        <div><label>مرسل (مبداء)</label><div class="card-item"><?= e($r['origin_department'] ?? '—') ?></div></div>
        <div><label>عدد</label><div class="card-item"><?= e((string)$r['doc_count']) ?></div></div>
        <div><label>نمره اجرایه ارشیف</label><div class="card-item"><?= e($r['action_no']) ?></div></div>
        <div><label>امضاء</label><div class="card-item"><?= $r['records_signature'] ? 'بلې' : 'نه' ?></div></div>
        <div><label>اصل</label><div class="card-item"><?= $r['records_original'] ? 'بلې' : 'نه' ?></div></div>
    </div>

    <label>ملاحظات</label>
    <div class="card-item"><?= nl2br(e($r['remarks'])) ?></div>

    <p class="text-muted" style="margin-top:20px;">
        ثبت شوی لخوا: <?= e($r['created_by_name'] ?? '—') ?> | نیټه: <?= e($r['created_at']) ?>
        <?php if ($r['updated_at'] !== $r['created_at']): ?> | وروستی سمون: <?= e($r['updated_at']) ?><?php endif; ?>
    </p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>