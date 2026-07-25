<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$q    = trim($_GET['q'] ?? '');
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');

$where  = [];
$params = [];
if ($q !== '') {
    $where[] = '(serial_no LIKE :q OR sent_from LIKE :q2 OR subject LIKE :q3 OR incoming_no LIKE :q4 OR origin LIKE :q5)';
    $params['q'] = $params['q2'] = $params['q3'] = $params['q4'] = $params['q5'] = "%$q%";
}
if ($from !== '') { $where[] = 'letter_date >= :from'; $params['from'] = $from; }
if ($to !== '')   { $where[] = 'letter_date <= :to';   $params['to']   = $to; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = db()->prepare("SELECT * FROM incoming_letters $whereSql ORDER BY id ASC");
$stmt->execute($params);
$rows = $stmt->fetchAll();

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="incoming_letters_' . date('Y-m-d') . '.xls"');
echo "\xEF\xBB\xBF";
?>
<table border="1" dir="rtl">
<tr>
    <th>مسلسل لمبر</th><th>نیټه (ثبت)</th><th>نیټه (مکتوب)</th><th>وارده لمبر</th><th>مرسله الیه</th>
    <th>مبداء</th><th>موضوع</th><th>عدد</th><th>اوراق</th><th>د اقدام لمبر</th><th>ملاحظات</th>
</tr>
<?php foreach ($rows as $r): ?>
<tr>
    <td><?= e($r['serial_no']) ?></td>
    <td><?= e($r['incoming_date']) ?></td>
    <td><?= e($r['letter_date']) ?></td>
    <td><?= e($r['incoming_no']) ?></td>
    <td><?= e($r['sent_from']) ?></td>
    <td><?= e($r['origin']) ?></td>
    <td><?= e($r['subject']) ?></td>
    <td><?= e((string)$r['doc_count']) ?></td>
    <td><?= e($r['pages_no']) ?></td>
    <td><?= e($r['action_no']) ?></td>
    <td><?= e($r['remarks']) ?></td>
</tr>
<?php endforeach; ?>
</table>
