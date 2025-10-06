<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

require_once("../common/config.php");

$classroom_id = $_GET['class'] ?? '';
if (!$classroom_id) exit("Classroom not specified.");

// Fetch students in this class
$stmt = $conn->prepare("SELECT id, first_name, last_name, photo, dob, gender, guardian_name, guardian_phone 
                        FROM students WHERE classroom_id=? ORDER BY first_name");
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

// Fetch fees
$fees = [];
foreach ($students as $s) {
    $student_id = $s['id'];
    $stmt3 = $conn->prepare("SELECT total_amount, paid_amount, last_payment_date FROM fees WHERE student_id=?");
    $stmt3->bind_param("s", $student_id);
    $stmt3->execute();
    $res = $stmt3->get_result();
    $fees[$student_id] = $res->fetch_assoc() ?: ['total_amount'=>0,'paid_amount'=>0,'last_payment_date'=>null];
}

// Active tab (default: student list)
$tab = $_GET['tab'] ?? 'students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class <?= htmlspecialchars($classroom_id) ?> - Teacher Module</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.img-thumb { max-height: 50px; border-radius: 5px; }
</style>
</head>
<body>
<div class="container my-5">
<h2>Class <?= htmlspecialchars($classroom_id) ?></h2>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?= $tab=='students'?'active':'' ?>" href="?class=<?= urlencode($classroom_id) ?>&tab=students">Student List</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab=='attendance'?'active':'' ?>" href="?class=<?= urlencode($classroom_id) ?>&tab=attendance">Attendance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab=='fees'?'active':'' ?>" href="?class=<?= urlencode($classroom_id) ?>&tab=fees">Fees</a>
    </li>
</ul>

<?php if($tab=='students'): ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Photo</th>
<th>Name</th>
<th>DOB</th>
<th>Gender</th>
<th>Guardian</th>
<th>Phone</th>
</tr>
</thead>
<tbody>
<?php foreach($students as $i=>$s): ?>
<tr>
<td><?= $i+1 ?></td>
<td>
<?php if($s['photo'] && file_exists("../images/students/".$s['photo'])): ?>
<img src="../images/students/<?= htmlspecialchars($s['photo']) ?>" class="img-thumb" alt="Photo">
<?php else: ?>
-
<?php endif; ?>
</td>
<td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
<td><?= $s['dob']??'-' ?></td>
<td><?= $s['gender']??'-' ?></td>
<td><?= htmlspecialchars($s['guardian_name']??'-') ?></td>
<td><?= htmlspecialchars($s['guardian_phone']??'-') ?></td>
</tr>
<?php endforeach; ?>
<?php if(empty($students)) echo "<tr><td colspan='7'>No students found.</td></tr>"; ?>
</tbody>
</table>

<?php elseif($tab=='attendance'): ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Name</th>
<th>Present</th>
<th>Absent</th>
<th>Leave</th>
<th>Total Days</th>
<th>Attendance %</th>
</tr>
</thead>
<tbody>
<?php foreach($students as $i=>$s): 
    $summary = $attendance_summary[$s['id']] ?? ['present'=>0,'absent'=>0,'leave_count'=>0,'total_days'=>0];
    $total_days = $summary['total_days'] ?: 1;
    $attendance_percent = round(($summary['present'] / $total_days) * 100,2);
?>
<tr>
<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
<td><?= $summary['present'] ?></td>
<td><?= $summary['absent'] ?></td>
<td><?= $summary['leave_count'] ?></td>
<td><?= $summary['total_days'] ?></td>
<td><?= $attendance_percent ?>%</td>
</tr>
<?php endforeach; ?>
<?php if(empty($students)) echo "<tr><td colspan='7'>No students found.</td></tr>"; ?>
</tbody>
</table>
<a href="attendance.php?class=<?= urlencode($classroom_id) ?>" class="btn btn-primary mt-3">Take / Update Attendance</a>

<?php elseif($tab=='fees'): ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Name</th>
<th>Total Fees</th>
<th>Paid Amount</th>
<th>Due Amount</th>
<th>Last Payment Date</th>
</tr>
</thead>
<tbody>
<?php foreach($students as $i=>$s): 
    $f = $fees[$s['id']];
    $due = $f['total_amount'] - $f['paid_amount'];
?>
<tr>
<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
<td><?= number_format($f['total_amount'],2) ?></td>
<td><?= number_format($f['paid_amount'],2) ?></td>
<td><?= number_format($due,2) ?></td>
<td><?= $f['last_payment_date'] ?? '-' ?></td>
</tr>
<?php endforeach; ?>
<?php if(empty($students)) echo "<tr><td colspan='6'>No students found.</td></tr>"; ?>
</tbody>
</table>

<?php endif; ?>
</div>
</body>
</html>
