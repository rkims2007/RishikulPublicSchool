<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}
require_once("../common/config.php");

$classroom_id = $_GET['class'] ?? '';
if (!$classroom_id) exit("Classroom not specified.");

// Fetch students
$stmt = $conn->prepare("SELECT id, first_name, last_name, photo FROM students WHERE classroom_id=? ORDER BY first_name");
$stmt->bind_param("s", $classroom_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

// Fetch attendance summary
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
    $res = $stmt2->get_result();
    $attendance_summary[$student_id] = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Report - <?= htmlspecialchars($classroom_id) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.img-thumb { max-height: 50px; border-radius: 50%; }
</style>
</head>
<body>
<div class="container my-5">
<h2>Attendance Report - Class <?= htmlspecialchars($classroom_id) ?></h2>
<a href="attendance.php?class=<?= urlencode($classroom_id) ?>" class="btn btn-secondary mb-3">Back to Attendance</a>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Photo</th>
<th>Name</th>
<th>Present</th>
<th>Absent</th>
<th>Leave</th>
<th>Total Days</th>
<th>Attendance %</th>
</tr>
</thead>
<tbody>
<?php foreach ($students as $i => $s): 
    $summary = $attendance_summary[$s['id']] ?? ['present'=>0,'absent'=>0,'leave_count'=>0,'total_days'=>0];
    $total_days = $summary['total_days'] ?: 1;
    $attendance_percent = round(($summary['present'] / $total_days) * 100, 2);
?>
<tr>
<td><?= $i+1 ?></td>
<td>
<?php if($s['photo'] && file_exists("../images/students/".$s['photo'])): ?>
<img src="../images/students/<?= htmlspecialchars($s['photo']) ?>" class="img-thumb" alt="Photo">
<?php else: ?>
<img src="../images/no-image.png" class="img-thumb" alt="No Photo">
<?php endif; ?>
</td>
<td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
<td><?= $summary['present'] ?></td>
<td><?= $summary['absent'] ?></td>
<td><?= $summary['leave_count'] ?></td>
<td><?= $summary['total_days'] ?></td>
<td><?= $attendance_percent ?>%</td>
</tr>
<?php endforeach; ?>
<?php if(empty($students)) echo "<tr><td colspan='8'>No students found.</td></tr>"; ?>
</tbody>
</table>
</div>
</body>
</html>
