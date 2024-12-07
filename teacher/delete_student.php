<?php
session_start();
include '../database.php';

// Check if the required parameters are passed
if (isset($_POST['student_id']) && isset($_POST['leader_id'])) {
    $student_id = $_POST['student_id'];
    $leader_id = $_POST['leader_id'];

    // Start a transaction
    $connection->begin_transaction();

    try {
        // Delete the student from the student_team table
        $sql_delete = "DELETE FROM student_team WHERE student_id = ? AND leader_id = ?";
        if ($stmt_delete = $connection->prepare($sql_delete)) {
            $stmt_delete->bind_param('ii', $student_id, $leader_id);
            $stmt_delete->execute();
            $stmt_delete->close();
        } else {
            throw new Exception("Error preparing delete query.");
        }

        // Update the short_name column to NULL in the students table
        $sql_update = "UPDATE students SET short_name = NULL WHERE student_id = ?";
        if ($stmt_update = $connection->prepare($sql_update)) {
            $stmt_update->bind_param('i', $student_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            throw new Exception("Error preparing update query.");
        }

        // Commit the transaction
        $connection->commit();
        echo "Student deleted successfully and short_name updated to NULL.";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Required parameters are missing.";
}

$connection->close();
?>
