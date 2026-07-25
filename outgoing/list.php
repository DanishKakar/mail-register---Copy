<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$activePage = 'outgoing';
$pageTitle  = 'صادره مکتوبونه - ' . APP_NAME;

$q       = trim($_GET['q'] ?? '');
$from    = trim($_GET['from'] ?? '');
$to      = trim($_GET['to'] ?? '');

$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));

$where  = [];
$params = [];

if ($q !== '') {
    $where[] = "(serial_no LIKE :q
                OR sent_to LIKE :q2
                OR subject LIKE :q3
                OR reference_no LIKE :q4)";

    $params['q']  = "%$q%";
    $params['q2'] = "%$q%";
    $params['q3'] = "%$q%";
    $params['q4'] = "%$q%";
}

if ($from !== '') {
    $where[] = "letter_date >= :from";
    $params['from'] = $from;
}

if ($to !== '') {
    $where[] = "letter_date <= :to";
    $params['to'] = $to;
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$countStmt = db()->prepare("SELECT COUNT(*) AS c FROM outgoing_letters $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];

$totalPages = max(1, ceil($total / $perPage));
$page = min($page, $totalPages);

$offset = ($page - 1) * $perPage;

$sql = "SELECT *
        FROM outgoing_letters
        $whereSql
        ORDER BY id DESC
        LIMIT :lim OFFSET :off";

$stmt = db()->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue(":".$key, $value);
}

$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);

$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>صادره مکتوبونه (Outgoing Letters)</h1>

    <div class="row-actions">
        <a href="add.php" class="btn btn-primary">+ نوی ثبت</a>

        <a href="export.php?<?= e(http_build_query($_GET)) ?>"
           class="btn btn-secondary">
            📤 Excel ته صادرول
        </a>
    </div>
</div>

<form method="get" class="card filter-bar">

    <div class="field">
        <label>لټون (سریال، مرسل الیه، موضوع، مرجع)</label>

        <input
            type="text"
            name="q"
            value="<?= e($q) ?>"
            placeholder="لټون...">
    </div>

    <div class="field">
        <label>د نیټې پیل</label>

        <input
            type="text"
            name="from"
            value="<?= e($from) ?>"
            placeholder="1445/1/1">
    </div>

    <div class="field">
        <label>د نیټې پای</label>

        <input
            type="text"
            name="to"
            value="<?= e($to) ?>"
            placeholder="1445/12/30">
    </div>

    <div class="field">
        <button class="btn btn-primary" type="submit">لټون</button>

        <a href="list.php" class="btn btn-secondary">پاکول</a>
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
                <th>اسناد (ثبت/اجرائیه)</th>
                <th>کړنې</th>
            </tr>

            </thead>

            <tbody>

            <?php if (!$rows): ?>

                <tr>
                    <td colspan="9" class="empty-state">
                        هېڅ ریکارډ ونه موندل شو
                    </td>
                </tr>

            <?php endif; ?>

            <?php foreach ($rows as $row): ?>

                <tr>

                    <td><?= (int)$row['id'] ?></td>

                    <td><?= e($row['serial_no'] ?? '') ?></td>

                    <td>
                        <?= e($row['dossier_no'] ?? '') ?: '—' ?>
                    </td>

                    <td><?= e($row['letter_date'] ?? '') ?></td>

                    <td><?= e($row['sent_to'] ?? '') ?></td>

                    <td><?= e($row['reference_no'] ?? '') ?></td>
                    
                    <td class="subject-cell">
                        <?= e(mb_strimwidth($row['subject'] ?? '',0,70,'…')) ?>
                    </td>
                    
                    <td><?= e($row['receipts_no'] ?? '') ?></td>

                    <td>

                        <span class="badge <?= (!empty($row['records_signature']) || !empty($row['records_attachment']) || !empty($row['records_original'])) ? 'badge-yes' : 'badge-no' ?>">

                            ثبت شعبه

                            <?php
                            if (!empty($row['records_attachment'])) {

                                echo !empty($row['records_attachment_pages'])
                                    ? ' (' . (int)$row['records_attachment_pages'] . 'پ)'
                                    : '';
                            }
                            ?>

                        </span>

                        <span class="badge <?= (!empty($row['exec_signature']) || !empty($row['exec_attachment']) || !empty($row['exec_original'])) ? 'badge-yes' : 'badge-no' ?>">

                            اجرائیه

                            <?php
                            if (!empty($row['exec_attachment'])) {

                                echo !empty($row['exec_attachment_pages'])
                                    ? ' (' . (int)$row['exec_attachment_pages'] . 'پ)'
                                    : '';
                            }
                            ?>

                        </span>

                    </td>

                    <td class="row-actions">
                        <a href="view.php?id=<?= (int)$row['id'] ?>" title="کتل">
                            <i class="fa-solid fa-eye" style="font-size:18px;color:#0d6efd;"></i>
                        </a>

                        <a href="edit.php?id=<?= (int)$row['id'] ?>" title="سمول" style="margin:0 10px;">
                            <i class="fa-solid fa-pen-to-square" style="font-size:18px;color:#198754;"></i>
                        </a>

                        <?php if ($currentUser['role'] !== 'viewer'): ?>

                        <form method="post"
                            action="delete.php"
                            style="display:inline"
                            onsubmit="return confirm('ایا تاسو ډاډه یاست چې دا ریکارډ حذف کړئ؟');">

                            <?= csrf_field() ?>

                            <input type="hidden"
                                name="id"
                                value="<?= (int)$row['id'] ?>">

                            <button type="submit"
                                    title="حذف"
                                    style="background:none;border:none;padding:0;cursor:pointer;">

                                <i class="fa-solid fa-trash"
                                style="font-size:18px;color:#dc3545;"></i>

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

                    <span class="current"><?= $p ?></span>

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
