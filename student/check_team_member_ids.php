<?php
include '../database.php';
session_start();
$session_student_id=$_SESSION['student_id'];
$_SESSION['leader_id']=$session_student_id;
// Get the IDs from the POST request and filter out empty values
$ids = array_filter($_POST['ids'] ?? [], function ($id) {
    return !empty(trim($id));
});

// If no IDs are provided, return success immediately
if (empty($ids)) {
    echo json_encode(['status' => 'success']);
    exit; // Stop further script execution
}

$duplicates = [];
$nonExistent = [];
$conflict = [];
$selectedFacultyName = $_POST['selectedFacultyName'] ?? '';
$session = $_SESSION['session'];
// Check each ID
foreach ($ids as $id) {

    if ($id === $session_student_id) {
        $conflict[] = $id; // Add to conflict array
        continue; // Skip further checks for this ID
    }

    // Check if the student exists in the students table
    $check_exist_query = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $connection->prepare($check_exist_query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $exist_result = $stmt->get_result();

    if ($exist_result->num_rows === 0) {
        $nonExistent[] = $id; // Add to non-existent array
    } else {
        // Check for duplicates in the student_team table
        $check_duplicate_query = "SELECT * FROM student_team WHERE student_id = ? AND session = ? AND short_name = ?";
        $stmt = $connection->prepare($check_duplicate_query);
        $stmt->bind_param("sss", $id,$session, $selectedFacultyName);
        $stmt->execute();
        $duplicate_result = $stmt->get_result();

        if ($duplicate_result->num_rows > 0) {
            $duplicates[] = $id; // Add to duplicates array
        }
    }
    $stmt->close();
}


// Send response based on results
if (empty($duplicates) && empty($nonExistent) && empty($conflict)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'duplicates' => $duplicates,
        'nonExistent' => $nonExistent,
        'conflict' => $conflict
    ]);
}

$connection->close();
