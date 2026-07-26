<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$activePage = 'departments';
$pageTitle  = 'د ریاست / آمریت معلومات - ' . APP_NAME;


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
        د ریاست / آمریت معلومات
    </h1>


    <div class="row-actions">

        <a href="list.php"
           class="btn btn-secondary">

            ← بېرته

        </a>


        <a href="edit.php?id=<?= (int)$department['id'] ?>"
           class="btn btn-success">

            ✏ سمول

        </a>

    </div>

</div>



<div class="card">


    <table class="data-table">


        <tr>

            <th width="200">
                ID
            </th>

            <td>
                <?= (int)$department['id'] ?>
            </td>

        </tr>


        <tr>

            <th>
                د ریاست / آمریت نوم
            </th>

            <td>
                <?= e($department['name']) ?>
            </td>

        </tr>


        <tr>

            <th>
                تشریح
            </th>

            <td>
                <?= nl2br(e($department['description'] ?? '')) ?>
            </td>

        </tr>


        <tr>

            <th>
                د ثبت نېټه
            </th>

            <td>
                <?= e($department['created_at']) ?>
            </td>

        </tr>


    </table>


</div>



<?php

require __DIR__ . '/../includes/footer.php';

?>
