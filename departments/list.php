<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$activePage = 'departments';
$pageTitle  = 'ریاستونه او آمریتونه - ' . APP_NAME;

$q = trim($_GET['q'] ?? '');

$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));

$where = [];
$params = [];

if ($q != '') {
    $where[] = "(name LIKE :q OR description LIKE :q2)";
    $params['q'] = "%$q%";
    $params['q2'] = "%$q%";
}

$whereSql = $where ? "WHERE ".implode(" AND ",$where) : "";

$count = db()->prepare("SELECT COUNT(*) c FROM departments $whereSql");
$count->execute($params);
$total = (int)$count->fetch()['c'];

$totalPages = max(1,ceil($total/$perPage));
$page=min($page,$totalPages);

$offset=($page-1)*$perPage;

$sql="SELECT *
      FROM departments
      $whereSql
      ORDER BY id DESC
      LIMIT :lim OFFSET :off";

$stmt=db()->prepare($sql);

foreach($params as $k=>$v){
    $stmt->bindValue(":".$k,$v);
}

$stmt->bindValue(':lim',$perPage,PDO::PARAM_INT);
$stmt->bindValue(':off',$offset,PDO::PARAM_INT);

$stmt->execute();

$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__.'/../includes/header.php';
?>

    <div class="page-header">

        <h1>ریاستونه او آمریتونه</h1>

        <div class="row-actions">

            <a href="add.php" class="btn btn-primary">
                + نوی ثبت
            </a>

        </div>

    </div>

    <form method="get" class="card filter-bar">

        <div class="field">

            <label>لټون</label>

            <input type="text"
                name="q"
                value="<?=e($q)?>"
                placeholder="د ریاست نوم">

        </div>

        <div class="field">

            <button class="btn btn-primary">
                لټون
            </button>

            <a href="list.php"
            class="btn btn-secondary">

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
                <th>ریاست / آمریت</th>
                <th>تشریح</th>
                <th>ثبت نېټه</th>
                <th>عملیات</th>

            </tr>

        </thead>

        <tbody>

            <?php if(!$rows): ?>

                <tr>

                    <td colspan="5" class="empty-state">

                    هیڅ معلومات ونه موندل شول

                    </td>

                </tr>

            <?php endif; ?>

            <?php foreach($rows as $row): ?>

            <tr>

                <td><?= (int)$row['id']?></td>

                <td><?=e($row['name'])?></td>

                <td>

                <?=e(mb_strimwidth($row['description'],0,80,'...'))?>

                </td>

                <td><?=e($row['created_at'])?></td>

                <td class="row-actions">

                    <a href="view.php?id=<?=$row['id']?>">

                    <i class="fa-solid fa-eye"
                    style="font-size:18px;color:#0d6efd;"></i>

                    </a>

                    <a href="edit.php?id=<?=$row['id']?>"
                    style="margin:0 10px;">

                    <i class="fa-solid fa-pen-to-square"
                    style="font-size:18px;color:#198754;"></i>

                    </a>

                    <?php if($currentUser['role']!='viewer'): ?>

                    <form method="post"
                        action="delete.php"
                        style="display:inline"
                        onsubmit="return confirm('حذف شي؟');">

                        <?=csrf_field()?>

                        <input type="hidden"
                        name="id"
                        value="<?=$row['id']?>">

                        <button type="submit"
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

    ټول ریکارډونه :
    <?= $total ?>

    </p>

    <?php if($totalPages>1): ?>

    <div class="pagination">

        <?php for($p=1;$p<=$totalPages;$p++): ?>

            <?php $qs=array_merge($_GET,['page'=>$p]);?>

            <?php if($page==$p): ?>

                <span class="current"><?=$p?></span>

            <?php else: ?>

                <a href="?<?=e(http_build_query($qs))?>">

                <?=$p?>

            </a>

            <?php endif;?>

        <?php endfor;?>

    </div>

<?php endif;?>

</div>

<?php require __DIR__.'/../includes/footer.php'; ?>
