<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../common/config.php';

if(!isset($_GET['id'])) exit("Teacher ID missing");
$teacher_id = $_GET['id'];

$result = $conn->query("SELECT * FROM teachers WHERE teacher_id='$teacher_id'");
if($result->num_rows==0) exit("Teacher not found");
$teacher = $result->fetch_assoc();

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $class_name = $_POST['class_name'];
    $section = $_POST['section'];
    $classroom_id = ($class_name=='NA' || $section=='NA') ? 'NA' : $class_name.'-'.$section;

    // Handle photo upload
    if(isset($_FILES['photo']) && $_FILES['photo']['error']==0){
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = $teacher_id . "." . $ext;
        $upload_path = "../images/teachers/".$photo;
        move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path);
    } else $photo = $teacher['photo'];

    $stmt = $conn->prepare("UPDATE teachers SET name=?, subject=?, email=?, phone=?, classroom_id=?, photo=? WHERE teacher_id=?");
    $stmt->bind_param("sssssss",$name,$subject,$email,$phone,$classroom_id,$photo,$teacher_id);
    if($stmt->execute()) header("Location: show_teacher.php?updated=1");
    else echo "Error: ".$stmt->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Teacher</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f7f9;}
.card{border-radius:15px;border:none;}
.header-bar{background:linear-gradient(135deg,#0d6efd,#0b5ed7);color:white;padding:15px;border-radius:12px 12px 0 0;margin:-24px -24px 20px -24px;}
.teacher-photo{max-height:100px;border-radius:8px;margin-bottom:10px;}
</style>
</head>
<body>
<div class="container mt-5">
<div class="card shadow-lg p-4">
<div class="header-bar"><h3 class="mb-0">✏️ Edit Teacher</h3></div>
<form method="post" enctype="multipart/form-data" class="mt-3">

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Full Name</label>
<input type="text" name="name" value="<?= htmlspecialchars($teacher['name']) ?>" class="form-control" required>
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Subject</label>
<input type="text" name="subject" value="<?= htmlspecialchars($teacher['subject']) ?>" class="form-control" required>
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" class="form-control">
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Phone</label>
<input type="text" name="phone" value="<?= htmlspecialchars($teacher['phone']) ?>" class="form-control">
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Class Name</label>
<select name="class_name" class="form-select" required>
<option value="NA" <?= ($teacher['classroom_id']=='NA')?'selected':'' ?>>NA</option>
<?php
$classes = ['PG','LKG','UKG'];
for($i=1;$i<=10;$i++) $classes[] = (string)$i;
foreach($classes as $c){
    $selected = (strpos($teacher['classroom_id'],$c)===0)?'selected':'';
    echo "<option value='{$c}' {$selected}>{$c}</option>";
}
?>
</select>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Section</label>
<select name="section" class="form-select" required>
<option value="NA" <?= ($teacher['classroom_id']=='NA')?'selected':'' ?>>NA</option>
<?php
$sections = ['A','B','C'];
foreach($sections as $s){
    $selected = (strpos($teacher['classroom_id'],'-'.$s)!==false)?'selected':'';
    echo "<option value='{$s}' {$selected}>{$s}</option>";
}
?>
</select>
</div>
</div>

<div class="mb-3">
<label class="form-label">Photo</label><br>
<?php if($teacher['photo'] && file_exists("../images/teachers/".$teacher['photo'])): ?>
<img src="../images/teachers/<?= $teacher['photo'] ?>" class="teacher-photo">
<?php endif; ?>
<input type="file" name="photo" class="form-control" accept="image/*">
</div>

<div class="d-flex justify-content-between">
<a href="show_teacher.php" class="btn btn-secondary">← Back</a>
<button type="submit" class="btn btn-warning">💾 Update Teacher</button>
<a href="delete_teacher.php?id=<?= $teacher['teacher_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this teacher?')">🗑️ Delete Teacher</a>
</div>

</form>
</div>
</div>
</body>
</html>
