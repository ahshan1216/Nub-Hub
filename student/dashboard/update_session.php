<?php
session_start();

// Update the session and semester values
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session']) && isset($_POST['semester'])) {
    $_SESSION['session'] = $_POST['session'];
    $_SESSION['semester'] = $_POST['semester'];
    $_SESSION['short_name'] = $_POST['short_name'];
    echo "Session and Semester updated successfully!";
} else {
    echo "Failed to update session and semester.";
}
?>
