<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

if ($currentUser['role'] === 'viewer') {
    http_response_code(403);
    die('تاسو د ثبت صلاحیت نلرئ.');
}

$activePage = 'outgoing';
$pageTitle  = 'نوی صادره ثبت - ' . APP_NAME;
$errors = [];
$f = [
    'serial_no' => '', 'receipts_no' => '', 'issue_date' => '', 'letter_date' => '', 'sent_to' => '', 'dossier_no' => '',
    'subject' => '', 'distribution_notes' => '', 'remarks' => '',
    'records_signature' => 0, 'records_attachment' => 0, 'records_original' => 0, 'records_attachment_pages' => '',
    'exec_signature' => 0,  'exec_attachment_pages' => '', 'exec_attachment' => 0, 'exec_original' => 0,
    'sent_to_dep_id' => '', 'reference_dep_id' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $checkboxes = [
        'records_signature',
        'records_attachment',
        'records_original',
        'exec_signature',
        'exec_attachment',
        'exec_original',
    ];

    foreach ($f as $key => $default) {

        if (in_array($key, $checkboxes)) {
            $f[$key] = isset($_POST[$key]) ? 1 : 0;
        } else {
            $f[$key] = trim($_POST[$key] ?? '');
        }

    }

    if ($f['serial_no'] === '') {
        $errors[] = 'د مسلسل نمبر ډکول لازمي دي.';
    }

    if (!$f['records_attachment']) {
        $f['records_attachment_pages'] = null;
    }

    if (!$f['exec_attachment']) {
        $f['exec_attachment_pages'] = null;
    }

// echo '<pre>';
// print_r($f);
// exit;

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO outgoing_letters
             (serial_no, receipts_no, issue_date, letter_date, sent_to_dep_id,
            reference_dep_id, dossier_no, subject,
              records_signature, records_attachment, records_original,
              exec_signature, exec_attachment, exec_original,records_attachment_pages, exec_attachment_pages,
              distribution_notes, remarks, created_by)
             VALUES
             (:serial_no, :receipts_no, :issue_date, :letter_date,  :sent_to_dep_id, :reference_dep_id, :dossier_no, :subject,
              :records_signature, :records_attachment, :records_original,
              :exec_signature, :exec_attachment, :exec_original, :records_attachment_pages, :exec_attachment_pages,
              :distribution_notes, :remarks, :created_by)'
        );
        $stmt->execute([
            'serial_no' => $f['serial_no'],
            'receipts_no' => $f['receipts_no'],
            'issue_date' => $f['issue_date'],
            'letter_date' => $f['letter_date'],
            'sent_to_dep_id' => $f['sent_to_dep_id'],
            'reference_dep_id' => $f['reference_dep_id'],
            'dossier_no' => $f['dossier_no'],
            'subject' => $f['subject'],
            'records_signature' => $f['records_signature'],
            'records_attachment' => $f['records_attachment'],
            'records_original' => $f['records_original'],
            'exec_signature' => $f['exec_signature'],
            'exec_attachment' => $f['exec_attachment'],
            'exec_original' => $f['exec_original'],
            'records_attachment_pages' => $f['records_attachment_pages'],
            'exec_attachment_pages' => $f['exec_attachment_pages'],
            'distribution_notes' => $f['distribution_notes'],
            'remarks' => $f['remarks'],
            'created_by' => $currentUser['id'],
        ]);
        flash_set('success', 'د صادره مکتوب په بریالیتوب سره ثبت شو.');
        redirect('list.php');
    }
}

// select departments for the dropdown
$departments = db()
    ->query("SELECT id, name FROM departments ORDER BY name ASC")
    ->fetchAll(PDO::FETCH_ASSOC);


require __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><h1>نوی صادره مکتوب ثبت کول</h1></div>

