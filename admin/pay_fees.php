<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Access denied");
}

require_once '../common/config.php';

$student_id = $_POST['student_id'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$pay_date = $_POST['pay_date'] ?? date('Y-m-d');
$pay_mode = $_POST['pay_mode'] ?? 'Cash';

if($student_id === '' || $amount <= 0) exit("Invalid input");

// Fetch existing fees
$stmt = $conn->prepare("SELECT total_amount, paid_amount, due_amount FROM fees WHERE student_id=?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$res = $stmt->get_result();
$fees = $res->fetch_assoc();
if(!$fees) exit("Fee record not found");

// Update fees
$paid_new = floatval($fees['paid_amount']) + $amount;
$due_new = floatval($fees['total_amount']) - $paid_new;

$stmt2 = $conn->prepare("UPDATE fees SET paid_amount=?, due_amount=? WHERE student_id=?");
$stmt2->bind_param("dds", $paid_new, $due_new, $student_id);
$stmt2->execute();

// Insert into payment history table (create table fees_payments if needed)
$stmt3 = $conn->prepare("INSERT INTO fees_payments (student_id, amount, pay_date, pay_mode) VALUES (?,?,?,?)");
$stmt3->bind_param("sdss", $student_id, $amount, $pay_date, $pay_mode);
$stmt3->execute();

// Redirect to receipt
header("Location: fee_receipt.php?student_id=$student_id&payment_id=".$stmt3->insert_id);
exit;
