<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $short_name = $_SESSION['short_name'];
    $session = $_POST['session'];
    $total_team = $_POST['total_team'];

    include '../database.php';

    try {
        $stmt = $connection->prepare("INSERT INTO teacher_slot (short_name, session, total_team) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $short_name, $session, $total_team);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Session created successfully']);
        } else {
            if ($connection->errno === 1062) { // Duplicate entry error
                echo json_encode(['success' => false, 'message' => 'Duplicate entry: Short name and session combination already exists']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create session']);
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    } finally {
        $connection->close();
    }
}
?>
