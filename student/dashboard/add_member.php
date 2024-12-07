<?php
session_start();
include '../../database.php';

$leader_id = $_SESSION['student_id'];
$selected_session = isset($_SESSION['session']) ? $_SESSION['session'] : '';
$short_name=$_SESSION['short_name'];
$selected_semester = isset($_SESSION['semester']) ? $_SESSION['semester'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id']) && isset($_POST['student_name'])) {
    $students_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];

    // Check the total number of students for the leader_id and session
    $count_query = "SELECT COUNT(*) as total_students FROM student_team WHERE leader_id = ? AND session = ? AND semester= ?";
    $stmt = $connection->prepare($count_query);
    $stmt->bind_param("sss", $leader_id, $selected_session,$selected_semester);
    $stmt->execute();
    $count_result = $stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_students = $count_row['total_students'];

    // Fetch the allowed total team size from teacher_slot
    $team_limit_query = "SELECT total_team FROM teacher_slot WHERE short_name = ? AND session = ? ";
    $stmt = $connection->prepare($team_limit_query);
    $stmt->bind_param("ss", $short_name, $selected_session);
    $stmt->execute();
    $team_limit_result = $stmt->get_result();
    $team_limit_row = $team_limit_result->fetch_assoc();
    $team_limit = $team_limit_row['total_team'];

    if ($total_students >= $team_limit) {
        echo "Cannot add more members. Team limit reached!";
    } else {
        // Step 1: Check if the student_id exists in the students table
        $check_student_exist_query = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $connection->prepare($check_student_exist_query);
        $stmt->bind_param("s", $students_id);
        $stmt->execute();
        $student_exist_result = $stmt->get_result();
    
        if ($student_exist_result->num_rows === 0) {
            echo "This student ID does not exist. Please check and try again.";
        } else {
            // Step 2: Check for duplication in the student_team table
            $check_team_query = "SELECT * FROM student_team WHERE student_id = ? AND session = ?";
            $stmt = $connection->prepare($check_team_query);
            $stmt->bind_param("ss", $students_id, $selected_session);
            $stmt->execute();
            $check_team_result = $stmt->get_result();
    
            if ($check_team_result->num_rows > 0) {
                echo "Member already has a Team. Talk to him.";
            } else {



                $userIdQuery = "SELECT user_id FROM students WHERE student_id = ?";
                $userStmt = $connection->prepare($userIdQuery);
                $userStmt->bind_param("s", $students_id);
                $userStmt->execute();
                $userStmt->bind_result($user_id);
                $userStmt->fetch();
                $userStmt->close();
            
                
                    $nameQuery = "SELECT name FROM users WHERE id = ?";
                    $nameStmt = $connection->prepare($nameQuery);
                    $nameStmt->bind_param("i", $user_id);
                    $nameStmt->execute();
                    $nameStmt->bind_result($name);
                    $nameStmt->fetch();
                    $nameStmt->close();






                // Step 3: If not duplicated, proceed to insert into the student_team table
                $insert_query = "INSERT INTO student_team (student_id, student_name, leader_id, session, semester) VALUES (?, ?, ?, ?, ?)";
                $stmt = $connection->prepare($insert_query);
                $stmt->bind_param("sssss", $students_id, $name, $leader_id, $selected_session, $selected_semester);
                if ($stmt->execute()) {
                    echo "Member added successfully!";
                } else {
                    echo "Failed to add member.";
                }
            }
        }
    }
    

    $stmt->close();
    $connection->close();
}
?>
