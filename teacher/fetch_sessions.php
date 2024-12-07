<?php
session_start();
include '../database.php';
$short_name = $_SESSION['short_name'];
$result = $connection->query("SELECT * FROM teacher_slot where short_name = '$short_name'");
$sessions = [];
while ($row = $result->fetch_assoc()) {
    $sessions[] = $row;
}
echo json_encode(['success' => true, 'sessions' => $sessions]);

$connection->close();
?>
