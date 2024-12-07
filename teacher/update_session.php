<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $short_name = $_SESSION['short_name'];
    $session = $_POST['session'];
    $total_team = $_POST['total_team'];
    $open = $_POST['open'];
    $project_open = $_POST['project_open'];

    // Database connection
    include '../database.php';

    $stmt = $connection->prepare("UPDATE teacher_slot SET short_name = ?, session = ?, total_team = ?, open = ?, project_open = ? WHERE id = ?");
    $stmt->bind_param('ssisii', $short_name, $session, $total_team, $open, $project_open, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Session updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Problem Ahead']);
    }

    $stmt->close();
    $connection->close();
}
?>
