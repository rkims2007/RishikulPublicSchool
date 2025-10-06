<?php
session_start();
include("../common/config.php");

if(isset($_POST['student_id'], $_POST['term'], $_POST['year'])) {
    $student_id = $_POST['student_id'];
    $term = $_POST['term'];
    $year = $_POST['year'];

    $result = $conn->query("SELECT subject, marks FROM student_academics WHERE student_id='$student_id' AND term='$term' AND year='$year'");
    $marks = [];
    while($row = $result->fetch_assoc()) {
        $marks[$row['subject']] = $row['marks'];
    }
    echo json_encode($marks);
}
?>
