<?php
session_start();
// Include the database connection
include '../database.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['student_id'], $data['session'])) {
    $student_id = $data['student_id'];
    $session = $data['session'];
    // $semester = $data['semester'];

 

    // Perform delete operation
    $remove_query = "DELETE FROM student_team WHERE student_id = ? AND session = ?";
    $remove_stmt = $connection->prepare($remove_query);
    $remove_stmt->bind_param("ss", $student_id, $session);

    if ($remove_stmt->execute() && $remove_stmt->affected_rows > 0) {
        echo "Successfully removed from the team! Now Again Signin";
    } else {
        echo "No matching team found or error in deletion.";
    }

    $remove_stmt->close();
    $connection->close();
} else {
    echo "Invalid request. Missing parameters.";
}
?>
