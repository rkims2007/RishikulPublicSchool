<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit;
}

include("../common/config.php");

// Fetch teacher details
$teacher_id = $_SESSION['teacher_id'];
$result = $conn->query("SELECT name, classroom_id, photo FROM teachers WHERE teacher_id='$teacher_id'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $teacher_name = $row['name'];
    $photo = $row['photo'] ? "../images/teachers/" . $row['photo'] : "../images/default_teacher.png";
    $classroom_id = $row['classroom_id'];

    if ($classroom_id != 'NA' && strpos($classroom_id, '-') !== false) {
        list($class_name, $section) = explode('-', $classroom_id);
    } else {
        $class_name = $section = 'NA';
    }
} else {
    $teacher_name = $_SESSION['username'];
    $photo = "../images/default_teacher.png";
    $class_name = $section = 'NA';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Dashboard - Rishikul Public School</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="icon" type="image/png" href="../images/favicon.png">
<style>
.teacher-card { max-width: 400px; margin: auto; }
.teacher-photo { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #28a745; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../images/logo.png" width="50" height="50" class="me-2" alt="Logo">
      Teacher Dashboard
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="my_classes.php">My Classes</a></li>
        <li class="nav-item"><a class="nav-link" href="attendance.php">Attendance</a></li>
        <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-5 text-center">
    <!-- Teacher Info Card -->
    <div class="card shadow-sm p-4 teacher-card mb-5">
        <img src="<?php echo $photo; ?>" alt="Teacher Photo" class="teacher-photo mb-3">
        <h3><?php echo htmlspecialchars($teacher_name); ?></h3>
        <p><strong>Class:</strong> <?php echo htmlspecialchars($class_name); ?> &nbsp; | &nbsp; <strong>Section:</strong> <?php echo htmlspecialchars($section); ?></p>
    </div>

    <!-- Dashboard Cards -->
    <div class="row text-center">

        <!-- My Classes -->
        <div class="col-md-4 mb-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">My Classes</h5>
              <p class="card-text">View and manage your class schedules and student lists.</p>
              <a href="my_classes.php?class=<?php echo urlencode($classroom_id); ?>" class="btn btn-success">View Classes</a>
            </div>
          </div>
        </div>

        <!-- Manage Subjects -->
        <div class="col-md-4 mb-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Manage Subjects</h5>
              <p class="card-text">Add or edit subjects and set total marks for your class.</p>
              <a href="manage_subjects.php?class=<?php echo urlencode($classroom_id); ?>" class="btn btn-success">Open</a>
            </div>
          </div>
        </div>

        <!-- Attendance -->
        <div class="col-md-4 mb-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Attendance</h5>
              <p class="card-text">Take and update student attendance records.</p>
              <a href="attendance.php?class=<?php echo urlencode($classroom_id); ?>" class="btn btn-success">Take Attendance</a>
            </div>
          </div>
        </div>

        <!-- Reports -->
        <div class="col-md-4 mb-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Reports</h5>
              <p class="card-text">Check student performance and attendance reports.</p>
              <a href="reports.php?class=<?php echo urlencode($classroom_id); ?>" class="btn btn-success">View Reports</a>
            </div>
          </div>
        </div>

        <!-- Academic Details -->
        <div class="col-md-4 mb-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Academic Details</h5>
              <p class="card-text">Fill or update student marks and other academic details.</p>
              <a href="academic_details.php?class=<?php echo urlencode($classroom_id); ?>" class="btn btn-success">Fill Details</a>
            </div>
          </div>
        </div>

        <!-- Print Admit Card -->
        <div class="col-md-4 mb-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Print Admit Card</h5>
              <p class="card-text">Select exam type, add timetable, and print admit cards for students.</p>
              <a href="print_admit_card.php?class=<?php echo urlencode($classroom_id); ?>" class="btn btn-success">Open</a>
            </div>
          </div>
        </div>
        <!-- Teacher Dashboard Card: Question Paper Generator -->
        <div class="dashboard-card">
            <a href="image_to_paper.html" target="_blank" style="text-decoration:none;color:inherit;">
                <div style="background:#0b63d6;color:#fff;padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.1);text-align:center;transition:0.3s;">
                    <div style="font-size:28px;margin-bottom:8px;">📝</div>
                    <div style="font-weight:600;font-size:16px;">Question Paper Generator</div>
                    <div style="font-size:13px;margin-top:4px;">Upload image & auto-format questions</div>
                </div>
            </a>
        </div>


    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
