<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

$q    = trim($_GET['q'] ?? '');
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');

$where  = [];
$params = [];
if ($q !== '') {
    $where[] = '(serial_no LIKE :q OR sent_to LIKE :q2 OR subject LIKE :q3 OR reference_no LIKE :q4 OR dossier_no LIKE :q5)';
    $params['q'] = $params['q2'] = $params['q3'] = $params['q4'] = $params['q5'] = "%$q%";
}
if ($from !== '') { $where[] = 'letter_date >= :from'; $params['from'] = $from; }
if ($to !== '')   { $where[] = 'letter_date <= :to';   $params['to']   = $to; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = db()->prepare("SELECT * FROM outgoing_letters $whereSql ORDER BY id ASC");
$stmt->execute($params);
$rows = $stmt->fetchAll();

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="outgoing_letters_' . date('Y-m-d') . '.xls"');
echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel renders Pashto/Dari correctly
?>
<table border="1" dir="rtl">
<tr>
    <th>مسلسل لمبر</th><th>دوسیه نمبر</th><th>نیټه (صدور)</th><th>نیټه (مکتوب)</th><th>مرسل الیه</th><th>مرجع</th>
    <th>موضوع</th><th>ثبت-امضاء</th><th>ثبت-ضمیمه</th><th>ثبت-د پاڼو شمېر</th><th>ثبت-اصل</th>
    <th>اجرائیه-امضاء</th><th>اجرائیه-ضمیمه</th><th>اجرائیه-د پاڼو شمېر</th><th>اجرائیه-اصل</th>
    <th>د توزیع یادداشت</th><th>ملاحظات</th>
</tr>
<?php foreach ($rows as $r): ?>
<tr>
    <td><?= e($r['serial_no']) ?></td>
    <td><?= e($r['dossier_no']) ?></td>
    <td><?= e($r['issue_date']) ?></td>
    <td><?= e($r['letter_date']) ?></td>
    <td><?= e($r['sent_to']) ?></td>
    <td><?= e($r['reference_no']) ?></td>
    <td><?= e($r['subject']) ?></td>
    <td><?= $r['records_signature'] ? 'هو' : 'نه' ?></td>
    <td><?= $r['records_attachment'] ? 'هو' : 'نه' ?></td>
    <td><?= e((string)$r['records_attachment_count']) ?></td>
    <td><?= $r['records_original'] ? 'هو' : 'نه' ?></td>
    <td><?= $r['exec_signature'] ? 'هو' : 'نه' ?></td>
    <td><?= $r['exec_attachment'] ? 'هو' : 'نه' ?></td>
    <td><?= e((string)$r['exec_attachment_count']) ?></td>
    <td><?= $r['exec_original'] ? 'هو' : 'نه' ?></td>
    <td><?= e($r['distribution_notes']) ?></td>
    <td><?= e($r['remarks']) ?></td>
</tr>
<?php endforeach; ?>
</table>