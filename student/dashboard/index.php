<?php
// Start the session
session_start();
include '../../database.php';
// Check if the user is logged in and has the "student" role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student' || $_SESSION['short_name'] =='***') { 
    // Your code to handle the condition

    // Redirect to the login page if the user is not a student or not logged in
    header("Location: ../index.php");
    exit;
}
$name = $_SESSION['name'];
// Start the session and retrieve the email
$student_id = $_SESSION['student_id'];
$leader_id = $_SESSION['leader_id'];
$query = "SELECT session, semester , short_name,active,project FROM students WHERE student_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
// Fetch last selected session and semester from the session
$selected_session = isset($_SESSION['session']) ? $_SESSION['session'] : '';
$selected_semester = isset($_SESSION['semester']) ? $_SESSION['semester'] : '';
$short_name = $_SESSION['short_name'];


// Combined Query to fetch data from both tables
$sql1 = "SELECT 
            s.active, s.project, t.open 
        FROM 
            students AS s
        LEFT JOIN 
            teacher_slot AS t 
        ON 
            s.short_name = t.short_name AND s.session = t.session
        WHERE 
            s.student_id = ? AND s.session = ? AND s.short_name = ? AND s.semester = ?";
$stmt = $connection->prepare($sql1);
$stmt->bind_param("ssss", $student_id, $selected_session, $short_name, $selected_semester);
$stmt->execute();
$result1 = $stmt->get_result();

// Default values
$show_form = false;
$project_name = null;
$leader = $student_id === $leader_id;

// Check the combined result
if ($row1 = $result1->fetch_assoc()) {

    if ($row1['open'] == 1) {
        $show_form1 = true; // Show form if active and no project
    }


    // Conditions for $show_form
    if ($row1['active'] == 1 && empty($row1['project']) && $leader) {
        $show_form = true; // Show form if active and no project
    } else {
        if($leader)
        {
            $sms = 'Your Account Inactive. Contact with teacher to Verify';  
        }
        else
        {

          $sms = 'Contact Admin.';
        }
        
    }
    if (!empty($row1['project'])) {
        $project_name = $row1['project']; // Assign project name if exists
    }
    if ($row1['open'] == 0) {
        $show_form = false; // Override to false if teacher slot is open
        $sms = 'Times Up.Contact Teacher  ';
    }
}



?>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        button {
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f0f0f0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #007BFF;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 500px;
            text-align: center;
        }

        .modal-content ul {
            list-style-type: none;
            padding: 0;
        }

        .modal-content li {
            margin: 5px 0;
            cursor: pointer;
        }

        .modal-content li:hover {
            background-color: #f0f0f0;
        }

        .notice-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .notice-item p {
            margin: 0;
            color: #333;
            font-weight: bold;
        }
    </style>

</head>

