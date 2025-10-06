<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit;
}

include("../common/config.php");

$raw_classroom_id = $_GET['class'] ?? '';
$teacher_id = $_SESSION['teacher_id'] ?? '';

// Check if classroom_id is provided
if(empty($raw_classroom_id)) {
    die("<div class='alert alert-danger text-center mt-4'>Error: Class ID not provided!</div>");
}

// Extract class number from '1-A' format
if(strpos($raw_classroom_id, '-') !== false){
    list($class_number, $section) = explode('-', $raw_classroom_id);
} else {
    $class_number = $raw_classroom_id;
}
$class_number = trim($class_number);

// Find the corresponding classes.id from classes table
$stmt = $conn->prepare("SELECT id, name FROM classes WHERE name=?");
$stmt->bind_param("s", $class_number);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($valid_class_id, $valid_class_name);
$stmt->fetch();
$stmt->close();

if(empty($valid_class_id)){
    die("<div class='alert alert-danger text-center mt-4'>Error: Class not found in database!</div>");
}

// Save new subject
if(isset($_POST['save_subject'])) {
    $subject_name = $_POST['subject_name'] ?? '';

    if($subject_name) {
        $subject_id = uniqid('sub_'); // generate unique id

        $stmt = $conn->prepare("INSERT INTO subjects (id, class_id, name, teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $subject_id, $valid_class_id, $subject_name, $teacher_id);

        if($stmt->execute()) {
            echo "<div class='alert alert-success text-center mt-2'>Subject added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger text-center mt-2'>Error: ".$stmt->error."</div>";
        }
    }
}

// Fetch existing subjects for this class
$subjects = [];
$res = $conn->prepare("SELECT * FROM subjects WHERE class_id=?");
$res->bind_param("s", $valid_class_id);
$res->execute();
$result = $res->get_result();
while($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Subjects</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3 class="text-center mb-4">Manage Subjects for Class <?php echo htmlspecialchars($valid_class_name); ?> <?php echo isset($section) ? "- $section" : ""; ?></h3>

    <!-- Back to Dashboard Button -->
    <div class="mb-3 text-start">
        <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>

    <!-- Add New Subject Form -->
    <form method="post" class="mb-4 row g-3">
        <div class="col-md-8">
            <input type="text" name="subject_name" class="form-control" placeholder="Subject Name" required>
        </div>
        <div class="col-md-4">
            <button type="submit" name="save_subject" class="btn btn-success w-100">Add Subject</button>
        </div>
    </form>

    <!-- Existing Subjects Table -->
    <?php if(count($subjects) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Subject Name</th>
                    <th>Teacher ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($subjects as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['name']); ?></td>
                    <td><?php echo htmlspecialchars($s['teacher_id']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">No subjects added yet for this class.</div>
    <?php endif; ?>
</div>
</body>
</html>
