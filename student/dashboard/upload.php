<?php
session_start();
include '../../database.php';

$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : '';
$short_name=$_SESSION['short_name'];


// Ensure student ID is set
if (empty($student_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID is missing.']);
    exit;
}

// Check if `open` is 1
$query = "SELECT `open` FROM `teacher_slot` WHERE `short_name` = ? ";
$stmt = $connection->prepare($query);
$stmt->bind_param("ss", $short_name,$selected_semester);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['open'] != 1) {
        echo json_encode(['status' => 'error', 'message' => 'File upload is disabled as the teacher slot is not open.']);
        exit;
    }
}

// Proceed with file upload
$upload_dir = '../assets/pdf/';
$selected_session = isset($_SESSION['session']) ? $_SESSION['session'] : '';
$file_name = $student_id .$selected_session. '.pdf';
$target_file = $upload_dir . $file_name;

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$file_type = mime_content_type($_FILES['file']['tmp_name']);
if ($file_type !== 'application/pdf') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only PDF files are allowed.']);
    exit;
}

if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
    echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully.', 'fileName' => $file_name]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload the file.']);
}

$stmt->close();
$connection->close();
?>
