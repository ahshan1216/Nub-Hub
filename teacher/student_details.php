<?php
session_start();
include '../database.php';

// Array of light colors (you can add or modify this list as needed)
$colors = [
    '#fef9e4', // Light Yellow
    '#d1b3ff', // Light Purple
    '#a0e5e2', // Light Teal
    '#ececee', // Light Blue
    '#b7e1a1', // Light Green
    '#fbd5d5', // Light Red
    '#f0c9f7', // Light Lavender
    '#ffe0a3', // Light Orange
    '#fbe0b2', // Pale Peach
    '#d9f7b3', // Light Lime
    '#b3e0ff', // Light Sky Blue
    '#c1f7b0'  // Pale Green
];

// Check if session and short_name are passed in the URL
if (isset($_GET['session']) && isset($_GET['short_name'])) {
    $session = $_GET['session'];
    $short_name = $_GET['short_name'];

    // Prepare the SQL query to fetch details from both tables
    $sql = "
        SELECT 
            st.leader_id, 
            st.student_id, 
            st.student_name, 
            st.semester, 
            s.active, 
            s.project 
        FROM student_team AS st
        LEFT JOIN students AS s ON st.student_id = s.student_id
        WHERE s.session = ? 
        ORDER BY st.leader_id";  // Group by leader_id

    // Prepare and execute the query
    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param('s', $session);  // 's' for string type, session is a string
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if there are results
        if ($result->num_rows > 0) {
            $leader_groups = [];  // Array to store the groups by leader

            // Loop through the results and group students by leader
            while ($row = $result->fetch_assoc()) {
                $leader_groups[$row['leader_id']][] = $row;
            }

            // Display the leader-wise grouping
            echo "<h3>Session: $session</h3>";
            echo "<button onclick='toggleAllVerification()'>Toggle All Verification</button><br><br>";

            // Loop through each leader group
            foreach ($leader_groups as $leader_id => $students) {
                $leader = $students[0];
                $groupColor = $colors[$leader_id % count($colors)]; // This ensures that colors are reused in a cyclical manner

                // Create a box for the group, including the project info
                echo "<div class='group-box' style='border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;background-color: $groupColor;' id='group-{$leader_id}'>
                        <h4>
                            <strong>Group Project:</strong> 
                            <input type='text' id='project-{$leader_id}' value='" . htmlspecialchars($students[0]['project']) . "' 
                                   onchange='updateProject({$leader_id})' style='width: 100%;'>
                        </h4>
                        <h5>Leader: " . htmlspecialchars($leader['student_name']) . " (ID: " . htmlspecialchars($leader['student_id']) . ")</h5>";
// Add buttons for Proposal and Website
$proposalLink = "../student/assets/pdf/{$leader['student_id']}{$session}.pdf";
$websiteLink = "../project/project_file/{$leader['student_id']}{$session}/";

echo "<div style='margin-top: 10px;'>
        <a href='$proposalLink' target='_blank'>
            <button type='button'>View Proposal</button>
        </a>
        <a href='$websiteLink' target='_blank'>
            <button type='button'>Visit Website</button>
        </a>
    </div>";
                // Display all the students in the group, without repeating project info
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>
                        <tr>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Semester</th>
                            <th>Verify Student</th>
                            <th>Actions</th>
                        </tr>";

                // Loop through the students in this leader's group and display them
                foreach ($students as $student) {
                    // Displaying the student's information
                    $leader_label = ($student['leader_id'] == $student['student_id']) ? 'Leader' : '';
                    $active = $student['active'] == 1 ? 'Verified' : 'Not Verified';

                    // Displaying the student's information with a click event to open the student details modal
                    echo "<tr id='student-{$student['student_id']}' class='student-row' data-student-id='{$student['student_id']}'>
                            <td><a href='javascript:void(0)' onclick='showStudentDetails({$student['student_id']})'>" . htmlspecialchars($student['student_name']) . ($leader_label ? ' (' . $leader_label . ')' : '') . "</a></td>
                            <td>" . htmlspecialchars($student['student_id']) . "</td>
                            <td>" . htmlspecialchars($student['semester']) . "</td>
                            <td>
                                <label class='switch'>
                                    <input type='checkbox' class='verify-checkbox' id='verify-{$student['student_id']}' " . ($student['active'] == 1 ? 'checked' : '') . " 
                                           onchange='updateVerification({$student['student_id']})'>
                                    <span class='slider'></span>
                                </label>
                            </td>
                            <td>
                                <button onclick='deleteStudent({$student['student_id']}, {$leader_id})'>Delete</button>
                            </td>
                        </tr>";
                }

                echo "</table>
                      <button onclick='addStudent({$leader_id})'>Add Student</button>
                    </div><br>";
            }

        } else {
            echo "<p>No students found for this session.</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Error preparing the query: " . $connection->error . "</p>";
    }

} else {
    echo "<p>Session not selected. Please go back and select a session.</p>";
}

$connection->close();
?>

<!-- Add Student Modal -->
<div id="addStudentModal" style="display:none;">
    <div class="modal-content">
        <h4>Add Student to Group</h4>
        <form id="addStudentForm">
            <label for="studentID">Student ID:</label>
            <input type="text" id="studentID" name="studentID" required><br><br>
            <button type="submit">Add Student</button>
            <button type="button" onclick="closeAddStudentModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- Student Details Modal -->
