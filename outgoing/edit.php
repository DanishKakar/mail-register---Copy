<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

if ($currentUser['role'] === 'viewer') {
    http_response_code(403);
    die('تاسو د سمون صلاحیت نلرئ.');
}

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare("
    SELECT 
        outgoing_letters.*,

        sent.name AS sent_to_name,

        ref.name AS reference_name

    FROM outgoing_letters

    LEFT JOIN departments sent
        ON sent.id = outgoing_letters.sent_to_dep_id

    LEFT JOIN departments ref
        ON ref.id = outgoing_letters.reference_dep_id

    WHERE outgoing_letters.id = :id

    LIMIT 1
");

$stmt->execute([
    'id' => $id
]);

$record = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$record) {
    flash_set('error', 'ریکارډ ونه موندل شو.');
    redirect('list.php');
}

$activePage = 'outgoing';
$pageTitle  = 'د صادره سمون - ' . APP_NAME;
$errors = [];

// select departments for the dropdown
$departments = db()
->query("SELECT id,name FROM departments ORDER BY name")
->fetchAll(PDO::FETCH_ASSOC);


$boolFields = [
    'records_signature',
    'records_attachment',
    'records_original',
    'exec_signature',
    'exec_attachment',
    'exec_original'
];

$textFields = [
    'serial_no',
    'receipts_no',
    'dossier_no',
    'issue_date',
    'letter_date',
    'subject',
    'distribution_notes',
    'remarks',
    'records_attachment_count',
    'exec_attachment_count'
];

// ADD THESE
$selectFields = [
    'sent_to_dep_id',
    'reference_dep_id'
];

$f = $record;

/*
|--------------------------------------------------------------------------
| Prevent Undefined Index Errors
|--------------------------------------------------------------------------
*/
$defaults = [
    'serial_no' => '',
    'receipts_no' => '',
    'dossier_no' => '',
    'issue_date' => '',
    'letter_date' => '',
    'subject' => '',
    'distribution_notes' => '',
    'remarks' => '',
    'records_signature' => 0,
    'records_attachment' => 0,
    'records_attachment_count' => '',
    'records_original' => 0,
    'exec_signature' => 0,
    'exec_attachment' => 0,
    'exec_attachment_count' => '',
    'exec_original' => 0,
    'sent_to_dep_id' => null,
    'reference_dep_id' => null,

];