<?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" class="card">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div><label>مسلسل او مشترک نمبر <span style="color:red;">*</span></label><input type="text" name="serial_no" value="<?= e($f['serial_no']) ?>" required></div>
        <div><label>نیټه (د صدور) <span style="color:red;">*</span></label><input type="text" name="issue_date" required value="<?= e($f['issue_date']) ?>" placeholder="1445/1/1"></div>
        <div><label>نیټه (د مکتوب) <span style="color:red;">*</span></label><input type="text" name="letter_date" required value="<?= e($f['letter_date']) ?>" placeholder="1445/1/1"></div>
        <div>
            <label>دوسیه نمبر</label>
            <input type="text" name="dossier_no" value="<?= e($f['dossier_no']) ?>">
        </div>
         <div>
            <label>رسیداتو نمبر</label>
            <input type="text" name="receipts_no" value="<?= e($f['receipts_no']) ?>">
        </div>
        <div class="field">
            <label>مرسل الیه (لیږل شوی چاته) <span style="color:red;">*</span></label>
            <select name="sent_to_dep_id" required class="form-control searchable-select">

                <option value="">
                -- انتخاب اداره --
                </option>
                <?php foreach ($departments as $dep): ?>
                    <option
                        value="<?= $dep['id'] ?>"
                        <?= $f['sent_to_dep_id'] == $dep['id'] ? 'selected' : '' ?>>
                        <?= e($dep['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>مرجع <span style="color:red;">*</span></label>
            <select name="reference_dep_id" required class="form-control searchable-select">
                <option value="">
                -- انتخاب اداره --
                </option>
                <?php foreach ($departments as $dep): ?>
                    <option
                        value="<?= $dep['id'] ?>"
                        <?= $f['reference_dep_id'] == $dep['id'] ? 'selected' : '' ?>>
                        <?= e($dep['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <label>د مطلب خلاصه (موضوع) <span style="color:red;">*</span></label>
    <textarea name="subject" required><?= e($f['subject']) ?></textarea>

    <fieldset>
        <legend>د اوراقو د ضبط شعبه</legend>
        <div class="form-grid">
            <div class="checkbox-row"><input type="checkbox" name="records_signature" id="rs" <?= $f['records_signature'] ? 'checked' : '' ?>><label for="rs" style="margin:0">امضاء</label></div>
            <div class="checkbox-row"><input type="checkbox" name="records_attachment" id="ra" <?= $f['records_attachment'] ? 'checked' : '' ?>><label for="ra" style="margin:0">ضمیمه</label></div>
               <div id="records_pages_box"
                 style="<?= $f['records_attachment'] ? '' : 'display:none;margin-top:8px;' ?>">

                <input
                    type="number"
                    min="1"
                    name="records_attachment_pages"
                    placeholder="د ضمیمې د صفحو شمېر"
                    value="<?= e($f['records_attachment_pages']) ?>">
            </div>
            <div class="checkbox-row"><input type="checkbox" name="records_original" id="ro" <?= $f['records_original'] ? 'checked' : '' ?>><label for="ro" style="margin:0">اصل</label></div>
        </div>
    </fieldset>

    <fieldset>

        <legend>د اجرائیه ادارو شعبه</legend>

        <div class="form-grid">

            <div class="checkbox-row">
                <input type="checkbox"
                    id="es"
                    name="exec_signature"
                    <?= $f['exec_signature'] ? 'checked':'' ?>>
                <label for="es">امضاء</label>
            </div>

            <div>

                <div class="checkbox-row">
                    <input type="checkbox"
                        id="ea"
                        name="exec_attachment"
                        <?= $f['exec_attachment'] ? 'checked':'' ?>>
                    <label for="ea">ضمیمه</label>
                </div>

                <div id="exec_pages_box"
                    style="<?= $f['exec_attachment'] ? '' : 'display:none;margin-top:8px;' ?>">

                    <input
                        type="number"
                        min="1"
                        name="exec_attachment_pages"
                        placeholder="د ضمیمې د صفحو شمېر"
                        value="<?= e($f['exec_attachment_pages']) ?>">

                </div>

            </div>

            <div class="checkbox-row">
                <input type="checkbox"
                    id="eo"
                    name="exec_original"
                    <?= $f['exec_original'] ? 'checked':'' ?>>
                <label for="eo">اصل</label>
            </div>

        </div>

    </fieldset>

    <label>د توزیع او تسلیم یادداشتونه</label>
    <textarea name="distribution_notes"><?= e($f['distribution_notes']) ?></textarea>

    <label>ملاحظات</label>
    <textarea name="remarks"><?= e($f['remarks']) ?></textarea>

    <div style="margin-top:22px; display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit">ثبت کول</button>
        <a href="list.php" class="btn btn-secondary">لغوه کول</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
