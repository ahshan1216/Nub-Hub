<?php
session_start();
include '../database.php';

// Get data from POST request
$leader_id = $_POST['leader_id'];
$student_name = $_POST['studentName'];
$student_id = $_POST['studentID'];


// Retrieve the short_name from the session
$short_name = $_SESSION['short_name'];

// Step 1: Check if the student ID exists in the students table
$sql_check_student = "SELECT student_id FROM students WHERE student_id = ?";
if ($check_stmt = $connection->prepare($sql_check_student)) {
    $check_stmt->bind_param('s', $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Student ID is valid

        // Step 2: Check if the student ID already exists in the student_team table
        $sql_check_team = "SELECT leader_id FROM student_team WHERE student_id = ?";
        if ($check_team_stmt = $connection->prepare($sql_check_team)) {
            $check_team_stmt->bind_param('s', $student_id);
            $check_team_stmt->execute();
            $check_team_result = $check_team_stmt->get_result();

            if ($check_team_result->num_rows > 0) {
                // Student is already part of a team
                $team_info = $check_team_result->fetch_assoc();
                $existing_leader_id = $team_info['leader_id'];
                echo "This ID is already a team member. Leader ID: {$existing_leader_id}.";
                exit;  // Stop the execution as the student is already in a team
            }
            $check_team_stmt->close();
        } else {
            echo 'Error preparing the query to check student_team.';
            exit;
        }
        

        $userIdQuery = "SELECT user_id,semester,session FROM students WHERE student_id = ?";
            $userStmt = $connection->prepare($userIdQuery);
            $userStmt->bind_param("s", $student_id);
            $userStmt->execute();
            $userStmt->bind_result($user_id,$semester,$session);
            $userStmt->fetch();
            $userStmt->close();
        
            
                $nameQuery = "SELECT name FROM users WHERE id = ?";
                $nameStmt = $connection->prepare($nameQuery);
                $nameStmt->bind_param("i", $user_id);
                $nameStmt->execute();
                $nameStmt->bind_result($name);
                $nameStmt->fetch();
                $nameStmt->close();


        // Step 3: If student is not in the student_team table, insert into the student_team
        $sql_insert = "INSERT INTO student_team (leader_id, student_id, student_name, semester,session) 
                       VALUES (?, ?, ?, ?,?)";
        if ($stmt = $connection->prepare($sql_insert)) {
            $stmt->bind_param('sssss', $leader_id, $student_id, $name, $semester,$session);
            if ($stmt->execute()) {
                // Step 4: Update the short_name in the students table for the added student
                $sql_update = "UPDATE students SET short_name = ? WHERE student_id = ?";
                if ($update_stmt = $connection->prepare($sql_update)) {
                    $update_stmt->bind_param('ss', $short_name, $student_id);
                    if ($update_stmt->execute()) {
                        echo 'Student added to the team and short_name updated successfully.';
                    } else {
                        echo 'Error updating short_name.';
                    }
                    $update_stmt->close();
                } else {
                    echo 'Error preparing the update query for short_name.';
                }
            } else {
                echo 'Error adding student to the team.';
            }
            $stmt->close();
        } else {
            echo 'Error preparing the insert query for student.';
        }
    } else {
        // Invalid student ID
        echo "Invalid student ID {$student_id}. Please check the ID and try again.<br>";
        exit;  // Stop execution if the student ID is invalid
    }
    $check_stmt->close();
} else {
    echo 'Error preparing the query to check students.';
    exit;
}

$connection->close();
?>
