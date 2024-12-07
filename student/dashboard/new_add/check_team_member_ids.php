<?php
include '../../../database.php';

// Get the IDs from the POST request
$ids = $_POST['ids'] ?? [];
$value = isset($_POST['value']) ? $_POST['value'] : '';
$duplicates = [];

// Check each ID for duplicates in the database
foreach ($ids as $id) {
    $stmt = $connection->prepare("SELECT COUNT(*) FROM student_team WHERE student_id = ? AND session= ? ");
    $stmt->bind_param("ss", $id,$value);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    if ($count > 0) {
        $duplicates[] = $id;
    }
    $stmt->close();
}

// Return the response
if (!empty($duplicates)) {
    echo json_encode(['status' => 'error', 'duplicates' => $duplicates]);
} else {
    echo json_encode(['status' => 'success']);
}

$connection->close();
?>
