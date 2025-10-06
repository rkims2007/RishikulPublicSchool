<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit;
}
include("../common/config.php"); // include DB connection if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Dashboard - Rishikul Public School</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="icon" type="image/png" href="images/favicon.png">
</head>
<body>

<!-- Navbar -->
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
        <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#">My Classes</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Attendance</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Dashboard Content -->
<div class="container my-5">
  <div class="text-center">
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
    <p class="lead">This is your Teacher Dashboard where you can manage your classes, attendance, and student progress.</p>
  </div>

  <div class="row text-center mt-5">
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">My Classes</h5>
          <p class="card-text">View and manage your class schedules and student lists.</p>
          <a href="#" class="btn btn-success">View Classes</a>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Attendance</h5>
          <p class="card-text">Take and update student attendance records.</p>
          <a href="#" class="btn btn-success">Attendance</a>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Reports</h5>
          <p class="card-text">Check student performance and reports.</p>
          <a href="#" class="btn btn-success">View Reports</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
