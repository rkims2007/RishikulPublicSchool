<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once("../common/config.php");

// Tuition fee mapping for each class
$default_tuition = [
    'PG'=>600,'LKG'=>600,'UKG'=>600,
    '1'=>650,'2'=>650,'3'=>650,
    '4'=>700,'5'=>700,'6'=>700,
    '7'=>800,'8'=>800,
    '9'=>1200,'10'=>1200
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'] ?? '';
    $tuition_fee = floatval($_POST['tuition_fee'] ?? 0);

    if ($class_name) {
        // Update tuition fee for all students in this class
        $stmt = $conn->prepare("SELECT id FROM students WHERE class_name=?");
        $stmt->bind_param("s", $class_name);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $student_id = $row['id'];
            // Check if fee record exists
            $stmt2 = $conn->prepare("SELECT id FROM fees WHERE student_id=?");
            $stmt2->bind_param("s", $student_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if ($result2->num_rows > 0) {
                $stmt3 = $conn->prepare("UPDATE fees SET tuition_fee=? WHERE student_id=?");
                $stmt3->bind_param("ds", $tuition_fee, $student_id);
                $stmt3->execute();
            } else {
                $stmt3 = $conn->prepare("INSERT INTO fees (student_id, tuition_fee, travel_fee, paid_amount) VALUES (?, ?, 0, 0)");
                $stmt3->bind_param("sd", $student_id, $tuition_fee);
                $stmt3->execute();
            }
        }
        $msg = "Tuition fee updated for class $class_name";
    }
}

// Fetch all classes
$classes = $conn->query("SELECT DISTINCT class_name FROM students ORDER BY class_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Tuition Fees - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Update Tuition Fees</h2>
    <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
    <form method="POST" class="row g-3 align-items-center mb-4">
        <div class="col-auto">
            <label for="class_name" class="col-form-label">Class</label>
        </div>
        <div class="col-auto">
            <select name="class_name" id="class_name" class="form-select" required>
                <?php while($c = $classes->fetch_assoc()): ?>
                    <option value="<?= $c['class_name'] ?>"><?= $c['class_name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-auto">
            <label for="tuition_fee" class="col-form-label">Tuition Fee</label>
        </div>
        <div class="col-auto">
            <input type="number" name="tuition_fee" id="tuition_fee" step="0.01" class="form-control" required>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Update Fee</button>
        </div>
    </form>

    <h5>Default Tuition Fees (for reference)</h5>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Class</th>
                <th>Default Tuition Fee</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($default_tuition as $cls => $fee): ?>
                <tr>
                    <td><?= htmlspecialchars($cls) ?></td>
                    <td><?= number_format($fee,2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
