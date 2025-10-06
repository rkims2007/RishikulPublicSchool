<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit;
}

include("../common/config.php");

$classroom_id_raw = $_GET['class'] ?? '';
$teacher_id = $_SESSION['teacher_id'] ?? '';
$exam_type = $_POST['exam_type'] ?? '';
$year = "2025-2026"; // default academic year

// Trim classroom_id to get class name only (before '-')
$class_name_only = 'NA';
if($classroom_id_raw != 'NA' && strpos($classroom_id_raw, '-') !== false){
    list($class_name_only, $section) = explode('-', $classroom_id_raw);
} else {
    $class_name_only = $classroom_id_raw;
}

// Fetch class ID from classes table
$class_id = '';
$res_class = $conn->query("SELECT id FROM classes WHERE name='". $conn->real_escape_string(trim($class_name_only)) ."' LIMIT 1");
if($res_class && $res_class->num_rows > 0){
    $row_class = $res_class->fetch_assoc();
    $class_id = $row_class['id'];
}

// Fetch subjects for this class
$subjects_list = [];
if($class_id){
    $sub_res = $conn->query("SELECT name FROM subjects WHERE class_id='$class_id'");
    if($sub_res) {
        while($sub_row = $sub_res->fetch_assoc()) {
            $subjects_list[] = $sub_row['name'];
        }
    }
}

// Save timetable
if(isset($_POST['save_timetable'])){
    $subjects = $_POST['subject'] ?? [];
    $dates = $_POST['exam_date'] ?? [];
    $start_times = $_POST['start_time'] ?? [];
    $end_times = $_POST['end_time'] ?? [];
    $exam_type_post = $_POST['exam_type'] ?? '';

    // Delete existing timetable for this class & exam type
    $conn->query("DELETE FROM exam_timetable WHERE classroom_id='$classroom_id_raw' AND exam_type='".$conn->real_escape_string($exam_type_post)."'");

    $stmt = $conn->prepare("INSERT INTO exam_timetable (classroom_id, exam_type, subject, exam_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
    foreach($subjects as $i => $subject){
        $stmt->bind_param("ssssss", $classroom_id_raw, $exam_type_post, $subjects[$i], $dates[$i], $start_times[$i], $end_times[$i]);
        $stmt->execute();
    }
    echo "<div class='alert alert-success text-center'>Timetable saved successfully!</div>";
}

// Load students
$students = [];
if($exam_type){
    $res = $conn->query("SELECT id AS student_id, first_name, last_name, class_name, section FROM students WHERE classroom_id='$classroom_id_raw'");
    while($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Print Admit Card</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
// Subjects array from PHP
let allSubjects = <?php echo json_encode($subjects_list); ?>;

// Update all dropdowns to hide already selected subjects
function updateDropdowns(){
    const selects = document.querySelectorAll('select[name="subject[]"]');
    let selected = Array.from(selects).map(s => s.value).filter(v => v !== '');
    selects.forEach(select => {
        const currentVal = select.value;
        select.innerHTML = '<option value="">-- Select Subject --</option>';
        allSubjects.forEach(sub => {
            if(!selected.includes(sub) || sub === currentVal){
                let opt = document.createElement('option');
                opt.value = sub;
                opt.text = sub;
                if(sub === currentVal) opt.selected = true;
                select.appendChild(opt);
            }
        });
    });
}

// Add new row
function addRow(){
    const table = document.getElementById('timetable-body');
    const row = table.insertRow();
    row.innerHTML = `
        <td><select name="subject[]" class="form-select" required onchange="updateDropdowns()"></select></td>
        <td><input type="date" name="exam_date[]" class="form-control" required></td>
        <td><input type="time" name="start_time[]" class="form-control" required></td>
        <td><input type="time" name="end_time[]" class="form-control" required></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); updateDropdowns();">Remove</button></td>
    `;
    updateDropdowns();
}

// Initialize on page load
window.onload = function(){
    updateDropdowns();
};
</script>
</head>
<body>
<div class="container mt-4">
    <h3 class="text-center mb-4">🎟 Print Admit Card - Academic Year <?php echo $year; ?></h3>

    <!-- Select Exam Type -->
    <form method="post" class="mb-4">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <label for="exam_type" class="form-label">Select Exam Type</label>
                <select name="exam_type" id="exam_type" class="form-select" required onchange="this.form.submit()">
                    <option value="">-- Select Exam Type --</option>
                    <option value="Unit Test - 1" <?php if($exam_type == 'Unit Test - 1') echo 'selected'; ?>>Unit Test - 1</option>
                    <option value="Unit Test - 2" <?php if($exam_type == 'Unit Test - 2') echo 'selected'; ?>>Unit Test - 2</option>
                    <option value="Half Yearly" <?php if($exam_type == 'Half Yearly') echo 'selected'; ?>>Half Yearly</option>
                    <option value="Annual Exam" <?php if($exam_type == 'Annual Exam') echo 'selected'; ?>>Annual Exam</option>
                </select>
            </div>
        </div>
    </form>

    <?php if($exam_type): ?>
    <!-- Timetable Input -->
    <form method="post" class="mb-4">
        <input type="hidden" name="exam_type" value="<?php echo $exam_type; ?>">
        <table class="table table-bordered">
            <thead>
                <tr><th>Subject</th><th>Date</th><th>Start Time</th><th>End Time</th><th>Action</th></tr>
            </thead>
            <tbody id="timetable-body">
                <tr>
                    <td><select name="subject[]" class="form-select" required onchange="updateDropdowns()"></select></td>
                    <td><input type="date" name="exam_date[]" class="form-control" required></td>
                    <td><input type="time" name="start_time[]" class="form-control" required></td>
                    <td><input type="time" name="end_time[]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); updateDropdowns();">Remove</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary mb-2" onclick="addRow()">Add Row</button>
        <button type="submit" name="save_timetable" class="btn btn-success mb-2">Save Timetable</button>
        <a href="dashboard.php" class="btn btn-primary mb-2">Back to Dashboard</a>
    </form>

    <!-- Student List -->
    <?php if(count($students) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead><tr><th>Student ID</th><th>Name</th><th>Class</th><th>Section</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach($students as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['section']); ?></td>
                    <td>
                        <form method="post" action="print_single_admit.php" target="_blank">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>">
                            <input type="hidden" name="exam_type" value="<?php echo $exam_type; ?>">
                            <input type="hidden" name="year" value="<?php echo $year; ?>">
                            <button type="submit" class="btn btn-success btn-sm">Print</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">No students found for this class.</div>
    <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
