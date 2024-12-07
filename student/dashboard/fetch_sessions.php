<?php
session_start();
include '../../database.php';
$student_id = $_SESSION['student_id'];
$sql = "SELECT id, session, semester, short_name FROM students where student_id = $student_id ";
$result = $connection->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>
