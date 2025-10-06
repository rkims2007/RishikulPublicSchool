<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Access denied");
}

require_once '../common/config.php';

$class_name = $_GET['class_name'] ?? '';
$section = $_GET['section'] ?? '';

if($class_name === '' || $section === '') {
    exit("Invalid input");
}

// Fetch students of selected class & section
$stmt = $conn->prepare("
    SELECT id, first_name, last_name, guardian_name, guardian_phone 
    FROM students 
    WHERE class_name=? AND section=?
    ORDER BY first_name
");
$stmt->bind_param("ss", $class_name, $section);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

if(empty($students)) {
    echo "<div class='alert alert-info'>No students found in this class/section.</div>";
    exit;
}
?>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Guardian Name</th>
            <th>Phone No</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($students as $i => $s): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
            <td><?= htmlspecialchars($s['guardian_name']) ?></td>
            <td><?= htmlspecialchars($s['guardian_phone']) ?></td>
            <td>
                <a href="academic_report.php?student_id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">Academic Report</a>
                <a href="fees_report.php?student_id=<?= $s['id'] ?>" class="btn btn-success btn-sm">Fees Report</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
