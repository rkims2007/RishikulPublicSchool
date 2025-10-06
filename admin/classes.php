<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../common/config.php';

// Get class_id to edit, if any
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : '';

// Handle tuition fee update
if (isset($_POST['update_fee'])) {
    $class_id = $_POST['class_id'];
    $tuition_fee = $_POST['tuition_fee'];

    $stmt = $conn->prepare("UPDATE classes SET tuition_fee = ? WHERE id = ?");
    $stmt->bind_param("ds", $tuition_fee, $class_id);
    if ($stmt->execute()) {
        $success_msg = "Tuition fee updated successfully for the class.";
        $edit_id = ''; // reset edit mode
    } else {
        $error_msg = "Failed to update tuition fee.";
    }
    $stmt->close();
}

// Fetch all classes
$result = $conn->query("SELECT * FROM classes ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Classes & Tuition Fees</title>
<link href="../css/style.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card-hover:hover {
    transform: scale(1.05);
    transition: 0.3s;
}
</style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Classes & Tuition Fees</h2>
        <a href="dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
    </div>

    <?php if (!empty($success_msg)) : ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)) : ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>Class Name</th>
                <th>Description</th>
                <th>Tuition Fee (₹)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr class="<?= ($edit_id === $row['id']) ? 'table-warning' : '' ?>">
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <?php if ($edit_id === $row['id']): ?>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="class_id" value="<?= $row['id'] ?>">
                                <input type="number" step="0.01" min="0" name="tuition_fee" class="form-control form-control-sm" value="<?= number_format($row['tuition_fee'], 2) ?>" required>
                                <button type="submit" name="update_fee" class="btn btn-primary btn-sm">Update</button>
                                <a href="classes.php" class="btn btn-secondary btn-sm">Cancel</a>
                            </form>
                        <?php else: ?>
                            <?= number_format($row['tuition_fee'], 2) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($edit_id !== $row['id']): ?>
                            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit Fee</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Dashboard Shortcut Card -->
    <div class="row mt-5">
        <div class="col-md-4 offset-md-4">
            <div class="card card-hover text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">Back to Dashboard</h5>
                    <p class="card-text">Return to the main admin dashboard.</p>
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
