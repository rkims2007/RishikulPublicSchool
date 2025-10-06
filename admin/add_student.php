<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../common/config.php';

// Function to generate Student ID
function generateStudentID($conn, $year, $class_name, $section) {
    $class_codes = ['PG'=>'PG','LKG'=>'LK','UKG'=>'UK'];
    $class_code = is_numeric($class_name) ? str_pad($class_name, 2, '0', STR_PAD_LEFT) : ($class_codes[$class_name] ?? 'XX');
    $prefix = $year . $class_code . $section;
    $like = $prefix . '%';

    $stmt = $conn->prepare("SELECT id FROM students WHERE id LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $last_id = $res->fetch_assoc()['id'] ?? null;
    $num = $last_id ? intval(substr($last_id, -3)) + 1 : 1;
    return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
}

// Function to generate Admission Number
function generateAdmissionNo($conn, $year, $class_name) {
    $class_codes = ['PG'=>'PG','LKG'=>'LK','UKG'=>'UK'];
    $class_code = is_numeric($class_name) ? str_pad($class_name, 2, '0', STR_PAD_LEFT) : ($class_codes[$class_name] ?? 'XX');
    $prefix = $year . $class_code;
    $like = $prefix . '%';

    $stmt = $conn->prepare("SELECT admission_no FROM students WHERE admission_no LIKE ? ORDER BY admission_no DESC LIMIT 1");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $last_adm = $res->fetch_assoc()['admission_no'] ?? null;
    $num = $last_adm ? intval(substr($last_adm, -3)) + 1 : 1;
    return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = date('Y');
    $class_name = $_POST['class_name'];
    $section = strtoupper($_POST['section'] ?: 'A');
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $guardian_name = trim($_POST['guardian_name']);
    $guardian_phone = trim($_POST['guardian_phone']);
    $address = trim($_POST['address']);

    // Validation
    if (!preg_match("/^[A-Z]$/", $section)) $errors[] = "Section must be a single letter (A-Z).";
    if ($dob && strtotime($dob) > time()) $errors[] = "Date of Birth cannot be in the future.";
    if ($guardian_phone && !preg_match("/^\d{10}$/", $guardian_phone)) $errors[] = "Guardian phone must be 10 digits.";
    if (empty($first_name)) $errors[] = "First name is required.";

    if (empty($errors)) {
        $student_id = generateStudentID($conn, $year, $class_name, $section);
        $admission_no = generateAdmissionNo($conn, $year, $class_name);
        $classroom_id = $class_name . '-' . $section; // 🔑 Create classroom_id for FK

        // Photo upload
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (!in_array($ext, $allowed)) {
                $errors[] = "Invalid photo format. Allowed: jpg, jpeg, png, gif.";
            } else {
                $photo = $student_id . '.' . $ext;
                $target_dir = "../images/students/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $photo)) {
                    $errors[] = "Failed to upload photo.";
                }
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO students (id, admission_no, first_name, last_name, dob, gender, classroom_id, class_name, section, guardian_name, guardian_phone, address, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssssssssssss",
                $student_id,
                $admission_no,
                $first_name,
                $last_name,
                $dob,
                $gender,
                $classroom_id, // FK
                $class_name,   // display
                $section,
                $guardian_name,
                $guardian_phone,
                $address,
                $photo
            );
            if ($stmt->execute()) {
                header('Location: show_student.php?added=1');
                exit;
            } else {
                $errors[] = "Database Error: " . $stmt->error;
            }
        }
    }

    if (!empty($errors)) $error = implode("<br>", $errors);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Student - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f4f6f9; }
.card { box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 12px; }
.card-header { background-color: #0d6efd; color: #fff; font-size: 1.5rem; font-weight: 500; }
.btn-primary { background-color: #0d6efd; border: none; }
.img-preview { max-height: 150px; margin-top: 10px; border-radius: 8px; }
</style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container my-5">
    <div class="card mx-auto" style="max-width: 850px;">
        <div class="card-header text-center">
            <i class="bi bi-person-plus-fill"></i> Add New Student
        </div>
        <div class="card-body">
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" required placeholder="Enter first name">
                    </div>
                    <div class="col-md-6">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Enter last name">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Gender</label>
                        <select name="gender" class="form-select">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Class <span class="text-danger">*</span></label>
                        <select name="class_name" class="form-select" required>
                            <option value="PG">PG</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                            <?php for($i=1;$i<=10;$i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Section <span class="text-danger">*</span></label>
                        <input type="text" name="section" class="form-control" maxlength="1" placeholder="A/B/C" required>
                    </div>
                    <div class="col-md-4">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" placeholder="Enter guardian name">
                    </div>
                    <div class="col-md-4">
                        <label>Guardian Phone</label>
                        <input type="text" name="guardian_phone" class="form-control" placeholder="10-digit phone">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                </div>

                <div class="mb-3">
                    <label>Student Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewImg(event)">
                    <img id="imgPreview" class="img-preview" src="#" alt="Image Preview" style="display:none;">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-plus-circle"></i> Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImg(event){
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('imgPreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</body>
</html>
