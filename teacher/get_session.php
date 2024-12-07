<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Database connection
    include '../database.php';

    $stmt = $connection->prepare("SELECT * FROM teacher_slot WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $session = $result->fetch_assoc();
        echo json_encode(['success' => true, 'session' => $session]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Session not found']);
    }

    $stmt->close();
    $connection->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No session ID provided']);
}
?>
