<?php
session_start();
include '../database.php';

// Check if the required parameters are passed
if (isset($_POST['student_id']) && isset($_POST['active'])) {
    $student_id = $_POST['student_id'];
    $active = $_POST['active'];

    // Update the active status (verification) for the student
    $sql = "UPDATE students SET active = ? WHERE student_id = ?";
    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param('ii', $active, $student_id);  // 'ii' means integer for active status and student_id
        if ($stmt->execute()) {
            echo "Verification status updated successfully.";
        } else {
            echo "Error updating verification status.";
        }
        $stmt->close();
    } else {
        echo "Error preparing the query.";
    }
}

$connection->close();
?>
