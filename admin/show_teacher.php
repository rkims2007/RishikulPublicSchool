<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../common/config.php';

// Handle filters
$class_filter = $_GET['class_name'] ?? '';
$section_filter = $_GET['section'] ?? '';
$subject_filter = $_GET['subject'] ?? '';

$where = [];
$params = [];
$types = '';

// Filter by class
if($class_filter && $class_filter != 'All') {
    $where[] = "classroom_id LIKE ?";
    $params[] = $class_filter . '%';
    $types .= 's';
}

// Filter by section
if($section_filter && $section_filter != 'All') {
    $where[] = "classroom_id LIKE ?";
    $params[] = '%-'.$section_filter;
    $types .= 's';
}

// Filter by subject
if($subject_filter && $subject_filter != 'All') {
    $where[] = "subject = ?";
    $params[] = $subject_filter;
    $types .= 's';
}

// Build query
$sql = "SELECT * FROM teachers";
if($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);
if($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get all unique subjects for filter dropdown
$subjects = $conn->query("SELECT DISTINCT subject FROM teachers ORDER BY subject ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teachers List</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f7f9;}
.card{border-radius:15px;border:none;}
.header-bar{background:linear-gradient(135deg,#198754,#0d6efd);color:white;padding:15px;border-radius:12px 12px 0 0;margin:-24px -24px 20px -24px;}
table img{max-height:60px;border-radius:8px;}
.btn-sm{border-radius:6px;margin-bottom:2px;}
</style>
</head>
<body>
<div class="container mt-5">
<div class="card shadow-lg p-4">
<div class="header-bar d-flex justify-content-between align-items-center">
<h3 class="mb-0">👩‍🏫 Teachers List</h3>
<a href="add_teacher.php" class="btn btn-light btn-sm fw-bold">➕ Add Teacher</a>
</div>

<!-- Filter Form -->
<form method="get" class="row g-3 mt-2 mb-3">
<div class="col-md-3">
<label>Class</label>
<select name="class_name" class="form-select">
<option value="All">All</option>
<?php
$classes = ['PG','LKG','UKG'];
for($i=1;$i<=10;$i++) $classes[] = (string)$i;
foreach($classes as $c){
    $sel = ($class_filter==$c)?'selected':'';
    echo "<option value='{$c}' {$sel}>{$c}</option>";
}
?>
</select>
</div>
<div class="col-md-3">
<label>Section</label>
<select name="section" class="form-select">
<option value="All">All</option>
<?php
$sections = ['A','B','C'];
foreach($sections as $s){
    $sel = ($section_filter==$s)?'selected':'';
    echo "<option value='{$s}' {$sel}>{$s}</option>";
}
?>
</select>
</div>
<div class="col-md-3">
<label>Subject</label>
<select name="subject" class="form-select">
<option value="All">All</option>
<?php
while($sub = $subjects->fetch_assoc()){
    $sel = ($subject_filter==$sub['subject'])?'selected':'';
    echo "<option value='{$sub['subject']}' {$sel}>{$sub['subject']}</option>";
}
?>
</select>
</div>
<div class="col-md-3 d-flex align-items-end">
<button type="submit" class="btn btn-primary w-100">Filter</button>
</div>
</form>

<!-- Teachers Table -->
<div class="table-responsive mt-3">
<table class="table table-hover align-middle">
<thead class="table-dark">
<tr>
<th>Photo</th>
<th>ID</th>
<th>Name</th>
<th>Subject</th>
<th>Email</th>
<th>Phone</th>
<th>Class</th>
<th>Section</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($t=$result->fetch_assoc()):
$class = ($t['classroom_id']=='NA')?'NA':explode('-',$t['classroom_id'])[0];
$section = ($t['classroom_id']=='NA')?'NA':explode('-',$t['classroom_id'])[1];
?>
<tr>
<td>
<?php if($t['photo'] && file_exists("../images/teachers/".$t['photo'])): ?>
<img src="../images/teachers/<?= $t['photo'] ?>" alt="Photo">
<?php else: ?>
<img src="../images/teachers/default.png" alt="No Photo">
<?php endif; ?>
</td>
<td><?= $t['teacher_id'] ?></td>
<td><?= htmlspecialchars($t['name']) ?></td>
<td><?= htmlspecialchars($t['subject']) ?></td>
<td><?= htmlspecialchars($t['email']) ?></td>
<td><?= htmlspecialchars($t['phone']) ?></td>
<td><?= $class ?></td>
<td><?= $section ?></td>
<td>
<a href="edit_teacher.php?id=<?= $t['teacher_id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
<a href="delete_teacher.php?id=<?= $t['teacher_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete this teacher?')">🗑️ Delete</a>
<a href="print_teacher_id.php?id=<?= $t['teacher_id'] ?>" target="_blank" class="btn btn-primary btn-sm">🖨️ Print ID</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
</div>
</body>
</html>
