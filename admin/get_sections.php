<?php
require_once '../common/config.php';

if(!isset($_GET['class_name'])) {
    echo json_encode([]);
    exit;
}

$class_name = $conn->real_escape_string($_GET['class_name']);
$result = $conn->query("SELECT DISTINCT section FROM students WHERE class_name='$class_name' ORDER BY section");
$sections = [];
while($row = $result->fetch_assoc()) {
    $sections[] = $row;
}

echo json_encode($sections);
