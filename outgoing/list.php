<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$activePage = 'outgoing';
$pageTitle  = 'صادره مکتوبونه - ' . APP_NAME;
$q       = trim($_GET['q'] ?? '');
$from    = trim($_GET['from'] ?? '');
$to      = trim($_GET['to'] ?? '');

$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$where  = [];
$params = [];
// Search
if ($q !== '') {

    $where[] = "
        (
            outgoing_letters.serial_no LIKE :q
            OR sent_dep.name LIKE :q2
            OR outgoing_letters.subject LIKE :q3
            OR ref_dep.name LIKE :q4
        )
        ";

    $params['q']  = "%$q%";
    $params['q2'] = "%$q%";
    $params['q3'] = "%$q%";
    $params['q4'] = "%$q%";
}
// Date filter
if ($from !== '') {

    $where[] = "outgoing_letters.letter_date >= :from";

    $params['from'] = $from;
}
if ($to !== '') {

    $where[] = "outgoing_letters.letter_date <= :to";

    $params['to'] = $to;
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// COUNT
$countStmt = db()->prepare("

        SELECT COUNT(*) AS c

        FROM outgoing_letters
        LEFT JOIN departments AS sent_dep
            ON sent_dep.id = outgoing_letters.sent_to_dep_id

        LEFT JOIN departments AS ref_dep
            ON ref_dep.id = outgoing_letters.reference_dep_id

        $whereSql

    ");
$countStmt->execute($params);

$total = (int)$countStmt->fetch()['c'];

$totalPages = max(1, ceil($total / $perPage));

$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
// DATA
$sql = "

        SELECT

        outgoing_letters.*,

        sent_dep.name AS sent_to_department,

        ref_dep.name AS reference_department

        FROM outgoing_letters
            LEFT JOIN departments AS sent_dep
                ON sent_dep.id = outgoing_letters.sent_to_dep_id

            LEFT JOIN departments AS ref_dep
                ON ref_dep.id = outgoing_letters.reference_dep_id

        $whereSql

        ORDER BY outgoing_letters.id DESC
        LIMIT :lim OFFSET :off

    ";
$stmt = db()->prepare($sql);

foreach ($params as $key => $value) {

    $stmt->bindValue(":" . $key, $value);
}

$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// echo "<pre>";
// print_r($rows);
// echo "</pre>";
// exit;
require __DIR__ . '/../includes/header.php';

?>
<div class="page-header">

    <h1>صادره مکتوبونه (Outgoing Letters)</h1>
    <div class="row-actions">

        <a href="add.php" class="btn btn-primary">
            + نوی ثبت
        </a>

        <a href="export.php?<?= e(http_build_query($_GET)) ?>"
            class="btn btn-secondary">
            📤 Excel ته صادرول
        </a>
    </div>

</div>
<form method="get" class="card filter-bar">
    <div class="field">
        <label>
            لټون (سریال، مرسل الیه، موضوع، مرجع)
        </label>

        <input type="text" name="q" value="<?= e($q) ?>" placeholder="لټون...">
    </div>

    <div class="field">
        <label> د نیټې پیل </label>
        <input type="text" name="from" value="<?= e($from) ?>" placeholder="1445/1/1">
    </div>

    <div class="field">
        <label>د نیټې پای </label>
        <input type="text" name="to" value="<?= e($to) ?>" placeholder="1445/12/30">
    </div>
    <div class="field">

        <button class="btn btn-primary">

            لټون

        </button>
        <a href="list.php" class="btn btn-secondary">

            پاکول

        </a>
    </div>
</form>
<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>

                <tr>

                    <th>#</th>

                    <th>مسلسل نمبر</th>

                    <th>دوسیه نمبر</th>

                    <th>نیټه</th>

                    <th>مرسل الیه</th>

                    <th>مرجع</th>

                    <th>موضوع</th>

                    <th>رسیداتو نمبر</th>

                    <th>اسناد</th>

                    <th>کړنې</th>
                </tr>

            </thead>
            <tbody>
                <?php if (!$rows): ?>

                    <tr>

                        <td colspan="10" class="empty-state">

                            هېڅ ریکارډ ونه موندل شو

                        </td>

                    </tr>

                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <?= (int)$row['id'] ?>
                        </td>
                        <td>
                            <?= e($row['serial_no'] ?? '') ?>
                        </td>
                        <td>

                            <?= e($row['dossier_no'] ?? '') ?: '—' ?>

                        </td>
                        <td>

                            <?= e($row['letter_date'] ?? '') ?>

                        </td>
                        <td>

                            <?= e($row['sent_to_department'] ?? '—') ?>

                        </td>
                        <td>

                            <?= e($row['reference_department'] ?? '—') ?>

                        </td>
                        <td class="subject-cell">

                            <?= e(mb_strimwidth($row['subject'] ?? '', 0, 70, '…')) ?>

                        </td>
                        <td>
                            <?= e($row['receipts_no'] ?? '') ?>
                        </td>
                        <td>
                            <span class="badge <?=
                                (!empty($row['records_signature']) ||
                                    !empty($row['records_attachment']) ||
                                    !empty($row['records_original']))
                                    ? 'badge-yes' : 'badge-no'
                                ?>">

                                ثبت شعبه

                            </span>
                            <span class="badge <?=
                                (!empty($row['exec_signature']) ||
                                    !empty($row['exec_attachment']) ||
                                    !empty($row['exec_original']))
                                    ? 'badge-yes' : 'badge-no'
                                ?>">

                                اجرائیه

                            </span>
                        </td>
                        <td class="row-actions">
                            <a href="view.php?id=<?= $row['id'] ?>">

                                <i class="fa-solid fa-eye"
                                    style="color:#0d6efd;font-size:18px"></i>

                            </a>
                            <a href="edit.php?id=<?= $row['id'] ?>"
                                style="margin:0 10px">

                                <i class="fa-solid fa-pen-to-square"
                                    style="color:#198754;font-size:18px"></i>

                            </a>
                            <?php if ($currentUser['role'] !== 'viewer'): ?>
                                <form method="post"
                                    action="delete.php"
                                    style="display:inline"
                                    onsubmit="return confirm('ایا تاسو ډاډه یاست؟');">
                                    <?= csrf_field() ?>
                                    <input type="hidden"
                                        name="id"
                                        value="<?= $row['id'] ?>">
                                    <button type="submit"
                                        style="border:none;background:none;cursor:pointer">
                                        <i class="fa-solid fa-trash"
                                            style="color:#dc3545;font-size:18px"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-muted">

        ټول ریکارډونه: <?= $total ?>

    </p>
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php $qs = array_merge($_GET, ['page' => $p]); ?>
                <?php if ($p == $page): ?>
                    <span class="current">
                        <?= $p ?>
                    </span>
                <?php else: ?>
                    <a href="?<?= e(http_build_query($qs)) ?>">

                        <?= $p ?>

                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>