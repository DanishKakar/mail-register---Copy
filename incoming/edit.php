<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

if ($currentUser['role'] === 'viewer') {
    http_response_code(403);
    die('تاسو د سمون صلاحیت نلرئ.');
}

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM incoming_letters WHERE id = :id');
$stmt->execute(['id' => $id]);
$record = $stmt->fetch();
if (!$record) { flash_set('error', 'ریکارډ ونه موندل شو.'); redirect('list.php'); }

$activePage = 'incoming';
$pageTitle  = 'د وارده سمون - ' . APP_NAME;
$errors = [];
$fields = ['serial_no','incoming_date','letter_date','incoming_no', 'dossier_no', 'sent_from','origin','subject','doc_count','pages_no','action_no','remarks'];
$f = $record;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    foreach ($fields as $key) { $f[$key] = trim($_POST[$key] ?? ''); }

    if ($f['serial_no'] === '') {
        $errors[] = 'د مسلسل نمبر ډکول لازمي دي.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'UPDATE incoming_letters SET
             serial_no = :serial_no, incoming_date = :incoming_date, letter_date = :letter_date,
             incoming_no = :incoming_no, dossier_no = :dossier_no, sent_from = :sent_from, origin = :origin, subject = :subject,
             doc_count = :doc_count, pages_no = :pages_no, action_no = :action_no, remarks = :remarks
             WHERE id = :id'
        );
        $stmt->execute([
            'serial_no' => $f['serial_no'], 'incoming_date' => $f['incoming_date'], 'letter_date' => $f['letter_date'],
            'incoming_no' => $f['incoming_no'], 'dossier_no' => $f['dossier_no'], 'sent_from' => $f['sent_from'], 'origin' => $f['origin'],
            'subject' => $f['subject'], 'doc_count' => $f['doc_count'] !== '' ? (int)$f['doc_count'] : null,
            'pages_no' => $f['pages_no'], 'action_no' => $f['action_no'], 'remarks' => $f['remarks'],
            'id' => $id,
        ]);
        flash_set('success', 'بدلونونه خوندي شول.');
        redirect('view.php?id=' . $id);
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><h1>د وارده مکتوب سمون (#<?= $id ?>)</h1></div>

<?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" class="card">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div><label>مسلسل او مشترک نمبر *</label><input type="text" name="serial_no" value="<?= e($f['serial_no']) ?>" required></div>
        <div><label>نیټه (د ثبت)</label><input type="text" name="incoming_date" value="<?= e($f['incoming_date']) ?>"></div>
        <div><label>نیټه (د مکتوب)</label><input type="text" name="letter_date" value="<?= e($f['letter_date']) ?>"></div>
        <div><label>د وارده مکتوب نمبر</label><input type="text" name="incoming_no" value="<?= e($f['incoming_no']) ?>"></div>
        <div><label>مرسله الیه (لیږونکی)</label><input type="text" name="sent_from" value="<?= e($f['sent_from']) ?>"></div>
        <div><label>مبداء</label><input type="text" name="origin" value="<?= e($f['origin']) ?>"></div>
        <div><label>عدد</label><input type="number" name="doc_count" value="<?= e((string)$f['doc_count']) ?>"></div>
        <div><label>د اوراقو نمبر</label><input type="text" name="pages_no" value="<?= e($f['pages_no']) ?>"></div>
        <div><label>د اقدام او مراجعت نمبر</label><input type="text" name="action_no" value="<?= e($f['action_no']) ?>"></div>
        <div><label>دوسیه نمبر</label><input type="text" name="dossier_no" value="<?= e($f['dossier_no']) ?>"></div>
    </div>

    <label>د مطلب خلاصه (موضوع)</label>
    <textarea name="subject"><?= e($f['subject']) ?></textarea>

    <label>ملاحظات</label>
    <textarea name="remarks"><?= e($f['remarks']) ?></textarea>

    <div style="margin-top:22px; display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit">بدلونونه خوندي کول</button>
        <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">لغوه کول</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
