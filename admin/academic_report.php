<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../common/config.php';

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) exit("Student ID not provided.");

// Fetch student info
$stmt = $conn->prepare("SELECT first_name, last_name, class_name, section, classroom_id FROM students WHERE id=?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) exit("Student not found.");

// Fetch subjects for student's class
$stmt2 = $conn->prepare("SELECT id, name FROM subjects WHERE class_id=?");
$stmt2->bind_param("s", $student['classroom_id']);
$stmt2->execute();
$subjects = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch marks for this student
$marks_arr = [];
foreach ($subjects as $sub) {
    $stmt3 = $conn->prepare("SELECT obtained FROM marks WHERE student_id=? AND subject_id=?");
    $stmt3->bind_param("ss", $student_id, $sub['id']);
    $stmt3->execute();
    $mark_res = $stmt3->get_result()->fetch_assoc();
    $marks_arr[$sub['id']] = $mark_res['obtained'] ?? 0;
}

// Calculate total
$total_marks = array_sum($marks_arr);
$max_total = count($subjects) * 100; // assuming each subject max marks = 100
$percentage = ($max_total>0) ? round(($total_marks/$max_total)*100,2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Academic Report - <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { padding: 20px; }
@media print { .no-print { display: none; } }
</style>
</head>
<body>
<div class="container">
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
    </div>

    <h2 class="text-center mb-4">Academic Report</h2>
    <table class="table table-bordered">
        <tr>
            <th>Student Name</th>
            <td><?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></td>
            <th>Class & Section</th>
            <td><?= htmlspecialchars($student['class_name'].' '.$student['section']) ?></td>
        </tr>
    </table>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Obtained Marks</th>
                <th>Total Marks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($subjects as $i => $sub): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($sub['name']) ?></td>
                <td><?= $marks_arr[$sub['id']] ?></td>
                <td>100</td>
            </tr>
            <?php endforeach; ?>
            <tr class="table-success">
                <th colspan="2">Total</th>
                <th><?= $total_marks ?></th>
                <th><?= $max_total ?></th>
            </tr>
            <tr class="table-info">
                <th colspan="3">Percentage</th>
                <th><?= $percentage ?>%</th>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>
