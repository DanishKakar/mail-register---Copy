<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$activePage = 'departments';
$pageTitle  = 'د ریاست / آمریت سمون - ' . APP_NAME;


$id = (int)($_GET['id'] ?? 0);


if ($id <= 0) {

    header("Location: list.php");
    exit;

}


$stmt = db()->prepare("
    SELECT *
    FROM departments
    WHERE id = :id
    LIMIT 1
");


$stmt->execute([
    ':id' => $id
]);


$department = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$department) {

    $_SESSION['error'] = 'ریکارډ پیدا نشو';

    header("Location: list.php");
    exit;

}


require __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <h1>
        د ریاست / آمریت سمون
    </h1>


    <div class="row-actions">

        <a href="list.php"
           class="btn btn-secondary">

            ← بېرته لیست ته

        </a>

    </div>

</div>



<form method="post"
      action="update.php"
      class="card">


    <?= csrf_field() ?>


    <input type="hidden"
           name="id"
           value="<?= (int)$department['id'] ?>">



    <div class="form-grid">


        <div class="field">

            <label>
                د ریاست / آمریت نوم
                <span class="text-danger">*</span>
            </label>


            <input type="text"
                   name="name"
                   class="form-control"
                   maxlength="255"
                   required
                   value="<?= e($department['name']) ?>">


        </div>



        <div class="field">


            <label>
                تشریح
            </label>


            <textarea
                name="description"
                class="form-control"
                rows="6"><?= e($department['description'] ?? '') ?></textarea>


        </div>


    </div>



    <div class="row-actions"
         style="margin-top:20px;">


        <button type="submit"
                class="btn btn-primary">

            💾 تازه کول

        </button>



        <a href="list.php"
           class="btn btn-secondary">

            لغوه

        </a>


    </div>



</form>



<?php

require __DIR__ . '/../includes/footer.php';

?>
