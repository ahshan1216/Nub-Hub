<?php
session_start();

// Get JSON input from the request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['sessionValue'])) {
    // Save the input value to a session variable
    $_SESSION['session_value'] = $data['sessionValue'];

    // Respond with a success message
    echo json_encode(['success' => true]);
} else {
    // Respond with an error if sessionValue is missing
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}
