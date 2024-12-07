<?php
// Include the database connection
include '../database.php';

// Get the search term from the query string
$searchTerm = isset($_GET['search']) ? $connection->real_escape_string($_GET['search']) : '';

// Build the SQL query
$query = "SELECT student_id, project,session FROM students WHERE project IS NOT NULL AND project != ''";
if ($searchTerm != '') {
    $query .= " AND (project LIKE '%$searchTerm%' OR student_id LIKE '%$searchTerm%')";
}

$result = $connection->query($query);

// Check if there are any rows to display
if ($result->num_rows > 0) {
    // Loop through each project and display it
    while ($row = $result->fetch_assoc()) {
        $session = htmlspecialchars($row['session']);
        $student_id = htmlspecialchars($row['student_id']);
        $project_name = htmlspecialchars($row['project']);
        $link = "project_file/$student_id$session/";

        // Fetch team members for this leader
        $teamSql = "SELECT student_name,student_id FROM student_team WHERE leader_id = '$student_id'";
        $teamResult = $connection->query($teamSql);

        $teamMembers = [];
        if ($teamResult && $teamResult->num_rows > 0) {
            while ($teamRow = $teamResult->fetch_assoc()) {
                $teamMembers[] = htmlspecialchars($teamRow['student_name']) . " (ID: " . htmlspecialchars($teamRow['student_id']) . ")";
            }
        }

        // Convert the array of team members into a string
        $teamMembersList = !empty($teamMembers) ? implode(", ", $teamMembers) : "No team members";


        echo '
<div class="card">
  <a href="' . htmlspecialchars($link) . '" target="_blank">' . htmlspecialchars($project_name) . '</a>
  <p>This is a dynamically loaded project from the database.</p>
  <p><strong>Team Members:</strong><br>' . $teamMembersList . '</p>
  <span class="tag public">Public</span>
  <span class="date">Updated recently</span>
</div>
';
    }

} else {
    echo '<p style="text-align:center;">No projects match your search.</p>';
}

// Close the database connection
$connection->close();
?>