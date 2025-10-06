<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

require_once("../common/config.php");

$classroom_id = $_GET['class'] ?? '';
if (!$classroom_id) exit("Classroom not specified.");

// Fetch students with fees
$stmt = $conn->prepare("
    SELECT s.id, s.first_name, s.last_name, s.photo, f.total_amount, f.paid_amount, f.due_amount, f.last_payment_date
    FROM students s
    LEFT JOIN fees f ON s.id = f.student_id
    WHERE s.classroom_id=?
    ORDER BY s.first_name
");
$stmt->bind_param("s", $classroom_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fees - <?= htmlspecialchars($classroom_id) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.img-thumb { max-height: 50px; border-radius: 50%; }
</style>
</head>
<body>
<div class="container my-5">
<h2>Fees - Class <?= htmlspecialchars($classroom_id) ?></h2>
<a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Photo</th>
<th>Name</th>
<th>Total Fees</th>
<th>Paid</th>
<th>Due</th>
<th>Last Payment</th>
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
<td><?= number_format($s['total_amount'] ?? 0,2) ?></td>
<td><?= number_format($s['paid_amount'] ?? 0,2) ?></td>
<td><?= number_format($s['due_amount'] ?? ($s['total_amount'] ?? 0),2) ?></td>
<td><?= $s['last_payment_date'] ?? '-' ?></td>
</tr>
<?php endforeach; ?>
<?php if(empty($students)) echo "<tr><td colspan='7'>No students found.</td></tr>"; ?>
</tbody>
</table>
</div>
</body>
</html>
