<?php
include '../../database.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // Step 1: Fetch `student_id`, `semester`, `session`, and `short_name` from `students` table
    $sql = "SELECT student_id, semester, session, short_name FROM students WHERE id = ?";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for fetching student']);
        exit;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $student_id = $row['student_id'];
        $session = $row['session'];
        $semester = $row['semester'];
        $short_name = $row['short_name'];

        $stmt->close();

        // Step 2: Check if the teacher's session is open in `teacher_slot` table
        $sql2 = "SELECT open FROM teacher_slot WHERE semester = ? AND short_name = ?";
        $stmt2 = $connection->prepare($sql2);

        if (!$stmt2) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for checking teacher slot']);
            exit;
        }

        $stmt2->bind_param('ss', $semester, $short_name);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $open = null;
        if ($result2 && $result2->num_rows > 0) {
            $row2 = $result2->fetch_assoc();
            $open = $row2['open'];
        }
        $stmt2->close();

        if ($open) {
            // Step 3: Delete from `student_team` table based on `student_id`, `session`, and `semester`
            $delete_team_sql = "DELETE FROM student_team WHERE leader_id = ? AND session = ? AND semester = ?";
            $delete_team_stmt = $connection->prepare($delete_team_sql);

            if (!$delete_team_stmt) {
                echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for deleting student team']);
                exit;
            }

            $delete_team_stmt->bind_param('sss', $student_id, $session, $semester);
            $delete_team_success = $delete_team_stmt->execute();
            $delete_team_stmt->close();

            if ($delete_team_success) {
                // Step 4: Delete from `students` table
                $delete_students_sql = "DELETE FROM students WHERE id = ?";
                $delete_students_stmt = $connection->prepare($delete_students_sql);

                if (!$delete_students_stmt) {
                    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for deleting student']);
                    exit;
                }

                $delete_students_stmt->bind_param('i', $id);
                $delete_students_success = $delete_students_stmt->execute();
                $delete_students_stmt->close();

                // Step 5: Send response
                if ($delete_students_success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete from students table']);
                }
            } else {
                // Failed to delete from `student_team`
                echo json_encode(['success' => false, 'message' => 'Failed to delete from student_team table']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot remove because the teacher has already closed the session']);
        }
    } else {
        // Record not found in `students` table
        echo json_encode(['success' => false, 'message' => 'Record not found in students table']);
    }
} else {
    // Invalid or missing ID
    echo json_encode(['success' => false, 'message' => 'Invalid or missing ID']);
}
?>
