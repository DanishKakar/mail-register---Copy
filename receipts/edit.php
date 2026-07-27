<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

if ($currentUser['role'] === 'viewer') {
    http_response_code(403);
    die('تاسو د سمون صلاحیت نلرئ.');
}

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM receipts WHERE id = :id');
$stmt->execute(['id' => $id]);
$record = $stmt->fetch();
if (!$record) { flash_set('error', 'ریکارډ ونه موندل شو.'); redirect('list.php'); }

$activePage = 'incoming';
$pageTitle  = 'د وارده سمون - ' . APP_NAME;
$errors = [];
$departments = db()->query('SELECT id, name FROM departments ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$defaults = [
    'serial_no' => '', 'incoming_date' => '', 'letter_date' => '', 'archive' => '', 'office' => '',
    'name' => '', 'doc_count' => '', 'action_no' => '', 'records_signature' => 0, 'records_original' => 0, 'remarks' => '',
    'sent_to_dep_id' => null, 'origin_dep_id' => null,
];
$f = array_merge($defaults, $record);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $f['serial_no'] = trim($_POST['serial_no'] ?? '');
    $f['incoming_date'] = trim($_POST['incoming_date'] ?? '');
    $f['letter_date'] = trim($_POST['letter_date'] ?? '');
    $f['archive'] = trim($_POST['archive'] ?? '');
    $f['office'] = trim($_POST['office'] ?? '');
    $f['name'] = trim($_POST['name'] ?? '');
    $f['doc_count'] = trim($_POST['doc_count'] ?? '');
    $f['action_no'] = trim($_POST['action_no'] ?? '');
    $f['records_signature'] = !empty($_POST['records_signature']) ? 1 : 0;
    $f['records_original'] = !empty($_POST['records_original']) ? 1 : 0;
    $f['remarks'] = trim($_POST['remarks'] ?? '');
    $f['sent_to_dep_id'] = !empty($_POST['sent_to_dep_id']) ? (int)$_POST['sent_to_dep_id'] : null;
    $f['origin_dep_id'] = !empty($_POST['origin_dep_id']) ? (int)$_POST['origin_dep_id'] : null;

    if ($f['serial_no'] === '') {
        $errors[] = 'د مسلسل نمبر ډکول لازمي دي.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'UPDATE receipts SET
             serial_no = :serial_no, incoming_date = :incoming_date, letter_date = :letter_date,
             archive = :archive, office = :office, name = :name,
             sent_to_dep_id = :sent_to_dep_id, origin_dep_id = :origin_dep_id,
             doc_count = :doc_count, action_no = :action_no,
             records_signature = :records_signature, records_original = :records_original,
             remarks = :remarks
             WHERE id = :id'
        );
        $stmt->execute([
            'serial_no' => $f['serial_no'], 'incoming_date' => $f['incoming_date'], 'letter_date' => $f['letter_date'],
            'archive' => $f['archive'], 'office' => $f['office'], 'name' => $f['name'],
            'sent_to_dep_id' => $f['sent_to_dep_id'], 'origin_dep_id' => $f['origin_dep_id'],
            'doc_count' => $f['doc_count'] !== '' ? (int)$f['doc_count'] : null,
            'action_no' => $f['action_no'],
            'records_signature' => $f['records_signature'], 'records_original' => $f['records_original'],
            'remarks' => $f['remarks'],
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
        <div><label>مسلسل نمبر *</label><input type="text" name="serial_no" value="<?= e($f['serial_no']) ?>" required></div>
        <div><label>آرشیف</label><input type="text" name="archive" value="<?= e($f['archive']) ?>"></div>
        <div><label>شعبه </label><input type="text" name="office" value="<?= e($f['office']) ?>"></div>
        <div><label>نیټه (د مکتوب)</label><input type="text" name="letter_date" value="<?= e($f['letter_date']) ?>" placeholder="1445/1/1"></div>
        <div><label>نیټه (د ثبت)</label><input type="text" name="incoming_date" value="<?= e($f['incoming_date']) ?>" placeholder="1445/1/1"></div>
        <div>
            <label>مرسل</label>
            <select name="origin_dep_id" class="form-control searchable-select">
                <option value="">-- انتخاب ریاست --</option>
                <?php foreach ($departments as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>" <?= ((int)$dep['id'] === (int)($f['origin_dep_id'] ?? 0)) ? 'selected' : '' ?>><?= e($dep['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>مرسل الیه (اداره)</label>
            <select name="sent_to_dep_id" class="form-control searchable-select">
                <option value="">-- انتخاب ریاست --</option>
                <?php foreach ($departments as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>" <?= ((int)$dep['id'] === (int)($f['sent_to_dep_id'] ?? 0)) ? 'selected' : '' ?>><?= e($dep['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>تعداد</label><input type="number" name="doc_count" value="<?= e((string)$f['doc_count']) ?>"></div>
        <div><label> اسم | نوم</label><input type="text" name="name" value="<?= e($f['name']) ?>"></div>
        <div><label>نمره اجرایه ارشیف</label><input type="text" name="action_no" value="<?= e($f['action_no']) ?>"></div>
    </div>

    <fieldset>
        <legend>د اوراقو د ضبط شعبه</legend>
        <div class="form-grid">
            <div class="checkbox-row"><input type="checkbox" name="records_signature" id="rs" <?= $f['records_signature'] ? 'checked' : '' ?>><label for="rs" style="margin:0">امضاء</label></div>
            <div class="checkbox-row"><input type="checkbox" name="records_original" id="ro" <?= $f['records_original'] ? 'checked' : '' ?>><label for="ro" style="margin:0">اصل</label></div>
        </div>
    </fieldset>

    <label>ملاحظات</label>
    <textarea name="remarks"><?= e($f['remarks']) ?></textarea>

    <div style="margin-top:22px; display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit">بدلونونه خوندي کول</button>
        <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">لغوه کول</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>