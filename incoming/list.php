<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$activePage = 'incoming';
$pageTitle  = 'وارده مکتوبونه - ' . APP_NAME;

$q       = trim($_GET['q'] ?? '');
$from    = trim($_GET['from'] ?? '');
$to      = trim($_GET['to'] ?? '');
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));

$where  = [];
$params = [];

if ($q !== '') {
    $where[] = '(incoming_letters.serial_no LIKE :q OR incoming_letters.subject LIKE :q2 OR incoming_letters.incoming_no LIKE :q3 OR incoming_letters.action_no LIKE :q4 OR incoming_letters.dossier_no LIKE :q5 OR sent_dep.name LIKE :q6 OR origin_dep.name LIKE :q7)';
    $params['q'] = $params['q2'] = $params['q3'] = $params['q4'] = $params['q5'] = $params['q6'] = $params['q7'] = "%$q%";
}
if ($from !== '') { $where[] = 'incoming_letters.letter_date >= :from'; $params['from'] = $from; }
if ($to !== '')   { $where[] = 'incoming_letters.letter_date <= :to';   $params['to']   = $to; }

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = db()->prepare("SELECT COUNT(*) c FROM incoming_letters LEFT JOIN departments AS sent_dep ON sent_dep.id = incoming_letters.sent_to_dep_id LEFT JOIN departments AS origin_dep ON origin_dep.id = incoming_letters.origin_dep_id $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT incoming_letters.*, sent_dep.name AS sent_to_department, origin_dep.name AS origin_department FROM incoming_letters LEFT JOIN departments AS sent_dep ON sent_dep.id = incoming_letters.sent_to_dep_id LEFT JOIN departments AS origin_dep ON origin_dep.id = incoming_letters.origin_dep_id $whereSql ORDER BY incoming_letters.id DESC LIMIT :lim OFFSET :off";
$stmt = db()->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue('lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue('off', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>وارده مکتوبونه (Incoming Letters)</h1>
    <div class="row-actions">
        <a href="add.php" class="btn btn-primary">+ نوی ثبت</a>
        <a href="export.php?<?= e(http_build_query($_GET)) ?>" class="btn btn-secondary">📤 Excel ته صادرول</a>
    </div>
</div>

<form method="get" class="card filter-bar">
    <div class="field">
        <label>لټون (سریال، ریاست، موضوع، وارده نمبر، مبداء)</label>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="لټون...">
    </div>
    <div class="field">
        <label>د نیټې پیل</label>
        <input type="text" name="from" value="<?= e($from) ?>" placeholder="1445/1/1">
    </div>
    <div class="field">
        <label>د نیټې پای</label>
        <input type="text" name="to" value="<?= e($to) ?>" placeholder="1445/12/30">
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
                <th>#</th><th>مسلسل نمبر</th><th>د اقدام او مراجعت نمبر</th><th>نیټه وردود</th><th>وارده نمبر</th><th>مرسله الیه</th>
                <th>مبداء</th><th>موضوع</th><th>دوسیه نمبر</th><th>عدد</th><th>د اوراقو نمبر</th><th>کړنې</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="12" class="empty-state">هېڅ ریکارډ ونه موندل شو</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= (int)$row['id'] ?></td>
                    <td><?= e($row['serial_no']) ?></td>
                    <td><?= e($row['action_no']) ?></td>
                    <td><?= e($row['letter_date']) ?></td>
                    <td><?= e($row['incoming_no']) ?></td>
                    <td><?= e($row['sent_to_department'] ?? '—') ?></td>
                    <td><?= e($row['origin_department'] ?? '—') ?></td>
                    <td class="subject-cell"><?= e(mb_strimwidth($row['subject'] ?? '', 0, 60, '…')) ?></td>
                    <td><?= e($row['dossier_no']) ?></td>
                    <td><?= e((string)$row['doc_count']) ?></td>
                    <td><?= e($row['pages_no']) ?></td>
                    <td class="row-actions">
                       <a href="view.php?id=<?= (int)$row['id'] ?>" title="کتل">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <a href="edit.php?id=<?= (int)$row['id'] ?>" title="سمول">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        <?php if ($currentUser['role'] !== 'viewer'): ?>
                        <form method="post" action="delete.php" style="display:inline"
                            onsubmit="return confirm('ایا تاسو ډاډه یاست چې دا ریکارډ حذف کړئ؟');">

                            <?= csrf_field() ?>

                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

                            <button type="submit"
                                    style="background:none;border:none;padding:0;cursor:pointer;"
                                    title="حذف">
                                <i class="fa-solid fa-trash" style="color:#dc3545;"></i>
                            </button>

                        </form>
                        <?php endif; ?>


                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="text-muted">ټول ریکارډونه: <?= $total ?></p>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php $qs = array_merge($_GET, ['page' => $p]); ?>
            <?php if ($p === $page): ?>
                <span class="current"><?= $p ?></span>
            <?php else: ?>
                <a href="?<?= e(http_build_query($qs)) ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
