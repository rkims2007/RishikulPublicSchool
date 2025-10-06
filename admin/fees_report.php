<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../common/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$student_id = $_GET['student_id'] ?? '';
if ($student_id === '') exit("Invalid student ID");

// Fetch student info with tuition fee from classes
$stmt = $conn->prepare("
    SELECT s.first_name, s.last_name, s.guardian_name, s.guardian_phone,
           c.tuition_fee
    FROM students s
    LEFT JOIN classes c ON s.classroom_id LIKE CONCAT(c.name, '%')
    WHERE s.id=?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) exit("Student not found");

// Fetch fees info
$fees_stmt = $conn->prepare("SELECT * FROM fees WHERE student_id=?");
$fees_stmt->bind_param("s", $student_id);
$fees_stmt->execute();
$fees = $fees_stmt->get_result()->fetch_assoc();

// Default values
$tuition_fee = floatval($student['tuition_fee'] ?? 0);
$travel_fee  = floatval($fees['travel_fee'] ?? 0);
$total_amount = floatval($fees['total_amount'] ?? ($tuition_fee + $travel_fee));
$paid_amount = floatval($fees['paid_amount'] ?? 0);
$due_amount  = floatval($fees['due_amount'] ?? max(0, $total_amount - $paid_amount));
$due_date    = $fees['due_date'] ?? '-';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $pay_date = $_POST['pay_date'] ?? date('Y-m-d');
    $pay_mode = $_POST['pay_mode'] ?? 'Cash';
    $receipt_no = 'REC-' . date('Ymd') . '-' . rand(1000, 9999);

    if ($amount > 0 && $amount <= $due_amount) {
        $paid_amount += $amount;
        $due_amount -= $amount;

        if ($fees) {
            $stmt = $conn->prepare("UPDATE fees SET paid_amount=?, due_amount=? WHERE student_id=?");
            $stmt->bind_param("dds", $paid_amount, $due_amount, $student_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO fees (student_id, tuition_fee, travel_fee, total_amount, paid_amount, due_amount) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("sddddd", $student_id, $tuition_fee, $travel_fee, $total_amount, $paid_amount, $due_amount);
            $stmt->execute();
        }

        // Insert payment record with receipt no
        $stmt2 = $conn->prepare("INSERT INTO fees_payments (student_id, amount, pay_date, pay_mode, receipt_no) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("sdsss", $student_id, $amount, $pay_date, $pay_mode, $receipt_no);
        $stmt2->execute();

        // Generate printable receipt
        echo "<script>
            let receipt = window.open('', 'Receipt', 'width=800,height=600');
            receipt.document.write('<html><head><title>Fee Receipt</title><style>body{font-family:sans-serif;padding:20px;} .table{width:100%;border-collapse:collapse;} th,td{border:1px solid #000;padding:8px;text-align:left;} th{background:#eee;}</style></head><body>');
            receipt.document.write('<div style=\"text-align:center;\"><img src=\"../images/logo.png\" height=\"80\"><h2>Rishikul Public School</h2><p>Maniyarepur Chandwak, Jaunpur, UP 222129</p><hr></div>');
            receipt.document.write('<h4>Receipt No: $receipt_no</h4>');
            receipt.document.write('<p><strong>Student:</strong> ".htmlspecialchars($student['first_name'].' '.$student['last_name'])."</p>');
            receipt.document.write('<p><strong>Guardian:</strong> ".htmlspecialchars($student['guardian_name'])."</p>');
            receipt.document.write('<p><strong>Phone:</strong> ".htmlspecialchars($student['guardian_phone'])."</p>');
            receipt.document.write('<table class=\"table\"><tr><th>Amount Paid</th><th>Date</th><th>Payment Mode</th></tr>');
            receipt.document.write('<tr><td>".number_format($amount,2)."</td><td>".$pay_date."</td><td>".$pay_mode."</td></tr></table>');
            receipt.document.write('<p>Thank you for your payment!</p>');
            receipt.document.write('</body></html>');
            receipt.document.close();
            receipt.print();
            </script>";
        exit;
    } else {
        $msg = "Invalid payment amount.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fees Report - <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body { background-color: #f2f2f2; }
.card { margin-bottom: 20px; }
.table thead th { background-color: #343a40; color: white; }
.due { color: red; font-weight: bold; }
</style>
</head>
<body>
<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Fees Report</h2>
        <div>
            <a href="dashboard.php" class="btn btn-secondary me-2">
                <i class="bi bi-house-door-fill"></i> Dashboard
            </a>
            <button class="btn btn-primary" onclick="printReport()">
                <i class="bi bi-printer-fill"></i> Print
            </button>
        </div>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-warning"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Student Info -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Student Information
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></p>
            <p><strong>Guardian:</strong> <?= htmlspecialchars($student['guardian_name']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($student['guardian_phone']) ?></p>
        </div>
    </div>

    <!-- Fees Summary -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            Fees Summary
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th>Tuition Fee</th><td><?= number_format($tuition_fee,2) ?></td></tr>
                <tr><th>Travel Fee</th><td><?= number_format($travel_fee,2) ?></td></tr>
                <tr><th>Total Amount</th><td><?= number_format($total_amount,2) ?></td></tr>
                <tr><th>Paid Amount</th><td><?= number_format($paid_amount,2) ?></td></tr>
                <tr><th>Due Amount</th><td class="due"><?= number_format($due_amount,2) ?></td></tr>
                <?php if($due_amount > 0): ?>
                    <tr><th>Due Date</th><td><?= htmlspecialchars($due_date) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if($due_amount>0): ?>
    <!-- Pay Fees -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            Pay Fees
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label>Amount to Pay</label>
                    <input type="number" name="amount" max="<?= $due_amount ?>" step="0.01" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Payment Date</label>
                    <input type="date" name="pay_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Payment Mode</label>
                    <select name="pay_mode" class="form-select" required>
                        <option value="Cash">Cash</option>
                        <option value="Online">Online</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-success">Pay Now</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function printReport() {
    let content = document.querySelector('.container').innerHTML;
    let printWindow = window.open('', 'Print', 'width=900,height=700');
    printWindow.document.write('<html><head><title>Fees Report</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('<style>body{padding:20px;} .due {color:red; font-weight:bold;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>
</body>
</html>
