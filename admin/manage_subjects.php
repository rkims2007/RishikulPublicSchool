<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once("../common/config.php");

// Fetch all teachers
$teachers = [];
$res = $conn->query("SELECT teacher_id, name FROM teachers ORDER BY name");
if($res && $res->num_rows > 0) $teachers = $res->fetch_all(MYSQLI_ASSOC);

// Handle assigning class teacher
if(isset($_POST['assign_class_teacher'])) {
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'];
    $stmt = $conn->prepare("UPDATE classes SET class_teacher_id=? WHERE id=?");
    $stmt->bind_param("ss", $teacher_id, $class_id);
    $stmt->execute();
    header("Location: manage_subjects.php");
    exit;
}

// Handle adding subject
if(isset($_POST['add_subject'])) {
    $class_id = $_POST['class_id'];
    $subject_name = trim($_POST['subject_name']);
    $teacher_id = $_POST['teacher_id'];
    $total_marks = $_POST['total_marks'] ?: 100;

    if($class_id && $subject_name) {
        $subject_id = uniqid("SUBJ");
        $stmt = $conn->prepare("INSERT INTO subjects (id,class_id,name,teacher_id,total_marks) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssssi", $subject_id, $class_id, $subject_name, $teacher_id, $total_marks);
        $stmt->execute();
        header("Location: manage_subjects.php");
        exit;
    }
}

// Fetch all classrooms with class info and class teacher
$sql = "
SELECT 
    cr.classroom_id,
    cr.class_name AS classroom_name,
    cr.section,
    cl.id AS class_id,
    cl.name AS class_name_text,
    cl.class_teacher_id,
    t.name AS class_teacher_name
FROM classrooms cr
LEFT JOIN classes cl ON cr.class_name = cl.name
LEFT JOIN teachers t ON cl.class_teacher_id = t.teacher_id
ORDER BY cr.class_name, cr.section
";
$res = $conn->query($sql);
$classrooms = $res->fetch_all(MYSQLI_ASSOC);

// Fetch subjects grouped by class_id
$subjects = [];
$res = $conn->query("SELECT * FROM subjects ORDER BY name");
if($res && $res->num_rows>0) {
    while($row = $res->fetch_assoc()) {
        $subjects[$row['class_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Subjects - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
<h2>Classes and Subjects</h2>

<?php foreach($classrooms as $c): ?>
<div class="card mb-4">
<div class="card-header">
    <strong>Class: <?= htmlspecialchars($c['class_name_text'] ?? $c['classroom_name']) ?> | Section: <?= htmlspecialchars($c['section']) ?></strong>
</div>
<div class="card-body">
    <p>
        Class Teacher: 
        <?php if($c['class_teacher_id']): ?>
            <?= htmlspecialchars($c['class_teacher_name']) ?>
        <?php else: ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="class_id" value="<?= $c['class_id'] ?>">
                <select name="teacher_id" class="form-select d-inline w-auto" required>
                    <option value="">-- Select Teacher --</option>
                    <?php foreach($teachers as $t): ?>
                        <option value="<?= $t['teacher_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="assign_class_teacher" class="btn btn-sm btn-primary">Assign</button>
            </form>
        <?php endif; ?>
    </p>

    <h5>Subjects:</h5>
    <table class="table table-bordered">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Total Marks</th>
            <th>Teacher</th>
        </tr>
    </thead>
    <tbody>
        <?php if(isset($subjects[$c['class_id']])): ?>
            <?php foreach($subjects[$c['class_id']] as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= $s['total_marks'] ?></td>
                <td>
                    <?php
                        $sub_teacher_name = "-";
                        foreach($teachers as $t) if($t['teacher_id']==$s['teacher_id']) $sub_teacher_name=$t['name'];
                        echo htmlspecialchars($sub_teacher_name);
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">No subjects. Add below.</td></tr>
        <?php endif; ?>
    </tbody>
    </table>

    <form method="post" class="row g-2">
        <input type="hidden" name="class_id" value="<?= $c['class_id'] ?>">
        <div class="col-md-4">
            <input type="text" name="subject_name" class="form-control" placeholder="Subject Name" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="total_marks" class="form-control" placeholder="Total Marks" value="100">
        </div>
        <div class="col-md-3">
            <select name="teacher_id" class="form-select" required>
                <option value="">-- Select Subject Teacher --</option>
                <?php foreach($teachers as $t): ?>
                    <option value="<?= $t['teacher_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" name="add_subject" class="btn btn-success">Add Subject</button>
        </div>
    </form>

</div>
</div>
<?php endforeach; ?>

</div>
</body>
</html>
