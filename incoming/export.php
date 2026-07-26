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
    $where[] = '(incoming_letters.serial_no LIKE :q OR incoming_letters.subject LIKE :q2 OR incoming_letters.incoming_no LIKE :q3 OR incoming_letters.dossier_no LIKE :q4 OR sent_dep.name LIKE :q5 OR origin_dep.name LIKE :q6)';
    $params['q'] = $params['q2'] = $params['q3'] = $params['q4'] = $params['q5'] = $params['q6'] = "%$q%";
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
    "SELECT incoming_letters.*, sent_dep.name AS sent_to_department, origin_dep.name AS origin_department
     FROM incoming_letters
     LEFT JOIN departments AS sent_dep ON sent_dep.id = incoming_letters.sent_to_dep_id
     LEFT JOIN departments AS origin_dep ON origin_dep.id = incoming_letters.origin_dep_id
     $whereSql
     ORDER BY incoming_letters.id ASC"
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
    'د اقدام او مراجعت نمبر',
    'نیټه وردود',
    'وارده نمبر',
    'مرسله الیه',
    'مبداء',
    'موضوع',
    'دوسیه نمبر',
    'عدد',
    'د اوراقو نمبر',
    'ثبت-امضاء',
    'ثبت-ضمیمه',
    'ثبت-د پاڼو شمېر',
    'ملاحظات'
];

$sheet->fromArray($headers, null, 'A1');


// Data
$rowNumber = 2;

foreach ($rows as $r) {

    $sheet->fromArray([
        $r['serial_no'] ?? '',
        $r['action_no'] ?? '',
        $r['letter_date'] ?? '',
        $r['incoming_no'] ?? '',
        $r['sent_to_department'] ?? '',
        $r['origin_department'] ?? '',
        $r['subject'] ?? '',
        $r['dossier_no'] ?? '',
        $r['doc_count'] ?? '',
        $r['pages_no'] ?? '',

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
$fileName = 'incoming_letters_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;
