<?php
session_start();

$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : '';
$leader_id = isset($_SESSION['leader_id']) ? $_SESSION['leader_id'] : '';

$upload_dir = '../assets/pdf/';
$selected_session = isset($_SESSION['session']) ? $_SESSION['session'] : '';

if (empty($student_id)) {
    echo json_encode(['exists' => false, 'message' => 'Student ID not found.']);
    exit;
}

$file_name = $leader_id .$selected_session.'.pdf';
$file_path = $upload_dir . $file_name;

if (file_exists($file_path)) {
    echo json_encode(['exists' => true, 'url' => $file_path, 'fileName' => $file_name]);
} else {
    echo json_encode(['exists' => false]);
}
?>
