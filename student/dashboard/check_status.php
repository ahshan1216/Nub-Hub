<?php
session_start();
include '../../database.php'; // Include your database connection file

$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : '';
$short_name = isset($_SESSION['short_name']) ? $_SESSION['short_name'] : '';
$selected_session = isset($_SESSION['session']) ? $_SESSION['session'] : '';


if (empty($student_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID not found.']);
    exit;
}

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'No matching record found.',
];

// Query to check if `open` is set to 1 in `teacher_slot`
$query = "SELECT `open` FROM `teacher_slot` WHERE `short_name` = ? AND `session` = ? ";
$stmt = $connection->prepare($query);
$stmt->bind_param("ss", $short_name, $selected_session);
$stmt->execute();
$result = $stmt->get_result();

$open = false; // Default value

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $open = $row['open'] ; // Set `open` based on the database value
}

// Query to check if `project` is empty in `students` table
$query_project = "SELECT `project` FROM `students` WHERE `student_id` = ? AND `session` = ?";
$stmt_project = $connection->prepare($query_project);
$stmt_project->bind_param("ss", $student_id,$selected_session);
$stmt_project->execute();
$result_project = $stmt_project->get_result();

$project_empty = false; // Default value

if ($result_project && $result_project->num_rows > 0) {
    $row_project = $result_project->fetch_assoc();
    $project_empty = empty($row_project['project']); // Check if `project` is empty
}

// Close the prepared statements
$stmt->close();
$stmt_project->close();
$connection->close();

// Prepare the final response
$response['status'] = 'success';
$response['open'] = $open;
$response['project_empty'] = $project_empty;

// Return the JSON response
echo json_encode($response);
?>
