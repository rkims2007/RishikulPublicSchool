<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../common/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Classrooms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2 class="mb-4">Classrooms</h2>
    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Classroom added successfully!</div>
    <?php endif; ?>
    <a href="add_classroom.php" class="btn btn-primary mb-3">Add New Classroom</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Classroom ID</th>
                <th>Class</th>
                <th>Section</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = $conn->query("SELECT * FROM classrooms ORDER BY class_name ASC, section ASC");
            while ($row = $res->fetch_assoc()):
            ?>
            <tr>
                <td><?= htmlspecialchars($row['classroom_id']) ?></td>
                <td><?= htmlspecialchars($row['class_name']) ?></td>
                <td><?= htmlspecialchars($row['section']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
