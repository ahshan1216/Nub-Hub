<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $student_id = $data['student_id'];
    $leader_id = $data['leader_id'];

    include '../database.php';

    // Fetch the short_name of the leader from the students table
    $fetch_query = "SELECT short_name FROM students WHERE student_id = ?";
    $stmt = $connection->prepare($fetch_query);
    $stmt->bind_param("s", $leader_id);
    $stmt->execute();
    $stmt->bind_result($short_name);
    $stmt->fetch();
    $stmt->close();

    if (!empty($short_name)) {
        // Update the students table for the given student_id
        $update_query = "UPDATE students SET short_name = ? WHERE student_id = ?";
        $stmt = $connection->prepare($update_query);
        $stmt->bind_param("ss", $short_name, $student_id);

        if ($stmt->execute()) {
            echo "updated successfully. Login Again";
            $_SESSION['short_name'] = $short_name;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();


        // Update the student_team table for the given leader_id
$update_query = "UPDATE student_team SET short_name = ? WHERE student_id = ?";
$stmt = $connection->prepare($update_query);
$stmt->bind_param("ss", $short_name, $student_id);

if ($stmt->execute()) {
    echo "Updated successfully. Login Again";
    $_SESSION['short_name'] = $short_name;
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();



    } else {
        echo "Leader short_name not found for leader_id: $leader_id.";
    }

    $connection->close();
}
?>
