<?php
session_start();
include '../database.php';

// Check if the required parameters are passed
if (isset($_POST['leader_id']) && isset($_POST['project'])) {
    $leader_id = $_POST['leader_id'];
    $project = $_POST['project'];

    // Update the project name for the group leader
    $sql = "UPDATE students SET project = ? WHERE student_id = ?";
    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param('si', $project, $leader_id);  // 'si' means string for project, integer for leader_id
        if ($stmt->execute()) {
            echo "Project updated successfully.";
        } else {
            echo "Error updating project.";
        }
        $stmt->close();
    } else {
        echo "Error preparing the query.";
    }
}

$connection->close();
?>
