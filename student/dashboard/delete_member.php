<?php
session_start();
include '../../database.php';
$login_student_id = $_SESSION['student_id'];
if (!isset($_SESSION['session'])) {
    echo "Invalid session. Please log in again.";
    exit;
}
$session_short_name=$_SESSION['short_name'];
$selected_session = $_SESSION['session'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = trim($_POST['student_id']); // Trim input for extra safety

    // Fetch leader_id based on student_id
    $query = "SELECT leader_id FROM student_team WHERE student_id = ? AND session = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $student_id, $selected_session);
    $stmt->execute();
    $stmt->bind_result($leader_id);
    $stmt->fetch();
    $stmt->close();

    if (!$leader_id) {
        echo "No leader found for the given student.";
        exit;
    }

    if ($student_id === $leader_id) {
// Check if total leaders are 1 in student_team table
$query = "SELECT COUNT(*) FROM student_team WHERE leader_id = ? AND session = ? AND short_name = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("sss", $leader_id, $selected_session,$session_short_name);
$stmt->execute();
$stmt->bind_result($leader_count);
$stmt->fetch();
$stmt->close();
error_log($leader_count);
if ($leader_count === 1) {
    // Proceed with deletion and update only if there's one leader
    // Start transaction
    $connection->begin_transaction();

    try {
        // Delete member from student_team table
        $query = "DELETE FROM student_team WHERE student_id = ? AND session = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $student_id, $selected_session);

        if (!$stmt->execute()) {
            throw new Exception("Failed to delete member from student_team.");
        }
        echo "Member deleted successfully from student_team!";

        // Update short_name column in students table
        $query = "UPDATE students SET short_name = NULL WHERE student_id = ? AND session = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $student_id, $selected_session);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update short_name in students table.");
        }
        echo "short_name updated successfully in students table!";

       
            unset($_SESSION['short_name']);
        

        // Commit transaction
        $connection->commit();
    } catch (Exception $e) {
        // Rollback transaction on failure
        $connection->rollback();
        error_log("Transaction failed: " . $e->getMessage()); // Log the error
        echo "An error occurred while deleting the leader.";
    } finally {
        $stmt->close();
    }
} else {
    echo "Leader cannot delete itself as You have Already made a team. If You remove yourself , Delete others Team then remove yourself!";
}
exit;
    }

    // Start transaction
    $connection->begin_transaction();

    try {
        // Delete member from student_team table
        $query = "DELETE FROM student_team WHERE student_id = ? AND session = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $student_id, $selected_session);

        if (!$stmt->execute()) {
            throw new Exception("Failed to delete member from student_team.");
        }
        echo "Member deleted successfully from student_team!";

        // Update short_name column in students table
        $query = "UPDATE students SET short_name = NULL WHERE student_id = ? AND session = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $student_id, $selected_session);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update short_name in students table.");
        }
        echo "short_name updated successfully in students table!";
        if ($login_student_id != $leader_id)
        {
            unset($_SESSION['short_name']);
        }
        

        // Commit transaction
        $connection->commit();
    } catch (Exception $e) {
        // Rollback transaction on failure
        $connection->rollback();
        // error_log("Transaction failed: " . $e->getMessage()); // Log the error
        // echo "An error occurred. Debug: " . $e->getMessage();
        // error_log("Debug: student_id = $student_id, session = $selected_session");

    } finally {
        $stmt->close();
    }
}

// Ensure database connection is closed
$connection->close();
?>
