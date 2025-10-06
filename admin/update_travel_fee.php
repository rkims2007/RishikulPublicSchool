<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once("../common/config.php");

// Enable error reporting (for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $travel_fee = floatval($_POST['travel_fee'] ?? 0);

    if ($student_id !== '') {

        // Fetch tuition fee from classes using class name extracted from classroom_id
        $stmt_class = $conn->prepare("
            SELECT c.tuition_fee, f.id AS fee_id, f.paid_amount
            FROM students s
            LEFT JOIN classes c ON c.name = SUBSTRING_INDEX(s.classroom_id, '-', 1)
            LEFT JOIN fees f ON s.id = f.student_id
            WHERE s.id = ?
        ");
        $stmt_class->bind_param("s", $student_id);
        $stmt_class->execute();
        $res_class = $stmt_class->get_result();

        $tuition_fee = 0;
        $paid_amount = 0;
        $fee_id = null;

        if($row = $res_class->fetch_assoc()) {
            $tuition_fee = floatval($row['tuition_fee'] ?? 0);
            $paid_amount = floatval($row['paid_amount'] ?? 0);
            $fee_id = $row['fee_id'] ?? null;
        }

        $total_amount = $tuition_fee + $travel_fee;
        $due_amount = $total_amount - $paid_amount;

        if($fee_id) {
            // Update existing fees record
            $stmt2 = $conn->prepare("
                UPDATE fees
                SET travel_fee=?, tuition_fee=?, total_amount=?, due_amount=?
                WHERE student_id=?
            ");
            $stmt2->bind_param("dddds", $travel_fee, $tuition_fee, $total_amount, $due_amount, $student_id);
            $stmt2->execute();
        } else {
            // Insert new fees record
            $stmt2 = $conn->prepare("
                INSERT INTO fees (student_id, tuition_fee, travel_fee, total_amount, paid_amount, due_amount)
                VALUES (?,?,?,?,?,?)
            ");
            $stmt2->bind_param("sddddd", $student_id, $tuition_fee, $travel_fee, $total_amount, $paid_amount, $due_amount);
            $stmt2->execute();
        }

        $msg = "Fees updated for student ID $student_id";
    }
}

// Fetch all students with tuition (from class name) and fees info
$stmt = $conn->prepare("
    SELECT s.id, s.first_name, s.last_name, s.classroom_id,
           c.tuition_fee, f.travel_fee, f.paid_amount
    FROM students s
    LEFT JOIN classes c ON c.name = SUBSTRING_INDEX(s.classroom_id, '-', 1)
    LEFT JOIN fees f ON s.id=f.student_id
    ORDER BY s.first_name
");
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Travel Fees - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Dashboard</a>
  </div>
</nav>

<div class="container my-5">
    <h2>Update Travel Fees</h2>
    <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Class Tuition Fee</th>
                <th>Current Travel Fee</th>
                <th>Total Fees</th>
                <th>Paid Amount</th>
                <th>Due Amount</th>
                <th>Update Travel Fee</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($students as $i => $s): ?>
            <?php 
                $tuition_fee = floatval($s['tuition_fee'] ?? 0);
                $travel_fee = floatval($s['travel_fee'] ?? 0);
                $paid = floatval($s['paid_amount'] ?? 0);
                $total_fee = $tuition_fee + $travel_fee;
                $due = $total_fee - $paid;
            ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
                <td><?= number_format($tuition_fee,2) ?></td>
                <td><?= number_format($travel_fee,2) ?></td>
                <td><?= number_format($total_fee,2) ?></td>
                <td><?= number_format($paid,2) ?></td>
                <td><?= number_format($due,2) ?></td>
                <td>
                    <form method="POST" class="d-flex">
                        <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                        <input type="number" name="travel_fee" step="0.01" min="0" class="form-control me-2" 
                               value="<?= number_format($travel_fee,2) ?>" required>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(empty($students)) echo "<tr><td colspan='8'>No students found.</td></tr>"; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
