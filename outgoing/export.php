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
    $where[] = '(serial_no LIKE :q OR sent_to LIKE :q2 OR subject LIKE :q3 OR reference_no LIKE :q4 OR dossier_no LIKE :q5)';
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
    "SELECT * FROM outgoing_letters $whereSql ORDER BY id ASC"
);

$stmt->execute($params);
$rows = $stmt->fetchAll();


$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('صادره مکتوبونه');


/*
    Make sheet RTL
*/
$sheet->setRightToLeft(true);


/*
    Header row
*/
$headers = [
    'مسلسل نمبر',
    'دوسیه نمبر',
    'نیټه (صدور)',
    'نیټه (مکتوب)',
    'مرسل الیه',
    'مرجع',
    'موضوع',
    'رسیداتو نمبر',
    'ثبت-امضاء',
    'ثبت-ضمیمه',
    'ثبت-د پاڼو شمېر',
    'ثبت-اصل',
    'اجرائیه-امضاء',
    'اجرائیه-ضمیمه',
    'اجرائیه-د پاڼو شمېر',
    'اجرائیه-اصل',
    'د توزیع یادداشت',
    'ملاحظات'
];

$sheet->fromArray($headers, null, 'A1');


/*
    Data rows
*/
$rowNumber = 2;

foreach ($rows as $r) {

    $sheet->fromArray([
        $r['serial_no'],
        $r['dossier_no'],
        $r['issue_date'],
        $r['letter_date'],
        $r['sent_to'],
        $r['reference_no'],
        $r['subject'],
        $r['receipts_no'],
        $r['records_signature'] ? 'هو' : 'نه',
        $r['records_attachment'] ? 'هو' : 'نه',
        $r['records_attachment_count'],
        $r['records_original'] ? 'هو' : 'نه',
        $r['exec_signature'] ? 'هو' : 'نه',
        $r['exec_attachment'] ? 'هو' : 'نه',
        $r['exec_attachment_count'],
        $r['exec_original'] ? 'هو' : 'نه',
        $r['distribution_notes'],
        $r['remarks']
    ], null, 'A' . $rowNumber);

    $rowNumber++;
}


/*
    Style
*/
$lastColumn = $sheet->getHighestColumn();
$lastRow    = $sheet->getHighestRow();

$sheet->getStyle("A1:$lastColumn$lastRow")
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
    ->setVertical(Alignment::VERTICAL_CENTER);


$sheet->getStyle("A1:".$lastColumn."1")
    ->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('D9EAF7');



$sheet->getStyle('A1:' . $lastColumn . '1')
    ->getFont()
    ->setBold(true);


/*
    Auto width
*/
foreach (range('A', $lastColumn) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}


/*
    Download XLSX
*/
$fileName = 'outgoing_letters_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');


$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;