<body>
    <main>

        <nav class="main-menu">
            <h1>NUB HUB</h1>
            <img class="logo"
                src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/4cfdcb5a-0137-4457-8be1-6e7bd1f29ebb"
                alt="" />
            <ul>
                <li class="nav-item active">
                    <b></b>
                    <b></b>
                    <a href="#">
                        <i class="fa fa-house nav-icon"></i>
                        <span class="nav-text">Home</span>
                    </a>
                </li>







                <li class="nav-item">
                    <b></b>
                    <b></b>
                    <a href="../../project/g_panel/">
                        <i class="fa fa-sliders nav-icon"></i>
                        <span class="nav-text">File Manager</span>
                    </a>
                </li>


                <li class="nav-item">
                    <b></b>
                    <b></b>
                    <a href="../../logout.php">
                        <i class="fa fa-person-running nav-icon"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <section class="content">
            <div class="left-content">
                <style>
                    /* Project Proposal Container */
                    .weekly-schedule {
                        background: #ffffff;
                        border-radius: 10px;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        padding: 20px;
                        width: 300px;
                        text-align: center;
                    }

                    /* Title Styles */
                    .weekly-schedule h1 {
                        font-size: 24px;
                        color: #444;
                        margin-bottom: 20px;
                    }

                    .icon-container {
                        text-decoration: none;
                        /* Removes underline */
                    }

                    /* Calendar Section */
                    .calendar {
                        display: flex;
                        justify-content: center;
                    }

                    /* Day and Activity Container */
                    .day-and-activity {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 15px;
                    }

                    /* Day Number Styling */
                    .day h1 {
                        font-size: 36px;
                        background: linear-gradient(to right, #76c7c0, #84d1f7);
                        color: #ffffff;
                        border-radius: 50%;
                        width: 60px;
                        height: 60px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        margin: 0;
                        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
                    }

                    /* File Upload Form */
                    form {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }

                    /* File Input */
                    #fileInput {
                        padding: 10px;
                        border: 1px solid #ddd;
                        border-radius: 5px;
                        width: 100%;
                        max-width: 250px;
                        margin-bottom: 10px;
                    }

                    /* Upload Button */
                    .btn {
                        background: linear-gradient(to right, #6a11cb, #2575fc);
                        border: none;
                        color: #fff;
                        padding: 10px 20px;
                        font-size: 14px;
                        font-weight: bold;
                        text-transform: uppercase;
                        border-radius: 5px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    }

                    .btn:hover {
                        background: linear-gradient(to right, #2575fc, #6a11cb);
                        transform: scale(1.05);
                    }

                    /* Upload Status */
                    #uploadStatus {
                        margin-top: 10px;
                        font-size: 14px;
                        color: #6c757d;

                        .open-btn,
                        .delete-btn {
                            background: linear-gradient(to right, #43cea2, #185a9d);
                            border: none;
                            color: #fff;
                            padding: 8px 16px;
                            font-size: 14px;
                            font-weight: bold;
                            text-transform: uppercase;
                            border-radius: 5px;
                            cursor: pointer;
                            margin: 5px;
                            transition: all 0.3s ease;
                        }

                        .open-btn:hover,
                        .delete-btn:hover {
                            background: linear-gradient(to right, #185a9d, #43cea2);
                            transform: scale(1.05);
                        }


                    }
                </style>

                <div class="left-bottom">
                    <div class="weekly-schedule">
                        <h1>Project Proposal</h1>
                        <div class="calendar">
                            <div class="day-and-activity activity-one">
                                <div class="day">
                                    <h1>1</h1>
                                </div>

                                <!-- File Upload Button -->
                                <?php if ($show_form): ?>
                                    <!-- Show the upload form -->
                                    <form id="uploadForm" enctype="multipart/form-data" action="upload.php" method="POST">
                                        <input type="file" id="fileInput" name="file" accept=".pdf" required />
                                        <button type="submit" class="btn">Upload PDF</button>
                                    </form>
                                <?php elseif ($project_name): ?>
                                    <!-- Show the project name if it exists -->
                                    <p>Your project: <strong><?php echo htmlspecialchars($project_name); ?></strong></p>
                                <?php else: ?>
                                    <!-- Default message if conditions aren't met -->
                                    <p><?php echo $sms ?></p>
                                <?php endif; ?>


                                <div id="fileActions"></div>
                            </div>
                        </div>
                    </div>




                    <div class="personal-bests">
                        <h1>Notice</h1>
                        <div class="personal-bests-container">
                            <div class="best-item box-one">
                                <?php


                                // Define the query using a different variable name
                                $query = "SELECT * FROM notice where session= '$selected_session' AND short_name= '$short_name'  ORDER BY id DESC"; // Fetch all notices, ordering by most recent
                                $result5 = $connection->query($query);

                                if ($result5->num_rows > 0) {
                                    while ($row5 = $result5->fetch_assoc()) {
                                        $short_name = htmlspecialchars($row5['short_name']); // Sanitize output
                                        $notice = htmlspecialchars($row5['notice']);
                                        echo "<div class='notice-item'>
                <p>$notice</p>
              </div>";
                                    }
                                } else {
                                    echo "<p>No notices available.</p>";
                                }
                                ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="right-content">
                <div class="user-info">
                    <div class="icon-container">
                        <a href="new_add/" class="icon-link">
                            <i class="fa fa-plus nav-icon"></i>
                        </a>
                        <a href="#" class="icon-link" onclick="openPopup()">
                            <i class="fa fa-minus nav-icon"></i>
                        </a>
                    </div>


                    <h4><? echo $name ?></h4>
                    <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/40b7cce2-c289-4954-9be0-938479832a9c"
                        alt="user" />
                </div>

                <div class="active-calories">
                    <h1 style="align-self: flex-start">Session & Semester</h1>
                    <div class="active-calories-container">
                        <div class="dropdown-container">
                            <label for="session-semester-dropdown">Select Session & Semester:</label>
                            <select id="session-semester-dropdown" onchange="updateSessionAndSemesterDropdown()">
                                <option value="">-- Select --</option>
                                <?php
                                // Generate dropdown options dynamically
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $session = $row['session'];
                                        $semester = $row['semester'];
                                        $short_name = $row['short_name'];

                                        $value = $session . '-' . $semester . '-' . $short_name;

                                        // Check if this option is the selected one
                                        $selected = ($semester == $selected_semester) ? 'selected' : '';
                                        echo "<option value='$value' $selected>$session - $semester - $short_name</option>";
                                    }
                                } else {
                                    echo "<option value=''>No sessions available</option>";
                                }
                                ?>
                            </select>
                        </div>

                    </div>
                </div>
                <div class="active-calories">
                    <h1 style="align-self: flex-start">Team Members</h1>
                    <div class="active-calories-container dynamic">
                        <?php


                        // Query to fetch team members
                        $query = "SELECT student_name, student_id FROM student_team WHERE leader_id = ? AND session = ? AND semester=?";
                        $stmt = $connection->prepare($query);
                        $stmt->bind_param("sss", $leader_id, $selected_session, $selected_semester);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($leader_id == $student_id) {

                            if ($result->num_rows > 0) {
                                echo "<ul style='list-style: none; padding: 0;'>";
                                while ($row = $result->fetch_assoc()) {
                                    $student_name = $row['student_name'];
                                    $student_id1 = $row['student_id'];

                                    // Display team members with Add/Delete buttons
                                    echo "<li style='margin-bottom: 10px; display: flex; align-items: center;'>
                        <strong>$student_name</strong> - <span>$student_id1</span>";

                                    if ($show_form1) {

                                        echo " <button style='margin-left: 5px;' onclick='deleteMember(\"$student_id1\")'>Delete</button>";
                                    }

                                    echo "</li>";
                                }
                                if ($show_form1) {
                                    echo "<button style='margin-top: 20px;' onclick='showAddPopup()'>Add Member</button>";
                                }
                                echo "</ul>";
                            } else {
                                echo "<p>No team members found.</p>";
                            }

                        } else {



                            if ($result->num_rows > 0) {
                                echo "<ul style='list-style: none; padding: 0;'>";
                                while ($row = $result->fetch_assoc()) {
                                    $student_name = $row['student_name'];
                                    $student_id1 = $row['student_id'];

                                    // Display team members with Add/Delete buttons
                                    echo "<li style='margin-bottom: 10px; display: flex; align-items: center;'>
                        <strong>$student_name</strong> - <span>$student_id1</span>";

                                    if ($show_form1 && $student_id == $student_id1) {

                                        echo " <button style='margin-left: 5px;' onclick='deleteMember(\"$student_id1\")'>Delete</button>";
                                    }

                                    echo "</li>";
                                }

                                echo "</ul>";
                            } else {
                                echo "<p>No team members found.</p>";
                            }









                        }





                        $stmt->close();
                        $connection->close();
                        ?>
                    </div>
                </div>



            </div>

            <!-- Popup Modal -->
            <div id="popupModal" class="modal">
                <div class="modal-content">
                    <h3>List of Sessions & Semesters</h3>
                    <ul id="sessionList"></ul>
                    <button id="deleteButton" style="display: none;" onclick="deleteRecord()">Delete Selected</button>
                    <button onclick="closePopup()">Close</button>
                </div>
            </div>
            <!-- Popup for Adding Member -->
            <div id="addMemberPopup"
                style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; border-radius: 10px; z-index: 1000;">
                <h3>Add New Member</h3>
                <form id="addMemberForm">
                    <label for="studentId">Student ID:</label>
                    <input type="number" id="studentId" name="studentId" required><br><br>
                    <label for="studentName">Student Name:</label>
                    <input type="text" id="studentName" name="studentName" required><br><br>
                    <button type="button" onclick="submitAddMember()">Submit</button>
                    <button type="button" onclick="closeAddPopup()">Cancel</button>
                </form>
            </div>


        </section>
    </main>
</body>

<script src="script.js"></script>
<script>
    function updateSessionAndSemesterDropdown() {
        const dropdown = document.getElementById("session-semester-dropdown");
        const selectedValue = dropdown.value;

        if (selectedValue) {
            const [session, semester, short_name] = selectedValue.split("-");

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_session.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert("Updated to Session: " + session + " and Semester: " + semester + " and Teacher_name: " + short_name);

                    // Optionally refresh the page to show the updated selection
                    window.location.reload();
                }
            };
            xhr.send("session=" + session + "&semester=" + semester + "&short_name=" + short_name);
        }
    }
