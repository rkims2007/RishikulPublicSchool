```php
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once '../common/config.php';

if (!isset($_GET['id'])) exit("❌ Teacher ID missing");

$teacher_id = $_GET['id'];

// Get teacher photo (if any)
$result = $conn->query("SELECT photo FROM teachers WHERE teacher_id='$teacher_id'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['photo'] && file_exists("../images/teachers/" . $row['photo'])) {
        unlink("../images/teachers/" . $row['photo']); // delete photo file
    }
}

// Delete from users table first (to maintain consistency)
$conn->query("DELETE FROM users WHERE teacher_id='$teacher_id'");

// Delete from teachers table
$conn->query("DELETE FROM teachers WHERE teacher_id='$teacher_id'");

header("Location: show_teacher.php?deleted=1");
exit;
?>
