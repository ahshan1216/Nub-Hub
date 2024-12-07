<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    include '../database.php';

    $stmt = $connection->prepare("DELETE FROM teacher_slot WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Session deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete session']);
    }
    $stmt->close();
    $connection->close();
}
?>
