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
$departments = db()->query('SELECT id, name FROM departments ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$f = [
    'serial_no' => '', 'incoming_date' => '', 'letter_date' => '', 'incoming_no' => '', 'dossier_no' => '',
    'subject' => '', 'doc_count' => '',
    'pages_no' => '', 'action_no' => '', 'remarks' => '',
    'sent_to_dep_id' => null, 'origin_dep_id' => null,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $f['serial_no'] = trim($_POST['serial_no'] ?? '');
    $f['incoming_date'] = trim($_POST['incoming_date'] ?? '');
    $f['letter_date'] = trim($_POST['letter_date'] ?? '');
    $f['incoming_no'] = trim($_POST['incoming_no'] ?? '');
    $f['dossier_no'] = trim($_POST['dossier_no'] ?? '');
    $f['subject'] = trim($_POST['subject'] ?? '');
    $f['doc_count'] = trim($_POST['doc_count'] ?? '');
    $f['pages_no'] = trim($_POST['pages_no'] ?? '');
    $f['action_no'] = trim($_POST['action_no'] ?? '');
    $f['remarks'] = trim($_POST['remarks'] ?? '');
    $f['sent_to_dep_id'] = !empty($_POST['sent_to_dep_id']) ? (int)$_POST['sent_to_dep_id'] : null;
    $f['origin_dep_id'] = !empty($_POST['origin_dep_id']) ? (int)$_POST['origin_dep_id'] : null;

    $sentToName = '';
    $originName = '';
    foreach ($departments as $dep) {
        if ((int)$dep['id'] === (int)$f['sent_to_dep_id']) {
            $sentToName = $dep['name'];
        }
        if ((int)$dep['id'] === (int)$f['origin_dep_id']) {
            $originName = $dep['name'];
        }
    }

    $f['sent_from'] = $sentToName;
    $f['origin'] = $originName;

    if ($f['serial_no'] === '') {
        $errors[] = 'د مسلسل نمبر ډکول لازمي دي.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO incoming_letters
             (serial_no, incoming_date, letter_date, incoming_no, dossier_no, sent_to_dep_id, origin_dep_id, subject,
              doc_count, pages_no, action_no, remarks, created_by)
             VALUES
             (:serial_no, :incoming_date, :letter_date, :incoming_no, :dossier_no, :sent_to_dep_id, :origin_dep_id, :subject,
              :doc_count, :pages_no, :action_no, :remarks, :created_by)'
        );
        $stmt->execute([
            'serial_no' => $f['serial_no'],
            'incoming_date' => $f['incoming_date'],
            'letter_date' => $f['letter_date'],
            'incoming_no' => $f['incoming_no'],
            'dossier_no' => $f['dossier_no'],
            'sent_to_dep_id' => $f['sent_to_dep_id'],
            'origin_dep_id' => $f['origin_dep_id'],
            'subject' => $f['subject'],
            'doc_count' => $f['doc_count'] !== '' ? (int)$f['doc_count'] : null,
            'pages_no' => $f['pages_no'],
            'action_no' => $f['action_no'],
            'remarks' => $f['remarks'],
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
        <div><label>مسلسل او مشترک نمبر <span style="color:red;">*</span></label><input type="text" name="serial_no" value="<?= e($f['serial_no']) ?>" required></div>
        <div><label>نیټه (د ثبت) <span style="color:red;">*</span></label><input type="text" name="incoming_date" required value="<?= e($f['incoming_date']) ?>" placeholder="1445/1/1"></div>
        <div><label>نیټه (د مکتوب) <span style="color:red;">*</span></label><input type="text" name="letter_date" required value="<?= e($f['letter_date']) ?>" placeholder="1445/1/1"></div>
        <div><label>د وارده مکتوب نمبر <span style="color:red;">*</span></label><input type="text" name="incoming_no" required value="<?= e($f['incoming_no']) ?>"></div>
        <div>
            <label>مرسله الیه (اداره) <span style="color:red;">*</span></label>
            <select name="sent_to_dep_id" required class="form-control searchable-select">
                <option value="">-- انتخاب اداره --</option>
                <?php foreach ($departments as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>" <?= ((int)$dep['id'] === (int)($f['sent_to_dep_id'] ?? 0)) ? 'selected' : '' ?>><?= e($dep['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>مبداء (اداره) <span style="color:red;">*</span></label>
            <select name="origin_dep_id" required class="form-control searchable-select">
                <option value="">-- انتخاب اداره --</option>
                <?php foreach ($departments as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>" <?= ((int)$dep['id'] === (int)($f['origin_dep_id'] ?? 0)) ? 'selected' : '' ?>><?= e($dep['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>عدد</label><input type="number" name="doc_count" value="<?= e($f['doc_count']) ?>"></div>
        <div><label>د اوراقو نمبر</label><input type="text" name="pages_no" value="<?= e($f['pages_no']) ?>"></div>
        <div><label>د اقدام او مراجعت نمبر</label><input type="text" name="action_no" value="<?= e($f['action_no']) ?>"></div>
        <div><label>دوسیه نمبر</label><input type="text" name="dossier_no" value="<?= e($f['dossier_no']) ?>"></div>
    </div>

    <label>د مطلب خلاصه (موضوع) <span style="color:red;">*</span></label>
    <textarea name="subject" required><?= e($f['subject']) ?></textarea>

    <label>ملاحظات</label>
    <textarea name="remarks"><?= e($f['remarks']) ?></textarea>

    <div style="margin-top:22px; display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit">ثبت کول</button>
        <a href="list.php" class="btn btn-secondary">لغوه کول</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
