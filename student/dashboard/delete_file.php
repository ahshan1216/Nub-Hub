<?php
session_start();

$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : '';
$upload_dir = '../assets/pdf/';
$selected_session = isset($_SESSION['session']) ? $_SESSION['session'] : '';
$selected_semester = isset($_SESSION['semester']) ? $_SESSION['semester'] : '';
if (empty($student_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID not found.']);
    exit;
}

$file_name = $student_id .$selected_session.'s'.$selected_semester. '.pdf';
$file_path = $upload_dir . $file_name;

if (file_exists($file_path)) {
    if (unlink($file_path)) {
        echo json_encode(['status' => 'success', 'message' => 'File deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete the file.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'File not found.']);
}
?>
