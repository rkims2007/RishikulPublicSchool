<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit;
}

include("../common/config.php");

// Get admission number from URL
$admission_no = $_GET['admission_no'] ?? '';
if (!$admission_no) {
    die("Admission number not provided.");
}

// Fetch student details
$stmt = $conn->prepare("SELECT first_name, last_name, class_name, section, dob, guardian_name, photo 
                        FROM students WHERE admission_no = ?");
$stmt->bind_param("s", $admission_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No student found with this admission number.");
}

$student = $result->fetch_assoc();
$student_name = trim($student['first_name'] . ' ' . $student['last_name']);
$photo = $student['photo'] ? "../images/students/" . $student['photo'] : "../images/default_student.png";

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
    max-width: 700px;
    margin: 40px auto;
    background: #fff;
    border: 2px solid #198754;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.school-header {
    text-align: center;
    border-bottom: 2px solid #198754;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.school-header img {
    width: 70px;
    height: 70px;
}
.photo {
    width: 100px;
    height: 120px;
    border: 2px solid #198754;
    object-fit: cover;
}
.details-table th {
    width: 35%;
}
.print-btn {
    text-align: center;
    margin-top: 20px;
}
</style>
</head>
<body>

<div class="admit-card">
    <div class="school-header">
        <img src="../images/logo.png" alt="School Logo">
        <h3 class="mt-2 mb-0">Rishikul Public School</h3>
        <p><strong>Admit Card</strong> - Half Yearly Examination 2025</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <table class="table table-borderless details-table">
                <tr><th>Student Name:</th><td><?php echo htmlspecialchars($student_name); ?></td></tr>
                <tr><th>Admission No:</th><td><?php echo htmlspecialchars($admission_no); ?></td></tr>
                <tr><th>Class:</th><td><?php echo htmlspecialchars($student['class_name']); ?></td></tr>
                <tr><th>Section:</th><td><?php echo htmlspecialchars($student['section']); ?></td></tr>
                <tr><th>Date of Birth:</th><td><?php echo htmlspecialchars($student['dob']); ?></td></tr>
                <tr><th>Guardian Name:</th><td><?php echo htmlspecialchars($student['guardian_name']); ?></td></tr>
            </table>
        </div>
        <div class="col-md-4 text-center">
            <img src="<?php echo $photo; ?>" alt="Student Photo" class="photo">
        </div>
    </div>

    <hr>
    <h5 class="text-success text-center mt-4 mb-3">Exam Instructions</h5>
    <ul>
        <li>Students must bring this admit card to every exam.</li>
        <li>Reach the exam hall 15 minutes before the start time.</li>
        <li>Use of unfair means will result in disqualification.</li>
        <li>Mobile phones and electronic gadgets are strictly prohibited.</li>
    </ul>

    <div class="print-btn">
        <button onclick="window.print();" class="btn btn-success">Print Admit Card</button>
    </div>
</div>

</body>
</html>
