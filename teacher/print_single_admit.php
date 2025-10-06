<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit;
}

include("../common/config.php");

// Get student ID, exam type, and year from POST
$student_id = $_POST['student_id'] ?? '';
$exam_type = $_POST['exam_type'] ?? '';
$year = $_POST['year'] ?? '2025-2026';

if (!$student_id || !$exam_type) {
    die("Student or Exam Type not specified.");
}

// Fetch student details
$stmt = $conn->prepare("SELECT first_name, last_name, class_name, section, dob, guardian_name, photo, classroom_id 
                        FROM students WHERE id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Student not found.");
}

$student = $result->fetch_assoc();
$student_name = trim($student['first_name'] . ' ' . $student['last_name']);
$photo = $student['photo'] ? "../images/students/" . $student['photo'] : "../images/default_student.png";
$class_name = htmlspecialchars($student['class_name']);
$section = htmlspecialchars($student['section']);
$dob = htmlspecialchars($student['dob']);
$guardian = htmlspecialchars($student['guardian_name']);
$classroom_id = $student['classroom_id'];

// Fetch timetable dynamically from database
$timetable_query = $conn->prepare("SELECT subject, exam_date, start_time, end_time 
                                   FROM exam_timetable 
                                   WHERE classroom_id = ? AND exam_type = ? 
                                   ORDER BY exam_date, start_time");
$timetable_query->bind_param("ss", $classroom_id, $exam_type);
$timetable_query->execute();
$timetable_result = $timetable_query->get_result();
$timetable = [];
while ($row = $timetable_result->fetch_assoc()) {
    $timetable[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admit Card - <?php echo htmlspecialchars($student_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.admit-card {
    max-width: 750px;
    margin: 40px auto;
    background: #fff;
    border: 2px solid #198754;
    border-radius: 10px;
    padding: 30px 40px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.school-header { text-align: center; border-bottom: 2px solid #198754; padding-bottom: 10px; margin-bottom: 20px; }
.school-header img { width: 70px; height: 70px; }
.photo { width: 100px; height: 120px; border: 2px solid #198754; object-fit: cover; }
.details-table th { width: 35%; }
.print-btn { text-align: center; margin-top: 20px; }
.signature-section { margin-top: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
.signature-box { text-align: center; width: 45%; }
.signature-box img { width: 100px; opacity: 0.8; }
.exam-table { margin-top: 20px; }
.exam-table th { background-color: #198754; color: #fff; }
@media print { .print-btn { display: none; } body { background: #fff; } }
</style>
</head>
<body>

<div class="admit-card">
    <div class="school-header">
        <img src="../images/logo.png" alt="School Logo">
        <h3 class="mt-2 mb-0">Rishikul Public School</h3>
        <p><strong><?php echo htmlspecialchars($exam_type); ?> - Academic Year <?php echo $year; ?></strong></p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <table class="table table-borderless details-table">
                <tr><th>Student Name:</th><td><?php echo htmlspecialchars($student_name); ?></td></tr>
                <tr><th>Student ID:</th><td><?php echo htmlspecialchars($student_id); ?></td></tr>
                <tr><th>Class:</th><td><?php echo $class_name; ?></td></tr>
                <tr><th>Section:</th><td><?php echo $section; ?></td></tr>
                <tr><th>Date of Birth:</th><td><?php echo $dob; ?></td></tr>
                <tr><th>Guardian Name:</th><td><?php echo $guardian; ?></td></tr>
            </table>
        </div>
        <div class="col-md-4 text-center">
            <img src="<?php echo $photo; ?>" alt="Student Photo" class="photo">
        </div>
    </div>

    <hr>
    <h5 class="text-success text-center mt-4 mb-3">Exam Timetable</h5>
    <?php if(count($timetable) > 0): ?>
    <table class="table table-bordered exam-table text-center">
        <thead>
            <tr><th>Date</th><th>Subject</th><th>Time</th></tr>
        </thead>
        <tbody>
            <?php foreach($timetable as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars(date("d M Y", strtotime($t['exam_date']))); ?></td>
                <td><?php echo htmlspecialchars($t['subject']); ?></td>
                <td><?php echo htmlspecialchars(date("H:i", strtotime($t['start_time'])) . " - " . date("H:i", strtotime($t['end_time']))); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-warning text-center">No timetable defined for this exam. Please add it first.</div>
    <?php endif; ?>

    <h5 class="text-success text-center mt-4 mb-3">Exam Instructions</h5>
    <ul>
        <li>Students must bring this admit card to every exam.</li>
        <li>Reach the exam hall at least 15 minutes before the start time.</li>
        <li>Use of unfair means will lead to disqualification.</li>
        <li>Mobile phones and electronic gadgets are strictly prohibited.</li>
        <li>Maintain discipline and follow all invigilator instructions.</li>
    </ul>

    <div class="signature-section">
        <div class="signature-box">
            <img src="../images/stamp.png" alt="School Stamp">
            <p class="mt-2"><strong>School Seal</strong></p>
        </div>
        <div class="signature-box">
            <img src="../images/signature.png" alt="Principal Signature">
            <p class="mt-2"><strong>Principal</strong></p>
        </div>
    </div>

    <div class="print-btn">
        <button onclick="window.print();" class="btn btn-success btn-lg mt-4">Print Admit Card</button>
    </div>
</div>

</body>
</html>
