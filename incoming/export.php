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
    $where[] = '(serial_no LIKE :q OR sent_from LIKE :q2 OR subject LIKE :q3 OR incoming_no LIKE :q4 OR origin LIKE :q5)';
    $params['q'] = $params['q2'] = $params['q3'] = $params['q4'] = $params['q5'] = "%$q%";
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
    "SELECT * FROM incoming_letters $whereSql ORDER BY id ASC"
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
        $r['sent_from'] ?? '',
        $r['origin'] ?? '',
        $r['subject'] ?? '',
        $r['dossier_no'] ?? '',
        $r['doc_count'] ?? '',
        $r['pages_no'] ?? '',

        // د ثبت شعبه معلومات
        !empty($r['attachment_signed']) ? 'هو' : 'نه',
        !empty($r['attachment']) ? 'هو' : 'نه',
        $r['attachment_count'] ?? '',

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
