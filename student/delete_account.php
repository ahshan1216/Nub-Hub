<?php
session_start();
include '../database.php';

// Ensure session variables exist
if (!isset($_SESSION['user_id']) || !isset($_SESSION['student_id'])) {
    echo "Invalid session. Please log in.";
    exit;
}

$user_id = $_SESSION['user_id'];
$student_id = $_SESSION['student_id'];

// Start transaction
$connection->begin_transaction();

try {
    // Delete from student_team table if exists
    $query = "DELETE FROM student_team WHERE student_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->close();

    // Delete from users table
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete from users table.");
    }
    $stmt->close();

    // Commit transaction
    $connection->commit();

    // Destroy session and redirect to login page
    session_destroy();
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    // Rollback transaction on failure
    $connection->rollback();
    error_log("Account deletion failed: " . $e->getMessage());
    echo "An error occurred while deleting your account. Please try again later.";
} finally {
    $connection->close();
}
?>
