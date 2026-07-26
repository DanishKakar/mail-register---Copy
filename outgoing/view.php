<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT o.*, u.full_name AS created_by_name, sent_dep.name AS sent_to_department, ref_dep.name AS reference_department FROM outgoing_letters o LEFT JOIN users u ON u.id = o.created_by LEFT JOIN departments sent_dep ON sent_dep.id = o.sent_to_dep_id LEFT JOIN departments ref_dep ON ref_dep.id = o.reference_dep_id WHERE o.id = :id');
$stmt->execute(['id' => $id]);
$r = $stmt->fetch();
if (!$r) { flash_set('error', 'ریکارډ ونه موندل شو.'); redirect('list.php'); }

$activePage = 'outgoing';
$pageTitle  = 'د صادره کتنه - ' . APP_NAME;
require __DIR__ . '/../includes/header.php';

function yn(int $v): string { return $v ? '<span class="badge badge-yes">هو</span>' : '<span class="badge badge-no">نه</span>'; }
?>
<div class="page-header">
    <h1>د صادره مکتوب کتنه (#<?= e($r['serial_no']) ?>)</h1>
    <div class="row-actions">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-secondary">سمول</a>
        <a href="list.php" class="btn btn-secondary">بیرته لیست ته</a>
    </div>
</div>

<div class="card">
    <div class="form-grid">
        <div><label>مسلسل او مشترک نمبر</label><div class="card-item"><?= e($r['serial_no']) ?></div></div>
        <div><label>دوسیه نمبر</label><div class="card-item"><?= e($r['dossier_no']) ?: '—' ?></div></div>
        <div><label>رسیداتو نمبر</label><div class="card-item"><?= e($r['receipts_no']) ?: '—' ?></div></div>
        <div><label>نیټه (د صدور)</label><div class="card-item"><?= e($r['issue_date']) ?></div></div>
        <div><label>نیټه (د مکتوب)</label><div class="card-item"><?= e($r['letter_date']) ?></div></div>
        <div><label>مرسل الیه</label><div class="card-item"><?= e($r['sent_to_department'] ?? '—') ?></div></div>
        <div><label>مرجع</label><div class="card-item"><?= e($r['reference_department'] ?? '—') ?></div></div>
    </div>

    <label>د مطلب خلاصه (موضوع)</label>
    <div class="card-item"><?= nl2br(e($r['subject'])) ?></div>

    <fieldset class="card-item">
        <legend>د اوراقو د ضبط شعبه</legend>
        امضاء: <?= yn($r['records_signature']) ?> &nbsp;
        ضمیمه: <?= yn($r['records_attachment']) ?>
        <?php if ($r['records_attachment'] && $r['records_attachment_count'] !== null): ?>
            (<?= (int)$r['records_attachment_count'] ?> پاڼې)
        <?php endif; ?>
        &nbsp; اصل: <?= yn($r['records_original']) ?>
    </fieldset>

    <fieldset class="card-item">
        <legend>د اجرائیه ادارو شعبه</legend>
        امضاء: <?= yn($r['exec_signature']) ?> &nbsp;
        ضمیمه: <?= yn($r['exec_attachment']) ?>
        <?php if ($r['exec_attachment'] && $r['exec_attachment_count'] !== null): ?>
            (<?= (int)$r['exec_attachment_count'] ?> پاڼې)
        <?php endif; ?>
        &nbsp; اصل: <?= yn($r['exec_original']) ?>
    </fieldset>

    <label>د توزیع او تسلیم یادداشتونه</label>
    <div class="card-item"><?= nl2br(e($r['distribution_notes'])) ?></div>

    <label>ملاحظات</label>
    <div class="card-item"><?= nl2br(e($r['remarks'])) ?></div>

    <p class="text-muted" style="margin-top:20px;">
        ثبت شوی لخوا: <?= e($r['created_by_name'] ?? '—') ?> | نیټه: <?= e($r['created_at']) ?>
        <?php if ($r['updated_at'] !== $r['created_at']): ?> | وروستی سمون: <?= e($r['updated_at']) ?><?php endif; ?>
    </p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>