</script>
<script>

    function deleteMember(studentId) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_member.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert(xhr.responseText);
                location.reload(); // Refresh the page to update the list
            }
        };
        xhr.send("student_id=" + studentId);
    }
</script>
<script>
    function showAddPopup() {
        document.getElementById("addMemberPopup").style.display = "block";
    }

    function closeAddPopup() {
        document.getElementById("addMemberPopup").style.display = "none";
    }

    function submitAddMember() {
        const studentId = document.getElementById("studentId").value.trim();
        const studentName = document.getElementById("studentName").value.trim();

        if (!studentId || !studentName) {
            alert("Please enter both Student ID and Student Name.");
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "add_member.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                window.location.reload();
                alert(xhr.responseText);
                if (xhr.responseText.includes("successfully")) {
                    header("Location: ../index.php");
                    closeAddPopup();

                }
            }
        };
        xhr.send("student_id=" + encodeURIComponent(studentId) + "&student_name=" + encodeURIComponent(studentName));
    }


</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const fileActions = document.getElementById("fileActions");
        const student_id = <?php echo $student_id ?>;
        const leader_id = <?php echo $leader_id ?>;
        
        const leader = student_id === leader_id;

console.log(leader);
        // Check if the file exists
        fetch("check_file.php")
            .then((response) => response.json())
            .then((fileData) => {
                if (fileData.exists) {
                    fileActions.innerHTML = `
                    <p>File found: ${fileData.fileName}</p>
                    <button class="open-btn" onclick="openFile('${fileData.url}')">Open File</button>
                `;

                    // Check if the teacher slot is open
                    fetch("check_status.php")
                        .then((response) => response.json())
                        .then((statusData) => {
                            console.log(statusData.project_empty);
                            if (statusData.status === "success" && statusData.open && statusData.project_empty && leader) {


                           
                                fileActions.innerHTML += `

                                <button class="delete-btn" onclick="deleteFile()">Delete File</button>
                            `;
                                attachUploadHandler();
                            
                            } else {
                                if(leader)
                            {
                                fileActions.innerHTML += `
                                <p>Upload and delete options are disabled as the teacher close to the Permission.</p>
                            `;
                            }
                            else
                            {
                                fileActions.innerHTML += `
                                <p>Upload and delete options are disabled as you are not Team leader.</p>
                            `; 
                            }
                            }
                        })
                        .catch((error) => {
                            console.error("Error checking teacher slot status:", error);
                            fileActions.innerHTML += `<p style="color: red;">An error occurred while checking teacher slot status.</p>`;
                        });
                } else {
                    fileActions.innerHTML = `
                    <p>No file found.</p>
                `;


                }
            })
            .catch((error) => {
                console.error("Error checking file existence:", error);
                fileActions.innerHTML = `<p style="color: red;">An error occurred while checking file existence.</p>`;
            });
    });

    // Attach upload handler
    function attachUploadHandler() {
        document.getElementById("uploadForm").addEventListener("submit", function (event) {
            event.preventDefault();

            const fileInput = document.getElementById("fileInput");
            const fileActions = document.getElementById("fileActions");
            const formData = new FormData(this);

            fetch("upload.php", {
                method: "POST",
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === "success") {
                        fileActions.innerHTML = `
                        <p>File uploaded successfully: ${data.fileName}</p>
                        <button class="open-btn" onclick="openFile('../assets/pdf/${data.fileName}')">Open File</button>
                        <button class="delete-btn" onclick="deleteFile()">Delete File</button>
                    `;
                    } else {
                        fileActions.innerHTML = `<p style="color: red;">${data.message}</p>`;
                    }
                })
                .catch((error) => {
                    console.error("Error uploading file:", error);
                    fileActions.innerHTML = `<p style="color: red;">An error occurred while uploading the file.</p>`;
                });
        });
    }

    function openFile(fileURL) {
        window.open(fileURL, "_blank"); // Open the file in a new tab
    }

    function deleteFile() {
        const fileActions = document.getElementById("fileActions");

        fetch("delete_file.php", {
            method: "POST",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "success") {
                    fileActions.innerHTML = `
                    <p>${data.message}</p>
                `;
                } else {
                    fileActions.innerHTML += `<p style="color: red;">${data.message}</p>`;
                }
            })
            .catch((error) => {
                console.error("Error deleting file:", error);
                fileActions.innerHTML += `<p style="color: red;">An error occurred while deleting the file.</p>`;
            });
    }

