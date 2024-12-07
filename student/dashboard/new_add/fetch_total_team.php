<?php
include '../../../database.php';

if (isset($_POST['short_name'])) {
    $shortName = $_POST['short_name'];

    // Query to fetch total_team based on the selected short_name
    $query = "SELECT total_team FROM teacher_slot WHERE short_name = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $shortName);
    $stmt->execute();
    $stmt->bind_result($totalTeam);
    $stmt->fetch();

    // Send the total_team value as a response
    echo json_encode(['total_team' => $totalTeam]);

    $stmt->close();
    $connection->close();
}
?>
