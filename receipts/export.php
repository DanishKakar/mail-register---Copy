<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$q    = trim($_GET['q'] ?? '');
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');

$where  = [];
$params = [];

if ($q !== '') {
    $where[] = '(receipts.serial_no LIKE :q OR receipts.archive LIKE :q2 OR receipts.office LIKE :q3 OR receipts.name LIKE :q4 OR receipts.action_no LIKE :q5 OR sent_dep.name LIKE :q6 OR origin_dep.name LIKE :q7)';
    $params['q'] = $params['q2'] = $params['q3'] = $params['q4'] = $params['q5'] = $params['q6'] = $params['q7'] = "%$q%";
}

if ($from !== '') {
    $where[] = 'letter_date >= :from';
    $params['from'] = $from;
}

if ($to !== '') {
    $where[] = 'letter_date <= :to';
    $params['to'] = $to;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare(
    "SELECT receipts.*, sent_dep.name AS sent_to_department, origin_dep.name AS origin_department
     FROM receipts
     LEFT JOIN departments AS sent_dep ON sent_dep.id = receipts.sent_to_dep_id
     LEFT JOIN departments AS origin_dep ON origin_dep.id = receipts.origin_dep_id
     $whereSql
     ORDER BY receipts.id ASC"
);

$stmt->execute($params);
$rows = $stmt->fetchAll();


$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('وارده مکتوبونه');


// RTL
$sheet->setRightToLeft(true);


// Headers
$headers = [
    'مسلسل نمبر',
    'آرشیف',
    'نیټه (د مکتوب)',
    'نیټه (د ثبت)',
    'شعبه',
    'اسم | نوم',
    'مرسله الیه',
    'مبداء',
    'عدد',
    'نمره اجرایه ارشیف',
    'ثبت-امضاء',
    'ثبت-اصل',
    'ملاحظات'
];

$sheet->fromArray($headers, null, 'A1');


// Data
$rowNumber = 2;

foreach ($rows as $r) {

    $sheet->fromArray([
        $r['serial_no'] ?? '',
        $r['archive'] ?? '',
        $r['letter_date'] ?? '',
        $r['incoming_date'] ?? '',
        $r['office'] ?? '',
        $r['name'] ?? '',
        $r['sent_to_department'] ?? '',
        $r['origin_department'] ?? '',
        $r['doc_count'] ?? '',
        $r['action_no'] ?? '',
        !empty($r['records_signature']) ? 'بلې' : 'نه',
        !empty($r['records_original']) ? 'بلې' : 'نه',
        $r['remarks'] ?? ''

    ], null, 'A' . $rowNumber);

    $rowNumber++;
}


// Styling
$lastColumn = $sheet->getHighestColumn();
$lastRow    = $sheet->getHighestRow();


$sheet->getStyle("A1:{$lastColumn}{$lastRow}")
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
    ->setVertical(Alignment::VERTICAL_CENTER);


$sheet->getStyle("A1:{$lastColumn}1")
    ->getFont()
    ->setBold(true);


$sheet->getStyle("A1:{$lastColumn}1")
    ->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('D9EAF7');


// Auto width
foreach ($sheet->getColumnIterator() as $column) {
    $sheet->getColumnDimension($column->getColumnIndex())
        ->setAutoSize(true);
}


// Download
$fileName = 'درسیداتو لیست_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;