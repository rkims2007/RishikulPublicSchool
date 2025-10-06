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

$teacher_id = $_SESSION['teacher_id'] ?? null;

// Fetch students
$stmt = $conn->prepare("SELECT id, first_name, last_name, photo FROM students WHERE classroom_id=? ORDER BY first_name");
$stmt->bind_param("s", $classroom_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

// Handle attendance submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    foreach ($_POST['attendance'] as $student_id => $status) {
        $stmt2 = $conn->prepare("
            INSERT INTO attendance (student_id, class_date, class_id, date, status, recorded_by)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status=?
        ");
        $stmt2->bind_param("sssssss", $student_id, $date, $classroom_id, $date, $status, $teacher_id, $status);
        $stmt2->execute();
    }
    $msg = "Attendance saved for $date.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance - <?= htmlspecialchars($classroom_id) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.img-thumb { max-height: 50px; border-radius: 50%; }
</style>
</head>
<body>
<div class="container my-5">
<h2>Attendance - Class <?= htmlspecialchars($classroom_id) ?></h2>
<?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
<form method="POST">
<div class="mb-3">
<label>Date:</label>
<input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control" required>
</div>

<table class="table table-bordered">
<thead>
<tr>
<th>#</th>
<th>Photo</th>
<th>Name</th>
<th>Present</th>
<th>Absent</th>
<th>Leave</th>
</tr>
</thead>
<tbody>
<?php foreach ($students as $i => $s): ?>
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
<td><input type="radio" name="attendance[<?= $s['id'] ?>]" value="present" required></td>
<td><input type="radio" name="attendance[<?= $s['id'] ?>]" value="absent"></td>
<td><input type="radio" name="attendance[<?= $s['id'] ?>]" value="leave"></td>
</tr>
<?php endforeach; ?>
<?php if(empty($students)) echo "<tr><td colspan='6'>No students found.</td></tr>"; ?>
</tbody>
</table>

<button class="btn btn-success">Save Attendance</button>
<a href="generate_report.php?class=<?= urlencode($classroom_id) ?>" class="btn btn-primary">Generate Report</a>
<a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</form>
</div>
</body>
</html>
