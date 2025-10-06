<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../common/config.php';

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: show_student.php');
    exit;
}

$student_id = $_GET['id'];

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $class_id = $_POST['class_id'];
    $section = $_POST['section'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];
    $address = $_POST['address'];

    // Photo upload handling
    $photo = $student['photo']; // keep old if not replaced
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $new_photo = $student_id . '.' . $ext;
        $target_dir = "../images/students/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        // delete old photo if exists
        if ($photo && file_exists($target_dir . $photo)) {
            unlink($target_dir . $photo);
        }

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $new_photo)) {
            $photo = $new_photo;
        }
    }

    // Update query
    $stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, dob=?, gender=?, class_id=?, section=?, guardian_name=?, guardian_phone=?, address=?, photo=? WHERE id=?");
    $stmt->bind_param("sssssssssss", $first_name, $last_name, $dob, $gender, $class_id, $section, $guardian_name, $guardian_phone, $address, $photo, $student_id);

    if ($stmt->execute()) {
        header("Location: show_student.php?updated=1");
        exit;
    } else {
        $error = "Error updating student: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Student - Admin</title>
<link href="../css/style.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.card { box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 12px; }
.card-header { background-color: #ffc107; color: #000; font-size: 1.5rem; font-weight: 500; border-top-left-radius: 12px; border-top-right-radius: 12px; }
.btn-warning { background-color: #ffc107; border: none; }
.table-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
</style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container my-5">
    <div class="card mx-auto" style="max-width: 800px;">
        <div class="card-header text-center">
            <i class="bi bi-pencil-square"></i> Edit Student
        </div>
        <div class="card-body">
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($student['dob']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Gender</label>
                        <select name="gender" class="form-select">
                            <option value="male" <?= $student['gender']=='male'?'selected':'' ?>>Male</option>
                            <option value="female" <?= $student['gender']=='female'?'selected':'' ?>>Female</option>
                            <option value="other" <?= $student['gender']=='other'?'selected':'' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="PG" <?= $student['class_id']=='PG'?'selected':'' ?>>PG</option>
                            <option value="LKG" <?= $student['class_id']=='LKG'?'selected':'' ?>>LKG</option>
                            <option value="UKG" <?= $student['class_id']=='UKG'?'selected':'' ?>>UKG</option>
                            <?php for($i=1;$i<=10;$i++): ?>
                                <option value="<?= $i ?>" <?= $student['class_id']==$i?'selected':'' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Section</label>
                        <input type="text" name="section" class="form-control" maxlength="1" value="<?= htmlspecialchars($student['section']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?= htmlspecialchars($student['guardian_name']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Guardian Phone</label>
                        <input type="text" name="guardian_phone" class="form-control" value="<?= htmlspecialchars($student['guardian_phone']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($student['address']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label>Current Photo</label><br>
                    <?php if($student['photo'] && file_exists("../images/students/".$student['photo'])): ?>
                        <img src="../images/students/<?= $student['photo'] ?>" class="table-img mb-2">
                    <?php else: ?>
                        <img src="../images/logo.png" class="table-img mb-2" alt="No Photo">
                    <?php endif; ?>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-warning btn-lg"><i class="bi bi-save"></i> Update Student</button>
                    <a href="show_student.php" class="btn btn-secondary btn-lg"><i class="bi bi-arrow-left"></i> Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
