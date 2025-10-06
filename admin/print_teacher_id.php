<?php
session_start();
require_once '../common/config.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// QR Code library
$qr_lib_path = '../common/phpqrcode/qrlib.php';
if(!file_exists($qr_lib_path)) die("QR Code library not found.");
require_once $qr_lib_path;

if(!isset($_GET['id'])) die("Teacher ID required");
$id = $_GET['id'];

// Fetch teacher info
$stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id=?");
$stmt->bind_param("s", $id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
if(!$teacher) die("Teacher not found");

// School info
$school_name = "Rishikul Public School";
$school_address = "Maniyarepur Chandwak Jaunpur Uttar Pradesh 222129";
$school_phone = "6387949720";
$school_logo = "../images/logo.png";

// QR code folder
$qr_dir = "../images/teachers_qr";
if(!is_dir($qr_dir)) mkdir($qr_dir, 0755, true);
if(!is_writable($qr_dir)) die("QR folder not writable");

$qr_file = $qr_dir."/{$teacher['teacher_id']}.png";
QRcode::png($teacher['teacher_id'], $qr_file, QR_ECLEVEL_L, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher ID Card</title>
<style>
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:0; background:#fff; }
.id-card { width:303px; height:200px; border-radius:10px; border:2px solid #0d6efd; padding:8px; display:flex; justify-content:space-between; align-items:center; box-sizing:border-box; }
.left { flex:2; display:flex; flex-direction:column; justify-content:space-between; }
.right { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; }
img.logo { width:30px; height:30px; margin-bottom:4px; }
img.photo { width:60px; height:60px; border-radius:50%; border:2px solid #0d6efd; object-fit:cover; margin-bottom:4px; }
h1 { font-size:14px; color:#0d6efd; margin:0; }
p { font-size:10px; margin:2px 0; line-height:1.2; }
.qr { width:60px; height:60px; object-fit:contain; margin-top:4px; }
.footer { font-size:8px; color:#555; text-align:center; margin-top:2px; }
</style>
</head>
<body>
<div class="id-card">
    <div class="left">
        <img src="<?= $school_logo ?>" class="logo" alt="Logo">
        <h1><?= $school_name ?></h1>
        <p><?= $school_address ?></p>
        <p><strong>Name:</strong> <?= htmlspecialchars($teacher['name'] ?? '') ?></p>
        <p><strong>ID:</strong> <?= htmlspecialchars($teacher['teacher_id'] ?? '') ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($teacher['subject'] ?? '') ?></p>
        <?php if(isset($teacher['designation'])): ?>
            <p><strong>Designation:</strong> <?= htmlspecialchars($teacher['designation'] ?? '') ?></p>
        <?php endif; ?>
        <p><strong>Phone:</strong> <?= htmlspecialchars($teacher['phone'] ?? '') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($teacher['email'] ?? '') ?></p>
        <div class="footer">Rishikul Public School &copy; <?= date("Y") ?></div>
    </div>
    <div class="right">
        <?php if(!empty($teacher['photo']) && file_exists("../images/teachers/".$teacher['photo'])): ?>
            <img src="../images/teachers/<?= $teacher['photo'] ?>" class="photo" alt="Teacher Photo">
        <?php endif; ?>
        <img src="<?= $qr_file ?>" class="qr" alt="QR Code">
    </div>
</div>
<script>
window.onload = function(){ window.print(); }
</script>
</body>
</html>
