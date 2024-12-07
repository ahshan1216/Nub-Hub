<?php
include '../database.php';
session_start();
if (isset($_POST['short_name'])) {
    $shortName = $_POST['short_name'];
    $session = $_SESSION['session'];
    // Query to fetch total_team based on the selected short_name
    $query = "SELECT total_team FROM teacher_slot WHERE short_name = ? AND session = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $shortName,$session);
    $stmt->execute();
    $stmt->bind_result($totalTeam);
    $stmt->fetch();

    // Send the total_team value as a response
    echo json_encode(['total_team' => $totalTeam]);

    $stmt->close();
    $connection->close();
}
?>
