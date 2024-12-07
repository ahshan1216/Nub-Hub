<?php
session_start();
include '../database.php'; // Include the database connection

// Check if the session variable 'short_name' is set and notice and session are passed
if (isset($_SESSION['short_name']) && isset($_POST['notice']) && isset($_POST['session'])) {
    $short_name = $_SESSION['short_name'];
    $notice = $_POST['notice'];
    $session = $_POST['session']; // Get the session from the POST data

    // Prepare the SQL query to insert the notice into the 'notices' table
    $sql = "INSERT INTO notice (short_name, notice, session) VALUES (?, ?, ?)";

    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param('sss', $short_name, $notice, $session);
        if ($stmt->execute()) {
            echo "Notice added successfully!";
        } else {
            echo "Error inserting notice.";
        }
        $stmt->close();
    } else {
        echo "Error preparing query: " . $connection->error;
    }
} else {
    echo "Missing required data.";
}

$connection->close();
?>
