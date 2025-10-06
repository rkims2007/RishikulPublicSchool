<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
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
<title>Admin Dashboard - Rishikul Public School</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="icon" type="image/png" href="images/favicon.png">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../images/logo.png" width="50" height="50" class="me-2" alt="Logo">
      Admin Dashboard
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Manage Teachers</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Manage Students</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Dashboard Content -->
<div class="container my-5">
  <div class="text-center">
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
    <p class="lead">This is your Admin Dashboard where you can manage teachers, students, and school data.</p>
  </div>

  <div class="row text-center mt-5">
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Teachers</h5>
          <p class="card-text">Add, edit, or remove teacher accounts.</p>
          <a href="#" class="btn btn-primary">Manage Teachers</a>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Students</h5>
          <p class="card-text">View and manage student information.</p>
          <a href="#" class="btn btn-primary">Manage Students</a>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Reports</h5>
          <p class="card-text">Generate reports for performance and attendance.</p>
          <a href="#" class="btn btn-primary">View Reports</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
