<?php
include '../database.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    if ($id) {
        $query = "DELETE FROM tutorials WHERE id = ?";
        if ($stmt = $connection->prepare($query)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            echo "Tutorial deleted successfully.";
        } else {
            echo "Error: Could not prepare query.";
        }
    }
}
?>
