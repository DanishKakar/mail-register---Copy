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
    $where[] = '(outgoing_letters.serial_no LIKE :q OR outgoing_letters.subject LIKE :q2 OR outgoing_letters.dossier_no LIKE :q3 OR sent_dep.name LIKE :q4 OR ref_dep.name LIKE :q5)';
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
    "SELECT outgoing_letters.*, sent_dep.name AS sent_to_department, ref_dep.name AS reference_department
     FROM outgoing_letters
     LEFT JOIN departments AS sent_dep ON sent_dep.id = outgoing_letters.sent_to_dep_id
     LEFT JOIN departments AS ref_dep ON ref_dep.id = outgoing_letters.reference_dep_id
     $whereSql
     ORDER BY outgoing_letters.id ASC"
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
        $r['sent_to_department'] ?? '',
        $r['reference_department'] ?? '',
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
