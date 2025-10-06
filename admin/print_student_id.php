<?php
session_start();
require_once '../common/config.php';
$qr_lib_path = '../common/phpqrcode/qrlib.php';
if(!file_exists($qr_lib_path)) die("QR Code library not found.");
require_once $qr_lib_path;

if(!isset($_GET['id'])) die("Student ID required");
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("s",$id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if(!$student) die("Student not found");

// School info
$school_name = "Rishikul Public School";
$school_address = "Maniyarepur Chandwak Jaunpur Uttar Pradesh 222129";
$school_phone = "6387949720";
$school_logo = "../images/logo.png";

// QR code folder
$qr_dir = "../images/students_qr";
if(!is_dir($qr_dir) || !is_writable($qr_dir)) die("QR folder missing or not writable");
$qr_file = $qr_dir."/{$student['id']}.png";
QRcode::png($student['id'],$qr_file,QR_ECLEVEL_L,5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Compact Horizontal PVC ID</title>
<style>
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:0; background:#fff; }
.print-area { width:403px; margin:15px auto; display:flex; flex-direction:column; gap:15px; }
.id-card { width:403px; height:550px; border-radius:10px; border:2px solid #0d6efd; overflow:hidden; box-sizing:border-box; display:flex; flex-direction:row; }
.front, .back { width:100%; height:100%; display:flex; flex-direction:row; }
.front .left, .back .left { flex:2; padding:8px; display:flex; flex-direction:column; justify-content:space-between; }
.front .right, .back .right { flex:1; display:flex; align-items:center; justify-content:center; padding:8px; }
img.logo { width:45px; height:45px; }
img.photo { width:80px; height:80px; border-radius:50%; border:2px solid #0d6efd; object-fit:cover; }
.header h1 { font-size:17px; color:#0d6efd; margin:0; }
.header p { font-size:11px; margin:2px 0; }
.info, .back-info { font-size:11px; margin-top:5px; line-height:1.3; }
.info p, .back-info p { margin:2px 0; }
.footer { font-size:9px; color:#555; text-align:center; margin-top:3px; }

/* Back layout: left text, right QR */
.back .info-text { font-size:11px; line-height:1.3; text-align:left; }
.back img.qr { width:80px; height:80px; object-fit:contain; }
.back .qr-text { text-align:center; font-size:9px; color:#555; margin-top:3px; }
.back img.back-logo { width:35px; height:35px; display:block; margin-bottom:4px; }
.back .school-info { font-size:11px; color:#333; margin:2px 0; }

@media print {
    body { margin:0; }
    .print-area { gap:15px; }
}
</style>
</head>
<body>
<div class="print-area">
   <!-- Front -->
    <div class="id-card front">
        <div class="left">
            <div class="header">
                <img src="<?= $school_logo ?>" class="logo" alt="Logo">
                <h1><?= $school_name ?></h1>
                <p><?= $school_address ?></p>
            </div>
            <div class="info">
                <p><strong>Student Name:</strong> <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></p>
                <p><strong>Student ID:</strong> <?= htmlspecialchars($student['id']) ?></p>
                <p><strong>Admission No:</strong> <?= htmlspecialchars($student['admission_no']) ?></p>
                <p><strong>Class-Section:</strong> <?= htmlspecialchars($student['class_id'].'-'.$student['section']) ?></p>
            </div>
            <div class="footer">Rishikul Public School &copy; <?= date("Y") ?> | www.rishikul.edu</div>
        </div>
        <div class="right" style="display:flex; flex-direction:column; justify-content:center; align-items:center;">
            <?php if(!empty($student['photo']) && file_exists("../images/students/".$student['photo'])): ?>
                <img src="../images/students/<?= $student['photo'] ?>" class="photo" alt="Student Photo" style="width:70px; height:70px; object-fit:cover;">
            <?php endif; ?>
        </div>
    </div>

    <!-- Back -->
    <div class="id-card back">
        <div class="left info-text back-info">
            <div class="header">
                <img src="<?= $school_logo ?>" class="logo" alt="Logo">
                <h1><?= $school_name ?></h1>
                <p><?= $school_address ?></p>
            </div>
            <p><strong>Guardian Name:</strong> <?= htmlspecialchars($student['guardian_name']) ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($student['guardian_phone']) ?></p>
            <p><strong>School Phone:</strong> <?= $school_phone ?></p>
            <p><strong>Student Address:</strong> <?= htmlspecialchars($student['address']) ?></p>
        </div>
        <div class="right" style="display:flex; flex-direction:column; justify-content:center; align-items:center;">
            <img src="<?= $qr_file ?>" class="qr" alt="QR Code" style="width:70px; height:70px; object-fit:contain;">
            <div class="qr-text">Scan QR for Student ID</div>
        </div>
    </div>

<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
