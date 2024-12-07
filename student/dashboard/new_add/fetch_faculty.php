<?php


// Check if it's an AJAX request for updated data
if (isset($_GET['fetch_faculty']) && $_GET['fetch_faculty'] == '1') {
    session_start();  // Ensure session is started
    include '../../../database.php';
    $value = isset($_GET['value']) ? $_GET['value'] : '';
    $semesterValue = isset($_GET['semesterValue']) ? $_GET['semesterValue'] : '';
    $add_new_session = $value;
    $query = "SELECT short_name, total_team FROM teacher_slot WHERE open = 1 AND session = '$add_new_session' AND semester ='$semesterValue'";
    $result = $connection->query($query);

    // Generate options
    $facultyOptions = "";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $facultyOptions .= "<div class='faculty-box' data-name='" . htmlspecialchars($row['short_name']) . "'>" . htmlspecialchars($row['short_name']) . "</div>";
        }
    } else {
        $facultyOptions = "<div class='faculty-box'>No active faculty available</div>";
    }
    error_log('Faculty Options: ' . $facultyOptions);
    // Return the options as JSON
    echo json_encode(['options' => $facultyOptions]);
    exit;
}

?>
