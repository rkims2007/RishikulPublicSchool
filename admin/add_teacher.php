```php
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../common/config.php';

// Enable error reporting (local dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Generate Teacher ID
function generateTeacherID($conn) {
    $year = date("Y");
    $prefix = $year . "TCH";

    $sql = "SELECT teacher_id FROM teachers WHERE teacher_id LIKE '$prefix%' ORDER BY teacher_id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $lastID = $result->fetch_assoc()['teacher_id'];
        $num = intval(substr($lastID, -3)) + 1;
    } else {
        $num = 1;
    }

    return $prefix . str_pad($num, 3, "0", STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = generateTeacherID($conn);
    $name = trim($_POST['name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $class_name = $_POST['class_name'] ?? 'NA';
    $section = $_POST['section'] ?? 'NA';

    $classroom_id = ($class_name == 'NA' || $section == 'NA') ? 'NA' : $class_name . '-' . $section;

    // Handle photo upload
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = $teacher_id . "." . $ext;
        $upload_dir = "../images/teachers/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $upload_path = $upload_dir . $photo;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            die("❌ Error uploading photo. Check folder permissions.");
        }
    }

    // Insert teacher details
    $stmt = $conn->prepare("INSERT INTO teachers (teacher_id, name, subject, email, phone, classroom_id, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $teacher_id, $name, $subject, $email, $phone, $classroom_id, $photo);

    if ($stmt->execute()) {
        // Insert into users table (MD5 password)
        if (!empty($email) && !empty($phone)) {
            $hashed_password = md5($phone); // 🔑 use MD5
            $stmt2 = $conn->prepare("INSERT INTO users (username, password, role, teacher_id) VALUES (?, ?, 'teacher', ?)");
            $stmt2->bind_param("sss", $email, $hashed_password, $teacher_id);
            $stmt2->execute();
        }

        header("Location: show_teacher.php?added=1");
        exit;
    } else {
        echo "❌ Database Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Teacher | Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f7f9; }
.card { border-radius: 15px; border: none; }
.header-bar {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    color: white;
    padding: 15px;
    border-radius: 12px 12px 0 0;
    margin: -24px -24px 20px -24px;
}
.form-label { font-weight: 600; }
.btn-success, .btn-secondary { border-radius: 8px; padding: 10px 20px; font-weight: 600; }
</style>
</head>
<body>
<div class="container mt-5">
<div class="card shadow-lg p-4">
<div class="header-bar">
<h3 class="mb-0">➕ Add New Teacher</h3>
<small>Fill in the details below</small>
</div>
<form method="post" enctype="multipart/form-data" class="mt-3">
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Full Name <span class="text-danger">*</span></label>
<input type="text" name="name" class="form-control" placeholder="Enter full name" required>
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Subject <span class="text-danger">*</span></label>
<input type="text" name="subject" class="form-control" placeholder="Enter subject taught" required>
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Email (will be username) <span class="text-danger">*</span></label>
<input type="email" name="email" class="form-control" placeholder="Enter email address" required>
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Phone (will be password) <span class="text-danger">*</span></label>
<input type="text" name="phone" class="form-control" placeholder="Enter phone number" required>
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Class Name</label>
<select name="class_name" class="form-select" required>
<option value="NA">NA</option>
<?php
$classes = ['PG','LKG','UKG'];
for($i=1;$i<=10;$i++) $classes[] = (string)$i;
foreach($classes as $c){
    echo "<option value='{$c}'>{$c}</option>";
}
?>
</select>
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Section</label>
<select name="section" class="form-select" required>
<option value="NA">NA</option>
<?php
$sections = ['A','B','C'];
foreach($sections as $s){
    echo "<option value='{$s}'>{$s}</option>";
}
?>
</select>
</div>
</div>

<div class="mb-3">
<label class="form-label">Upload Photo</label>
<input type="file" name="photo" class="form-control" accept="image/*">
<small class="text-muted">Optional – JPG, PNG recommended.</small>
</div>

<div class="d-flex justify-content-between">
<a href="show_teacher.php" class="btn btn-secondary">← Back</a>
<button type="submit" class="btn btn-success">✅ Save Teacher</button>
</div>
</form>
</div>
</div>
</body>
</html>
