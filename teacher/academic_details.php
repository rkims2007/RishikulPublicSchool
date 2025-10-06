<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit;
}

include("../common/config.php");

// Fetch teacher info
$teacher_id = $_SESSION['teacher_id'];
$result = $conn->query("SELECT classroom_id FROM teachers WHERE teacher_id='$teacher_id'");
$teacher_classroom = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['classroom_id'] : '';

$classroom_id = isset($_GET['class']) ? $_GET['class'] : $teacher_classroom;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $term = $_POST['term'];
    $year = $_POST['year'];

    foreach ($_POST['marks'] as $subject => $mark) {
        $subject = $conn->real_escape_string($subject);
        $mark = $conn->real_escape_string($mark);

        $check = $conn->query("SELECT id FROM student_academics WHERE student_id='$student_id' AND subject='$subject' AND term='$term' AND year='$year'");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE student_academics SET marks='$mark' WHERE student_id='$student_id' AND subject='$subject' AND term='$term' AND year='$year'");
        } else {
            $conn->query("INSERT INTO student_academics (student_id, subject, marks, term, year) VALUES ('$student_id','$subject','$mark','$term','$year')");
        }
    }
    $success = "Academic details saved successfully!";
}

// Fetch students for this class
$students = $conn->query("SELECT student_id, name FROM students WHERE classroom_id='$classroom_id' ORDER BY name ASC");

// Example subjects
$subjects = ['Math', 'Science', 'English', 'Hindi', 'Social Studies'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fill Academic Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4 text-center">Fill Academic Details</h2>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" id="academicForm">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="student_id" class="form-label">Select Student</label>
                <select class="form-select" name="student_id" id="student_id" required>
                    <option value="">-- Select Student --</option>
                    <?php while($row = $students->fetch_assoc()): ?>
                        <option value="<?php echo $row['student_id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="term" class="form-label">Term</label>
                <select class="form-select" name="term" id="term" required>
                    <option value="">-- Select Term --</option>
                    <option value="Term 1">Term 1</option>
                    <option value="Term 2">Term 2</option>
                    <option value="Term 3">Term 3</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="year" class="form-label">Academic Year</label>
                <input type="text" class="form-control" name="year" id="year" value="<?php echo date('Y'); ?>" required>
            </div>
        </div>

        <h5>Enter Marks</h5>
        <div class="row mb-3" id="marksContainer">
            <?php foreach($subjects as $subject): ?>
                <div class="col-md-4 mb-2">
                    <label class="form-label"><?php echo $subject; ?></label>
                    <input type="number" min="0" max="100" class="form-control" name="marks[<?php echo $subject; ?>]" id="subject_<?php echo $subject; ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-success">Save Academic Details</button>
    </form>
</div>

<script>
$(document).ready(function() {
    function fetchMarks() {
        var student_id = $('#student_id').val();
        var term = $('#term').val();
        var year = $('#year').val();
        if(student_id && term && year) {
            $.ajax({
                url: 'fetch_academic_marks.php',
                method: 'POST',
                data: { student_id: student_id, term: term, year: year },
                dataType: 'json',
                success: function(response) {
                    <?php foreach($subjects as $subject): ?>
                        $('#subject_<?php echo $subject; ?>').val(response['<?php echo $subject; ?>'] || '');
                    <?php endforeach; ?>
                }
            });
        }
    }

    $('#student_id, #term, #year').change(fetchMarks);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
