<?php
include '../../database.php';
require_once("cPanelApi.php");

$api = new cPanelApi("grohonn.com", "grohonnc", "Fzdhg6ZIW53#6(");

function generateRandomPassword($length = 14)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $randomPassword = '';
    $maxIndex = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[random_int(0, $maxIndex)];
    }

    return $randomPassword;
}

$active = 0;

// Debug: Log the script's start
error_log("Script execution started.");

try {
    // Check if `project_open` is 0
    $slot_query = "SELECT short_name, session FROM teacher_slot WHERE project_open = ?";
    $slot_stmt = $connection->prepare($slot_query);
    $slot_stmt->bind_param('i', $active);
    $slot_stmt->execute();
    $slot_result = $slot_stmt->get_result();

    if ($slot_result->num_rows > 0) {
        // Fetch short_name and session
        while ($slot_row = $slot_result->fetch_assoc()) {
            $short_name = $slot_row['short_name'];
            $session = $slot_row['session'];

            error_log("Processing Short Name: $short_name, Session: $session");

            // Fetch students based on session and short_name
            $fetchQuery = "SELECT leader_id FROM student_team WHERE session = ? AND short_name = ?";
            $fetchStmt = $connection->prepare($fetchQuery);
            $fetchStmt->bind_param("ss", $session, $short_name);
            $fetchStmt->execute();
            $fetch_result = $fetchStmt->get_result();

            while ($fetch_row = $fetch_result->fetch_assoc()) {
                $leader_id = $fetch_row['leader_id'];

                // Generate random password
                $randomPassword = generateRandomPassword(12);
                error_log("Generated password for leader_id $leader_id: $randomPassword");

                // Set MySQL and FTP passwords
                $api->setPasswordUserMySQL($leader_id, $randomPassword);
                $api->ftpSetPassword($leader_id, $randomPassword);

                // Update the database
                $updateQuery = "UPDATE students SET ftp_pass = ? WHERE session = ? AND short_name = ? AND student_id = ?";
                $updateStmt = $connection->prepare($updateQuery);
                $updateStmt->bind_param("ssss", $randomPassword, $session, $short_name, $leader_id);

                if ($updateStmt->execute()) {
                    error_log("FTP password updated successfully for leader_id $leader_id.");
                    echo "FTP password updated successfully for leader_id $leader_id.<br>";
                } else {
                    error_log("Error updating FTP password for leader_id $leader_id: " . $updateStmt->error);
                    echo "Error updating FTP password for leader_id $leader_id: " . $updateStmt->error . "<br>";
                }

                $updateStmt->close();
            }

            $fetchStmt->close();
        }
    } else {
        error_log("No teacher slots found with project_open = 0.");
        echo "No teacher slots found with project_open = 0.<br>";
    }

    $slot_stmt->close();
} catch (Exception $e) {
    // Log any exception that occurs
    error_log("Error: " . $e->getMessage());
    echo "An error occurred: " . $e->getMessage();
}

// Debug: Log the script's end
error_log("Script execution ended.");
?>
