<?php
session_start();
include '../database.php'; // Include the database connection

// Check if the user is logged in and has the required role
if (isset($_SESSION['role']) && ($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'teacher')) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $link = isset($_POST['link']) ? $_POST['link'] : '';

        if (!empty($title) && !empty($link)) {
            $query = "INSERT INTO tutorials (title, link) VALUES (?, ?)";
            if ($stmt = $connection->prepare($query)) {
                $stmt->bind_param("ss", $title, $link);
                $stmt->execute();
                $stmt->close();
                echo "Tutorial added successfully.";
            } else {
                echo "Error: Could not prepare query.";
            }
        } else {
            echo "Error: Title and link cannot be empty.";
        }
    }
}
?>
