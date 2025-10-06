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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="../css/style.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card-hover:hover {
    transform: scale(1.05);
    transition: 0.3s;
}
</style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container my-5">
    <h2>Welcome, Admin</h2>
    <p class="text-muted">Use the dashboard to manage students, teachers, classes, and more.</p>

    <div class="row mt-4">
        <!-- Students Card -->
        <div class="col-md-3 mb-4">
            <div class="card card-hover text-center">
                <div class="card-body">
                    <h5 class="card-title">Students</h5>
                    <p class="card-text">Add or manage student records.</p>
                    <a href="add_student.php" class="btn btn-primary btn-sm">Add Student</a>
                    <a href="show_student.php" class="btn btn-secondary btn-sm">Manage Students</a>
                </div>
            </div>
        </div>

        <!-- Teachers Card -->
        <div class="col-md-3 mb-4">
            <div class="card card-hover text-center">
                <div class="card-body">
                    <h5 class="card-title">Teachers</h5>
                    <p class="card-text">Add or manage teacher information.</p>
                    <a href="add_teacher.php" class="btn btn-primary btn-sm">Add Teacher</a>
                    <a href="show_teacher.php" class="btn btn-secondary btn-sm">Manage Teachers</a>
                </div>
            </div>
        </div>

        <!-- Classes Card -->
        <div class="col-md-3 mb-4">
            <div class="card card-hover text-center">
                <div class="card-body">
                    <h5 class="card-title">Classes</h5>
                    <p class="card-text">View or manage classes.</p>
                    <a href="classes.php" class="btn btn-primary btn-sm">Manage Classes</a>
                </div>
            </div>
        </div>

        <!-- Manage Subjects Card -->
        <div class="col-md-3 mb-4">
            <div class="card card-hover text-center">
                <div class="card-body">
                    <h5 class="card-title">Manage Subjects</h5>
                    <p class="card-text">Add or edit subjects and assign total marks to classes.</p>
                    <a href="manage_subjects.php" class="btn btn-success btn-sm">Manage Subjects</a>
                </div>
            </div>
        </div>

        <!-- Reports Card -->
        <div class="col-md-3 mb-4">
            <div class="card card-hover text-center">
                <div class="card-body">
                    <h5 class="card-title">Reports</h5>
                    <p class="card-text">Generate reports for students or teachers.</p>
                    <a href="generate_report.php" class="btn btn-primary btn-sm">Generate Report</a>
                </div>
            </div>
        </div>

        <!-- Tuition Fees Card -->
        <div class="col-md-3 mb-4">
            <div class="card card-hover text-center">
                <div class="card-body">
                    <h5 class="card-title">Tuition Fees</h5>
                    <p class="card-text">Manage student tuition fees and payments.</p>
                    <a href="update_tution_fee.php" class="btn btn-primary btn-sm">Manage Fees</a>
                </div>
            </div>
        </div>

        <!-- Travel Card -->
        <div class="col-md-3 mb-4">
            <div class="card card-hover text-center">
                <div class="card-body">
                    <h5 class="card-title">Travel</h5>
                    <p class="card-text">Manage travel or transportation information.</p>
                    <a href="update_travel_fee.php" class="btn btn-primary btn-sm">Manage Travel</a>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
