<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../common/config.php';

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: show_student.php');
    exit;
}

$student_id = $_GET['id'];

// Fetch student to check if exists
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    header("Location: show_student.php?error=StudentNotFound");
    exit;
}

// Delete photo if exists
if ($student['photo'] && file_exists("../images/students/" . $student['photo'])) {
    unlink("../images/students/" . $student['photo']);
}

// Delete student record
$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("s", $student_id);

if ($stmt->execute()) {
    header("Location: show_student.php?deleted=1");
    exit;
} else {
    header("Location: show_student.php?error=DeleteFailed");
    exit;
}
?>
