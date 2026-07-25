<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT o.*, u.full_name AS created_by_name FROM outgoing_letters o LEFT JOIN users u ON u.id = o.created_by WHERE o.id = :id');
$stmt->execute(['id' => $id]);
$r = $stmt->fetch();
if (!$r) { flash_set('error', 'ریکارډ ونه موندل شو.'); redirect('list.php'); }

$activePage = 'outgoing';
$pageTitle  = 'د صادره کتنه - ' . APP_NAME;
require __DIR__ . '/../includes/header.php';

function yn(int $v): string { return $v ? '<span class="badge badge-yes">هو</span>' : '<span class="badge badge-no">نه</span>'; }
?>
<div class="page-header">
    <h1>د صادره مکتوب کتنه (#<?= $id ?>)</h1>
    <div class="row-actions">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-secondary">سمول</a>
        <a href="list.php" class="btn btn-secondary">بیرته لیست ته</a>
    </div>
</div>

<div class="card">
    <div class="form-grid">
        <div><label>مسلسل او مشترک نمبر</label><div><?= e($r['serial_no']) ?></div></div>
        <div><label>دوسیه نمبر</label><div><?= e($r['dossier_no']) ?: '—' ?></div></div>
        <div><label>رسیداتو نمبر</label><div><?= e($r['receipts_no']) ?: '—' ?></div></div>
        <div><label>نیټه (د صدور)</label><div><?= e($r['issue_date']) ?></div></div>
        <div><label>نیټه (د مکتوب)</label><div><?= e($r['letter_date']) ?></div></div>
        <div><label>مرسل الیه</label><div><?= e($r['sent_to']) ?></div></div>
        <div><label>مرجع</label><div><?= e($r['reference_no']) ?></div></div>
    </div>

    <label>د مطلب خلاصه (موضوع)</label>
    <div><?= nl2br(e($r['subject'])) ?></div>

    <fieldset>
        <legend>د اوراقو د ضبط شعبه</legend>
        امضاء: <?= yn($r['records_signature']) ?> &nbsp;
        ضمیمه: <?= yn($r['records_attachment']) ?>
        <?php if ($r['records_attachment'] && $r['records_attachment_count'] !== null): ?>
            (<?= (int)$r['records_attachment_count'] ?> پاڼې)
        <?php endif; ?>
        &nbsp; اصل: <?= yn($r['records_original']) ?>
    </fieldset>

    <fieldset>
        <legend>د اجرائیه ادارو شعبه</legend>
        امضاء: <?= yn($r['exec_signature']) ?> &nbsp;
        ضمیمه: <?= yn($r['exec_attachment']) ?>
        <?php if ($r['exec_attachment'] && $r['exec_attachment_count'] !== null): ?>
            (<?= (int)$r['exec_attachment_count'] ?> پاڼې)
        <?php endif; ?>
        &nbsp; اصل: <?= yn($r['exec_original']) ?>
    </fieldset>

    <label>د توزیع او تسلیم یادداشتونه</label>
    <div><?= nl2br(e($r['distribution_notes'])) ?></div>

    <label>ملاحظات</label>
    <div><?= nl2br(e($r['remarks'])) ?></div>

    <p class="text-muted" style="margin-top:20px;">
        ثبت شوی لخوا: <?= e($r['created_by_name'] ?? '—') ?> | نیټه: <?= e($r['created_at']) ?>
        <?php if ($r['updated_at'] !== $r['created_at']): ?> | وروستی سمون: <?= e($r['updated_at']) ?><?php endif; ?>
    </p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>