<div id="studentDetailsModal" style="display:none;">
    <div class="modal-content">
        <h4>Student Details</h4>
        <div id="studentDetailsContent">
            <!-- Student photo and ID card will be injected here dynamically -->
        </div>
        <button type="button" onclick="closeStudentDetailsModal()">Close</button>
    </div>
</div>

<script>
// Update Project Name
function updateProject(leader_id) {
    var project_name = document.getElementById('project-' + leader_id).value;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_project.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log('Project updated successfully');
        }
    };
    xhr.send('leader_id=' + leader_id + '&project=' + encodeURIComponent(project_name));
}

// Update Student Verification
function updateVerification(student_id, active) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_verification.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log('Verification updated successfully');
        }
    };
    xhr.send('student_id=' + student_id + '&active=' + active);
}

// Toggle Verification for All Students (Global Toggle)
function toggleAllVerification() {
    var allStudents = document.getElementsByClassName('student-row');
    var verifyStatus = (allStudents[0].querySelector('.verify-checkbox').checked ? 0 : 1); // Get current verification status of the first student (0 = not verified, 1 = verified)

    // Loop through all students and update their verification status
    for (var i = 0; i < allStudents.length; i++) {
        var studentId = allStudents[i].dataset.studentId;
        var checkbox = allStudents[i].querySelector('.verify-checkbox');
        checkbox.checked = (verifyStatus === 1);

        // Update verification in the database
        updateVerification(studentId, verifyStatus);
    }
}

// Add Student
function addStudent(leader_id) {
    document.getElementById('addStudentModal').style.display = 'block';
    var leaderInput = document.createElement('input');
    leaderInput.type = 'hidden';
    leaderInput.name = 'leader_id';
    leaderInput.value = leader_id;
    document.getElementById('addStudentForm').appendChild(leaderInput);
}

// Close Add Student Modal
function closeAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'none';
}

// Show Student Details (Photo and ID Card)
function showStudentDetails(student_id) {
    var photoPath = '../student/assets/student_photo/' + student_id + '.jpg';
    var idCardPath = '../student/assets/student_idcard/' + student_id + '.jpg';
    
    var content = "<div><img src='" + photoPath + "' alt='Student Photo' style='width:150px; height:150px;'><br><strong>Photo</strong></div>";
    content += "<div><img src='" + idCardPath + "' alt='Student ID Card' style='width:150px; height:150px;'><br><strong>ID Card</strong></div>";

    document.getElementById('studentDetailsContent').innerHTML = content;
    document.getElementById('studentDetailsModal').style.display = 'block';
}

// Close Student Details Modal
function closeStudentDetailsModal() {
    document.getElementById('studentDetailsModal').style.display = 'none';
}

// Delete Student
function deleteStudent(student_id, leader_id) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_student.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('student-' + student_id).remove();
            console.log('Student deleted successfully');
        }
    };
    xhr.send('student_id=' + student_id + '&leader_id=' + leader_id);
}
</script>

<style>
/* General Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

/* Header */
h3 {
    font-size: 24px;
    color: #333;
    padding: 10px;
    background-color: #007BFF;
    color: white;
    text-align: center;
}

/* Group Box Styles */
.group-box {
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin: 15px;
    transition: background-color 0.3s ease;
}

.group-box:hover {
    background-color: #f1f8ff;
}

.group-box h4 {
    font-size: 20px;
    color: #333;
    margin-bottom: 15px;
}

.group-box h5 {
    font-size: 16px;
    color: #555;
    margin-bottom: 15px;
}

input[type="text"] {
    width: 100%;
    padding: 8px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 10px;
    transition: border 0.3s ease;
}

input[type="text"]:focus {
    border-color: #007BFF;
}

/* Button Styling */
button {
    background-color: #007BFF;
    color: white;
    font-size: 14px;
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    margin-right: 10px;
}

button:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    padding: 12px;
    text-align: left;
    font-size: 14px;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: #f2f2f2;
    color: #333;
}

table td a {
    color: #007BFF;
    text-decoration: none;
}

table td a:hover {
    text-decoration: underline;
}

/* Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 22px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 50px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    border-radius: 50px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:checked + .slider:before {
    transform: translateX(18px);
}

/* Modal Styles */
#addStudentModal, #studentDetailsModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    text-align: center;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

.modal-content h4 {
    font-size: 18px;
    color: #333;
    margin-bottom: 20px;
}

#studentDetailsContent img {
    width: 100%;
    border-radius: 8px;
    margin: 10px 0;
}

/* Form Styling */
form input[type="text"] {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

form button[type="submit"] {
    background-color: #28a745;
    color: white;
    padding: 12px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button[type="submit"]:hover {
    background-color: #218838;
}

form button[type="button"] {
    background-color: #dc3545;
    color: white;
    padding: 12px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button[type="button"]:hover {
    background-color: #c82333;
}

/* Close button */
button.close {
    background-color: transparent;
    border: none;
    font-size: 20px;
    color: #888;
    cursor: pointer;
}

button.close:hover {
    color: #333;
}

/* Responsive Design */
@media (max-width: 768px) {
    .group-box {
        padding: 15px;
    }

    table th, table td {
        font-size: 12px;
        padding: 8px;
    }

    button {
        font-size: 12px;
        padding: 6px 12px;
    }

    .modal-content {
        width: 90%;
    }
}

</style>
