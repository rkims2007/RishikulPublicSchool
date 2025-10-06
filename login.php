<?php
session_start();
include("./common/config.php"); // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from POST
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']); // Basic MD5 hashing (for demo)

    // Check credentials
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['teacher_id'] = $row['teacher_id'];

        // Redirect based on role
        if ($row['role'] == "admin") {
            header("Location: ./admin/dashboard.php");
            exit;
        } elseif ($row['role'] == "teacher") {
            header("Location: ./teacher/dashboard.php");
            exit;
        }
    } else {
        // Invalid login
        echo "<script>alert('Invalid username or password!'); window.location.href='login.html';</script>";
    }
} else {
    // Direct access to login.php without POST
    header("Location: login.html");
    exit;
}
?>
