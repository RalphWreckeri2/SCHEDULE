<?php
require 'vendor/autoload.php'; // para sa PhpSpreadsheet
include 'DbConnection.php';
include 'CRUD.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$UserManager = new UserManager($conn);

if (!isset($_GET['event_id'])) {
    die('Event ID missing.');
}

$event_id = $_GET['event_id'];

// Get participants
$participants = $UserManager->GetEventParticipants($event_id);

// Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Header
$sheet->setCellValue('A1', 'Name');
$sheet->setCellValue('B1', 'Email');
$sheet->setCellValue('C1', 'Phone');

// Set Data
$row = 2;
foreach ($participants as $participant) {
    $sheet->setCellValue('A' . $row, $participant['name']);
    $sheet->setCellValue('B' . $row, $participant['email']);
    $sheet->setCellValue('C' . $row, $participant['phone']);
    $row++;
}

// Output to browser as downloadable file
$filename = 'participants_list.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