</script>
<script>
    let selectedItem = null;

    function openPopup() {
        // Fetch list dynamically
        fetch('fetch_sessions.php')
            .then((response) => response.json())
            .then((data) => {
                const sessionList = document.getElementById('sessionList');
                sessionList.innerHTML = ''; // Clear the list
                data.forEach((item) => {
                    const li = document.createElement('li');
                    li.textContent = `${item.session} - ${item.semester} - ${item.short_name}`;
                    li.setAttribute('data-id', item.id);
                    li.onclick = () => selectItem(li);
                    sessionList.appendChild(li);
                });
            });
        document.getElementById('popupModal').style.display = 'flex';
    }

    function closePopup() {
        document.getElementById('popupModal').style.display = 'none';
        document.getElementById('deleteButton').style.display = 'none';
        selectedItem = null;
    }

    function selectItem(item) {
        if (selectedItem) {
            selectedItem.style.backgroundColor = '';
        }
        item.style.backgroundColor = '#d3d3d3';
        selectedItem = item;
        document.getElementById('deleteButton').style.display = 'block';
    }

    function deleteRecord() {
        if (selectedItem) {
            const id = selectedItem.getAttribute('data-id');
            fetch(`delete_session.php?id=${id}`, {
                method: 'POST',
            })
                .then((response) => response.json())
                .then((result) => {
                    if (result.success) {
                        alert('Record deleted successfully!');
                        selectedItem.remove();
                        document.getElementById('deleteButton').style.display = 'none';
                    } else {
                        alert(result.message);
                    }
                });
        }
    }

</script>

</html>