$f = array_merge($defaults, $f);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_verify();

    foreach ($textFields as $field) {
        $f[$field] = trim($_POST[$field] ?? '');
    }


    // Department IDs
    $f['sent_to_dep_id'] = !empty($_POST['sent_to_dep_id'])
        ? (int)$_POST['sent_to_dep_id']
        : null;

    $f['reference_dep_id'] = !empty($_POST['reference_dep_id'])
        ? (int)$_POST['reference_dep_id']
        : null;


    foreach ($boolFields as $field) {
        $f[$field] = isset($_POST[$field]) ? 1 : 0;
    }


    if ($f['serial_no'] === '') {
        $errors[] = 'د مسلسل نمبر ډکول لازمي دي.';
    }


    if (!$errors) {

        $stmt = db()->prepare("
            UPDATE outgoing_letters SET

                serial_no = :serial_no,
                receipts_no = :receipts_no,
                dossier_no = :dossier_no,

                issue_date = :issue_date,
                letter_date = :letter_date,

                sent_to_dep_id = :sent_to_dep_id,
                reference_dep_id = :reference_dep_id,

                subject = :subject,

                records_signature = :records_signature,
                records_attachment = :records_attachment,
                records_attachment_count = :records_attachment_count,
                records_original = :records_original,

                exec_signature = :exec_signature,
                exec_attachment = :exec_attachment,
                exec_attachment_count = :exec_attachment_count,
                exec_original = :exec_original,

                distribution_notes = :distribution_notes,
                remarks = :remarks

            WHERE id = :id
        ");


        $stmt->execute([

            'serial_no' => $f['serial_no'],
            'receipts_no' => $f['receipts_no'],
            'dossier_no' => $f['dossier_no'],

            'issue_date' => $f['issue_date'],
            'letter_date' => $f['letter_date'],


            'sent_to_dep_id' => $f['sent_to_dep_id'],
            'reference_dep_id' => $f['reference_dep_id'],


            'subject' => $f['subject'],


            'records_signature' => $f['records_signature'],
            'records_attachment' => $f['records_attachment'],

            'records_attachment_count' =>
                $f['records_attachment_count'] !== ''
                ? (int)$f['records_attachment_count']
                : null,

            'records_original' => $f['records_original'],


            'exec_signature' => $f['exec_signature'],
            'exec_attachment' => $f['exec_attachment'],

            'exec_attachment_count' =>
                $f['exec_attachment_count'] !== ''
                ? (int)$f['exec_attachment_count']
                : null,

            'exec_original' => $f['exec_original'],


            'distribution_notes' => $f['distribution_notes'],
            'remarks' => $f['remarks'],


            'id' => $id

        ]);


        flash_set('success', 'بدلونونه خوندي شول.');
        redirect("view.php?id={$id}");

    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>د صادره مکتوب سمون (#<?= $id ?>)</h1>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $err): ?>
            <div><?= e($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="card">

    <?= csrf_field() ?>

    <div class="form-grid">
        <div>
            <label>مسلسل او مشترک نمبر *</label>
            <input type="text" name="serial_no" value="<?= e($f['serial_no']) ?>" required>
        </div>

        <div>
            <label>دوسیه نمبر</label>
            <input type="text" name="dossier_no" value="<?= e($f['dossier_no']) ?>">
        </div>

        <div>
            <label>رسیداتو نمبر</label>
            <input type="text" name="receipts_no" value="<?= e($f['receipts_no']) ?>">
        </div>

        <div>
            <label>نیټه (د صدور)</label>
            <input type="text" name="issue_date" value="<?= e($f['issue_date']) ?>">
        </div>

        <div>
            <label>نیټه (د مکتوب)</label>
            <input type="text" name="letter_date" value="<?= e($f['letter_date']) ?>">
        </div>

        <div>
            <label>مرسل الیه</label>
            <select name="sent_to_dep_id" class="form-control searchable-select">

                <option value="">
                -- انتخاب ریاست --
                </option>

                <?php foreach($departments as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>"
                        <?= ((int)$dep['id'] === (int)($f['sent_to_dep_id'] ?? 0)) ? 'selected' : '' ?>>
                        <?= e($dep['name']) ?>
                    </option>
                <?php endforeach; ?>

            </select>

        </div>

        <div>
            <label>مرجع</label>
            <select name="reference_dep_id" class="form-control searchable-select">

                <option value="">
                -- انتخاب ریاست --
                </option>


                <?php foreach($departments as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>"
                        <?= ((int)$dep['id'] === (int)($f['reference_dep_id'] ?? 0)) ? 'selected' : '' ?>>
                        <?= e($dep['name']) ?>
                    </option>
                <?php endforeach; ?>


                </select>

        </div>
    </div>

    <label>د مطلب خلاصه (موضوع)</label>
    <textarea name="subject"><?= e($f['subject']) ?></textarea>

    <fieldset>
        <legend>د اوراقو د ضبط شعبه</legend>

        <div class="form-grid">
            <div class="checkbox-row">
                <input type="checkbox" id="rs" name="records_signature" <?= $f['records_signature'] ? 'checked' : '' ?>>
                <label for="rs" style="margin:0">امضاء</label>
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="ra" name="records_attachment" data-count-toggle="ra-count" <?= $f['records_attachment'] ? 'checked' : '' ?>>
                <label for="ra" style="margin:0">ضمیمه</label>
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="ro" name="records_original" <?= $f['records_original'] ? 'checked' : '' ?>>
                <label for="ro" style="margin:0">اصل</label>
            </div>
        </div>

        <div id="ra-count" class="<?= $f['records_attachment'] ? '' : 'hidden-field' ?>" style="margin-top:10px; max-width:240px;">
            <label style="margin-top:0">د ضمیمې پاڼې شمېر</label>
            <input type="number" min="0" name="records_attachment_count" value="<?= e((string)$f['records_attachment_count']) ?>">
        </div>

    </fieldset>

    <fieldset>
        <legend>د اجرائیه ادارو شعبه</legend>

        <div class="form-grid">
            <div class="checkbox-row">
                <input type="checkbox" id="es" name="exec_signature" <?= $f['exec_signature'] ? 'checked' : '' ?>>
                <label for="es" style="margin:0">امضاء</label>
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="ea" name="exec_attachment" data-count-toggle="ea-count" <?= $f['exec_attachment'] ? 'checked' : '' ?>>
                <label for="ea" style="margin:0">ضمیمه</label>
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="eo" name="exec_original" <?= $f['exec_original'] ? 'checked' : '' ?>>
                <label for="eo" style="margin:0">اصل</label>
            </div>
        </div>

        <div id="ea-count" class="<?= $f['exec_attachment'] ? '' : 'hidden-field' ?>" style="margin-top:10px; max-width:240px;">
            <label style="margin-top:0">د ضمیمې پاڼې شمېر</label>
            <input type="number" min="0" name="exec_attachment_count" value="<?= e((string)$f['exec_attachment_count']) ?>">
        </div>

    </fieldset>

    <label>د توزیع او تسلیم یادداشتونه</label>
    <textarea name="distribution_notes"><?= e($f['distribution_notes']) ?></textarea>

    <label>ملاحظات</label>
    <textarea name="remarks"><?= e($f['remarks']) ?></textarea>

    <div style="margin-top:22px; display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit">بدلونونه خوندي کول</button>
        <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">لغوه کول</a>
    </div>

</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>