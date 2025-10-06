<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}
require_once("../common/config.php");
require_once("../tcpdf/tcpdf.php");

$classroom_id = $_GET['class'] ?? '';
if (!$classroom_id) exit("Classroom not specified.");

// Fetch students
$stmt = $conn->prepare("SELECT id, first_name, last_name, photo FROM students WHERE classroom_id=? ORDER BY first_name");
$stmt->bind_param("s", $classroom_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Attendance summary
$attendance_summary = [];
foreach ($students as $s) {
    $student_id = $s['id'];
    $stmt2 = $conn->prepare("
        SELECT 
            SUM(status='present') AS present,
            SUM(status='absent') AS absent,
            SUM(status='leave') AS leave_count,
            COUNT(*) AS total_days
        FROM attendance
        WHERE student_id=? AND class_id=?
    ");
    $stmt2->bind_param("ss", $student_id, $classroom_id);
    $stmt2->execute();
    $attendance_summary[$student_id] = $stmt2->get_result()->fetch_assoc();
}

// Generate PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$html = "<h2>Attendance Report - Class ".htmlspecialchars($classroom_id)."</h2>";
$html .= "<table border='1' cellpadding='4'>
<tr><th>#</th><th>Name</th><th>Present</th><th>Absent</th><th>Leave</th><th>Total Days</th><th>Attendance %</th></tr>";

foreach ($students as $i => $s) {
    $summary = $attendance_summary[$s['id']] ?? ['present'=>0,'absent'=>0,'leave_count'=>0,'total_days'=>0];
    $total_days = $summary['total_days'] ?: 1;
    $attendance_percent = round(($summary['present'] / $total_days) * 100, 2);
    $html .= "<tr>
    <td>".($i+1)."</td>
    <td>".htmlspecialchars($s['first_name'].' '.$s['last_name'])."</td>
    <td>{$summary['present']}</td>
    <td>{$summary['absent']}</td>
    <td>{$summary['leave_count']}</td>
    <td>{$summary['total_days']}</td>
    <td>{$attendance_percent}%</td>
    </tr>";
}

$html .= "</table>";
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('attendance_report.pdf', 'I');
