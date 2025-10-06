<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}
require_once("../common/config.php");

$classroom_id = $_GET['class'] ?? '';
if (!$classroom_id) exit("Classroom not specified.");

// Fetch students safely
$stmt = $conn->prepare("SELECT id, first_name, last_name, dob, gender, guardian_name, guardian_phone, photo 
                        FROM students 
                        WHERE classroom_id=? ORDER BY first_name");
$stmt->bind_param("s", $classroom_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student List - <?= htmlspecialchars($classroom_id) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.student-photo { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
</style>
</head>
<body>
<div class="container my-5">
<h2>Students in Class <?= htmlspecialchars($classroom_id) ?></h2>
<table class="table table-bordered table-striped mt-3">
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
<?php foreach ($students as $i => $s): ?>
<tr>
<td><?= $i+1 ?></td>
<td>
<?php if(!empty($s['photo']) && file_exists("../images/students/".$s['photo'])): ?>
    <img src="../images/students/<?= htmlspecialchars($s['photo']) ?>" alt="Photo" class="student-photo">
<?php else: ?>
    <img src="../images/default_avatar.png" alt="Photo" class="student-photo">
<?php endif; ?>
</td>
<td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
<td><?= !empty($s['dob']) ? date("d-m-Y", strtotime($s['dob'])) : '-' ?></td>
<td><?= ucfirst($s['gender'] ?: '-') ?></td>
<td><?= htmlspecialchars($s['guardian_name'] ?: '-') ?></td>
<td><?= htmlspecialchars($s['guardian_phone'] ?: '-') ?></td>
</tr>
<?php endforeach; ?>
<?php if(empty($students)) echo "<tr><td colspan='7'>No students found.</td></tr>"; ?>
</tbody>
</table>
</div>
</body>
</html>
