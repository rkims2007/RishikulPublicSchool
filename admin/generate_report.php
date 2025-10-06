<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../common/config.php';

// Fetch distinct classes
$class_result = $conn->query("SELECT DISTINCT class_name FROM students ORDER BY class_name");
$classes = [];
while($row = $class_result->fetch_assoc()) {
    $classes[] = $row['class_name'];
}

// Fetch teachers
$teachers_result = $conn->query("SELECT teacher_id, name, subject FROM teachers ORDER BY name");
$teachers = $teachers_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generate Report - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.hidden { display: none; }
</style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container my-5">
    <h2>Generate Report</h2>
    <hr>

    <div class="mb-4">
        <button class="btn btn-primary me-2" id="studentBtn">Students</button>
        <button class="btn btn-secondary" id="teacherBtn">Teachers</button>
    </div>

    <!-- Student Section -->
    <div id="studentSection" class="hidden mb-4">
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <select id="classDropdown" class="form-select">
                    <option value="">Select Class</option>
                    <?php foreach($classes as $cls): ?>
                        <option value="<?= htmlspecialchars($cls) ?>"><?= htmlspecialchars($cls) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select id="sectionDropdown" class="form-select">
                    <option value="">Select Section</option>
                </select>
            </div>
            <div class="col-md-4">
                <button id="showStudentReport" class="btn btn-success">Show Report</button>
            </div>
        </div>

        <div id="studentReportTable"></div>
    </div>

    <!-- Teacher Section -->
    <div id="teacherSection" class="hidden">
        <?php if(empty($teachers)): ?>
            <div class="alert alert-info">No teachers found.</div>
        <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($teachers as $i => $t): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['subject']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Fees Modal -->
<div class="modal fade" id="feesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="feesModalContent">
      <!-- Loaded dynamically -->
    </div>
  </div>
</div>

<script>
// Toggle sections
const studentBtn = document.getElementById('studentBtn');
const teacherBtn = document.getElementById('teacherBtn');
const studentSection = document.getElementById('studentSection');
const teacherSection = document.getElementById('teacherSection');

studentBtn.addEventListener('click', () => {
    studentSection.classList.remove('hidden');
    teacherSection.classList.add('hidden');
});
teacherBtn.addEventListener('click', () => {
    teacherSection.classList.remove('hidden');
    studentSection.classList.add('hidden');
});

// Dynamic sections
const classDropdown = document.getElementById('classDropdown');
const sectionDropdown = document.getElementById('sectionDropdown');

classDropdown.addEventListener('change', () => {
    const className = classDropdown.value;
    sectionDropdown.innerHTML = "<option value=''>Loading...</option>";

    fetch('./get_sections.php?class_name=' + encodeURIComponent(className))
        .then(res => res.json())
        .then(data => {
            let options = "<option value=''>Select Section</option>";
            data.forEach(sec => {
                options += "<option value='"+sec.section+"'>"+sec.section+"</option>";
            });
            sectionDropdown.innerHTML = options;
        })
        .catch(err => console.error(err));
});

// Show Report
const showStudentReport = document.getElementById('showStudentReport');
const studentReportTable = document.getElementById('studentReportTable');

showStudentReport.addEventListener('click', () => {
    if(classDropdown.value === "" || sectionDropdown.value === "") {
        alert("Please select Class and Section");
        return;
    }

    studentReportTable.innerHTML = "<div class='alert alert-info'>Loading report...</div>";

    fetch(`./get_student_report.php?class_name=${encodeURIComponent(classDropdown.value)}&section=${encodeURIComponent(sectionDropdown.value)}`)
        .then(res => res.text())
        .then(html => {
            studentReportTable.innerHTML = html;

            // Attach dynamic button events
            attachDynamicButtons();
        })
        .catch(err => {
            console.error(err);
            studentReportTable.innerHTML = "<div class='alert alert-danger'>Error fetching report.</div>";
        });
});

// Attach events for dynamically loaded buttons
function attachDynamicButtons() {
    // Fees buttons
    document.querySelectorAll('.feesBtn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const studentId = e.target.dataset.studentId;
            fetch(`./fees_report_modal.php?student_id=${studentId}`)
                .then(res => res.text())
                .then(modalHtml => {
                    document.getElementById('feesModalContent').innerHTML = modalHtml;
                    const feesModal = new bootstrap.Modal(document.getElementById('feesModal'));
                    feesModal.show();
                });
        });
    });

    // Academic buttons
    document.querySelectorAll('.academicBtn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const studentId = e.target.dataset.studentId;
            // Open academic report in a new tab for printing
            window.open(`academic_report.php?student_id=${studentId}`, '_blank');
        });
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
