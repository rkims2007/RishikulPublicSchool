<?php
session_start();
require_once '../common/config.php';

$student_id = $_GET['student_id'] ?? '';
$payment_id = $_GET['payment_id'] ?? '';

if($student_id === '' || $payment_id === '') exit("Invalid request");

// Fetch student
$stmt = $conn->prepare("SELECT first_name,last_name,guardian_name,guardian_phone FROM students WHERE id=?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Fetch payment
$stmt2 = $conn->prepare("SELECT amount,pay_date,pay_mode FROM fees_payments WHERE id=? AND student_id=?");
$stmt2->bind_param("is", $payment_id, $student_id);
$stmt2->execute();
$payment = $stmt2->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Fee Receipt</h2>
    <table class="table table-bordered">
        <tr><th>Student Name</th><td><?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></td></tr>
        <tr><th>Guardian Name</th><td><?= htmlspecialchars($student['guardian_name']) ?></td></tr>
        <tr><th>Phone No</th><td><?= htmlspecialchars($student['guardian_phone']) ?></td></tr>
        <tr><th>Amount Paid</th><td><?= number_format($payment['amount'],2) ?></td></tr>
        <tr><th>Payment Date</th><td><?= htmlspecialchars($payment['pay_date']) ?></td></tr>
        <tr><th>Payment Mode</th><td><?= htmlspecialchars($payment['pay_mode']) ?></td></tr>
    </table>
    <button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
</div>
</body>
</html>
