<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$activePage = 'departments';
$pageTitle  = 'نوی ریاست / آمریت - ' . APP_NAME;

require __DIR__ . '/../includes/header.php';
?>

<div class="page-header">

    <h1>نوی ریاست / آمریت</h1>

    <div class="row-actions">

        <a href="list.php" class="btn btn-secondary">
            ← بېرته لیست ته
        </a>

    </div>

</div>

<form method="post"
      action="store.php"
      class="card">

    <?= csrf_field() ?>

    <div class="form-grid">

        <div class="field">

            <label>
                د ریاست / آمریت نوم <span class="text-danger">*</span>
            </label>

            <input type="text"
                   name="name"
                   class="form-control"
                   maxlength="255"
                   required
                   autofocus
                   value="<?= e($_SESSION['old']['name'] ?? '') ?>">

        </div>

        <div class="field">

            <label>تشریح</label>

            <textarea
                name="description"
                class="form-control"
                rows="6"><?= e($_SESSION['old']['description'] ?? '') ?></textarea>

        </div>

    </div>

    <div class="row-actions" style="margin-top:20px;">

        <button type="submit"
                class="btn btn-primary">

            💾 ثبت کول

        </button>

        <a href="list.php"
           class="btn btn-secondary">

            لغوه

        </a>

    </div>

</form>

<?php
    unset($_SESSION['old']);
    require __DIR__ . '/../includes/footer.php';
?>
