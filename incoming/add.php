<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

if ($currentUser['role'] === 'viewer') {
    http_response_code(403);
    die('تاسو د ثبت صلاحیت نلرئ.');
}

$activePage = 'incoming';
$pageTitle  = 'نوی وارده ثبت - ' . APP_NAME;
$errors = [];
$f = [
    'serial_no' => '', 'incoming_date' => '', 'letter_date' => '', 'incoming_no' => '', 'dossier_no' => '',
    'sent_from' => '', 'origin' => '', 'subject' => '', 'doc_count' => '',
    'pages_no' => '', 'action_no' => '', 'remarks' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    foreach ($f as $key => $default) { $f[$key] = trim($_POST[$key] ?? ''); }

    if ($f['serial_no'] === '') {
        $errors[] = 'د مسلسل نمبر ډکول لازمي دي.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO incoming_letters
             (serial_no, incoming_date, letter_date, incoming_no, dossier_no, sent_from, origin, subject,
              doc_count, pages_no, action_no, remarks, created_by)
             VALUES
             (:serial_no, :incoming_date, :letter_date, :incoming_no, :dossier_no, :sent_from, :origin, :subject,
              :doc_count, :pages_no, :action_no, :remarks, :created_by)'
        );
        $stmt->execute([
            ...$f,
            'doc_count' => $f['doc_count'] !== '' ? (int)$f['doc_count'] : null,
            'created_by' => $currentUser['id'],
        ]);
        flash_set('success', 'د وارده مکتوب په بریالیتوب سره ثبت شو.');
        redirect('list.php');
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><h1>نوی وارده مکتوب ثبت کول</h1></div>

<?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" class="card">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div><label>مسلسل او مشترک نمبر *</label><input type="text" name="serial_no" value="<?= e($f['serial_no']) ?>" required></div>
        <div><label>نیټه (د ثبت)</label><input type="text" name="incoming_date" value="<?= e($f['incoming_date']) ?>" placeholder="1445/1/1"></div>
        <div><label>نیټه (د مکتوب)</label><input type="text" name="letter_date" value="<?= e($f['letter_date']) ?>" placeholder="1445/1/1"></div>
        <div><label>د وارده مکتوب نمبر</label><input type="text" name="incoming_no" value="<?= e($f['incoming_no']) ?>"></div>
        <div><label>مرسله الیه (لیږونکی)</label><input type="text" name="sent_from" value="<?= e($f['sent_from']) ?>"></div>
        <div><label>مبداء</label><input type="text" name="origin" value="<?= e($f['origin']) ?>"></div>
        <div><label>عدد</label><input type="number" name="doc_count" value="<?= e($f['doc_count']) ?>"></div>
        <div><label>د اوراقو نمبر</label><input type="text" name="pages_no" value="<?= e($f['pages_no']) ?>"></div>
        <div><label>د اقدام او مراجعت نمبر</label><input type="text" name="action_no" value="<?= e($f['action_no']) ?>"></div>
        <div><label>دوسیه نمبر</label><input type="text" name="dossier_no" value="<?= e($f['dossier_no']) ?>"></div>
    </div>

    <label>د مطلب خلاصه (موضوع)</label>
    <textarea name="subject"><?= e($f['subject']) ?></textarea>

    <label>ملاحظات</label>
    <textarea name="remarks"><?= e($f['remarks']) ?></textarea>

    <div style="margin-top:22px; display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit">ثبت کول</button>
        <a href="list.php" class="btn btn-secondary">لغوه کول</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
