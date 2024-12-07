<?php
// Start the session
session_start();
include '../database.php';
// Check if the user is logged in and has the "teacher" role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    // Redirect to the login page if the user is not a teacher or not logged in
    header("Location: ../index.php");
    exit;
}
$name = $_SESSION['name'];
$short_name = $_SESSION['short_name'];




$query = "SELECT session FROM teacher_slot WHERE short_name = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $short_name);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the available sessions for the current short_name
$query = "SELECT session FROM teacher_slot WHERE short_name = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $short_name);
$stmt->execute();
$result3 = $stmt->get_result();

// Store the sessions in an array for later use
$sessions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row['session'];
    }
} else {
    $sessions[] = "No sessions available"; // In case no sessions are found
}

$stmt->close();

// Fetch the notices from the database
$notices_query = "SELECT * FROM notice WHERE short_name = ?";
$stmt = $connection->prepare($notices_query);
$stmt->bind_param("s", $short_name);
$stmt->execute();
$notices_result = $stmt->get_result();
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
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1000;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            /* Enable scrolling if needed */
            background-color: rgba(0, 0, 0, 0.5);
            /* Black with opacity */
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80%;
            /* Width relative to the screen */
            max-width: 600px;
            /* Maximum width */
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




        /* File Upload Form */
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 1px;
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

        .active-calories {
   
   margin: 38px 10px 0 !important;
   
}
        /* Style for close button */
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #333;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #ff0000;
            /* Highlight on hover */
        }

        .btn_c {
            display: flex;
            justify-content: center;
            /* Center items horizontally */
            align-items: center;
            /* Center items vertically */
            gap: 10px;
            /* Space between buttons */
            margin-top: 20px;
            /* Optional: Add some margin if needed */
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
                    <a href="../tutorial">
                        <i class="fa fa-sliders nav-icon"></i>
                        <span class="nav-text">Tutorial</span>
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

                <div class="left-bottom">
                    <div class="weekly-schedule">
                        <h1>Session Slot</h1>
                        <button id="openSessionPopup" class="btn">Manage Session Slot</button>

                        <!-- Modal Popup -->
                        <div id="sessionPopup" class="modal">
                            <div class="modal-content">
                                <span id="closePopup" class="close">&times;</span>
                                <h2>Manage Session Slot</h2>
                                <div id="popupContent">
                                    <!-- Default buttons -->
                                    <div class="btn_c">
                                        <button id="createSession" class="btn">Create Session</button>
                                        <button id="showSession" class="btn">Show Sessions</button>
                                    </div>
                                    <!-- Create Form -->
                                    <div id="createForm" style="display: none;">
                                        <h3>Create a New Session</h3>
                                        <form id="sessionForm">
                                            <input type="hidden" id="shortName" name="short_name"
                                                value="<?php echo htmlspecialchars($short_name); ?>" />

                                            <label for="sessionName">Session Name:</label>
                                            <input type="text" id="sessionName" name="session" required />
                                            <label for="totalTeam">Total Team:</label>
                                            <input type="number" id="totalTeam" name="total_team" required />
                                            <button type="submit" class="btn">Submit</button>
                                        </form>
                                    </div>

                                    <!-- Show Sessions -->
                                    <div id="showSessions" style="display: none;">
                                        <h3>All Sessions</h3>
                                        <table id="sessionsTable" border="1">
                                            <thead>
                                                <tr>
                                                    <th>Short Name</th>
                                                    <th>Session Name</th>
                                                    <th>Total Team</th>
                                                    <th>Slot Open</th>
                                                    <th>Project Slot Open</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Sessions will be dynamically loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>







                    <div class="personal-bests">
                        <h1>Notice</h1>
                        <div class="personal-bests">
    <h1>Notice</h1>
    <div class="personal-bests-container">
        <div class="best-item box-one">
            <div class="notice-item">
                <?php
                if ($notices_result->num_rows > 0) {
                    while ($notice = $notices_result->fetch_assoc()) {
                        echo "<p>" . htmlspecialchars($notice['notice']) . " (Session: " . htmlspecialchars($notice['session']) . ")</p>";
                    }
                } else {
                    echo "<p>No notices available.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Button to trigger the modal -->
        <button onclick="openNoticeModal()">Add New Notice</button>
    </div>
</div>

<!-- Notice Modal -->
<div id="noticeModal" class="modal">
    <div class="modal-content">
        <h2>Add New Notice</h2>
        <form id="noticeForm">
            <label for="session">Session:</label>
            <select id="session" name="session" required>
                <!-- PHP to populate sessions dynamically -->
                <?php
                foreach ($sessions as $session) {
                    echo "<option value='$session'>$session</option>";
                }
                ?>
            </select><br><br>

            <label for="notice">Notice:</label>
            <textarea id="notice" name="notice" rows="4" required></textarea><br><br>

            <button type="submit">Submit Notice</button>
            <button type="button" onclick="closeNoticeModal()">Cancel</button>
        </form>
    </div>
</div>

                    </div>
                </div>
            </div>

            <div class="right-content">
                <div class="user-info">
                    <!-- <div class="icon-container">
                        <a href="new_add/" class="icon-link">
                            <i class="fa fa-plus nav-icon"></i>
                        </a>
                        <a href="#" class="icon-link" onclick="openPopup()">
                            <i class="fa fa-minus nav-icon"></i>
                        </a>
                    </div> -->


                    <h4><? echo $name ?></h4>
                    <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/40b7cce2-c289-4954-9be0-938479832a9c"
                        alt="user" />
                </div>

                <div class="active-calories">
                    <h1 style="align-self: flex-start">Session & Semester</h1>
                    <div class="active-calories-container">
                        <div class="dropdown-container">
                            <label for="session-semester-dropdown">Select Session & Semester:</label>
                            <select id="session-semester-dropdown" onchange="openStudentDetails()">
                                <option value="">-- Select --</option>
                                <?php
                                // Generate dropdown options dynamically
                                if ($result3->num_rows > 0) {
                                    while ($row3 = $result3->fetch_assoc()) {
                                        $session = $row3['session'];
                                        $value = $session;

                                        // Check if this option is the selected one
                                        $selected = ($session == $selected_session) ? 'selected' : '';

                                        // Echo the option
                                        echo "<option value='$value' $selected>$session</option>";
                                    }
                                } else {
                                    echo "<option value=''>No sessions available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <script>
                            // Function to open a new window when a session is selected
                            function openStudentDetails() {
                                var dropdown = document.getElementById('session-semester-dropdown');
                                var selectedSession = dropdown.value;

                                // Fetch short_name from PHP session
                                var shortName = "<?php echo $_SESSION['short_name']; ?>";

                                // Check if both session and short_name are valid
                                if (selectedSession && shortName) {
                                    var detailsUrl = "student_details.php?session=" + encodeURIComponent(selectedSession) + "&short_name=" + encodeURIComponent(shortName);
                                    window.open(detailsUrl, '_blank');  // Open the student details in a new tab
                                } else {
                                    alert("Both session and short name must be selected!");
                                }
                            }
                        </script>



                    </div>
                </div>
                <!-- <div class="active-calories">
                    <h1 style="align-self: flex-start">Team Members</h1>
                    <div class="active-calories-container dynamic">

                        <ul style='list-style: none; padding: 0;'>

                            <li style='margin-bottom: 10px; display: flex; align-items: center;'>
                                <strong>student_name</strong> - <span>student_id1</span>

                                <button style='margin-left: 5px;' onclick=' '>Delete</button>

                            </li>
                            <button style='margin-top: 20px;' onclick='showAddPopup()'>Add Member</button>
                        </ul>




                    </div>
                </div> -->



            </div>



        </section>
    </main>
</body>

<script src="script.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openPopup = document.getElementById('openSessionPopup');
        const modal = document.getElementById('sessionPopup');
        const closePopup = document.getElementById('closePopup');
        const createButton = document.getElementById('createSession');
        const showButton = document.getElementById('showSession');
        const createForm = document.getElementById('createForm');
        const showSessions = document.getElementById('showSessions');
        // const sessionForm = document.getElementById('sessionForm');
        const sessionsTable = document.querySelector('#sessionsTable tbody');

        // Open modal
        openPopup.addEventListener('click', () => {
            modal.style.display = 'block';
        });

        // Close modal
        closePopup.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Show Create Form
        createButton.addEventListener('click', () => {
            createForm.style.display = 'block';
            showSessions.style.display = 'none';

            // Reset form for creating new session (ensure it is empty and ready for new data)
            createForm.innerHTML = `
            <h3>Create a New Session</h3>
            <form id="sessionForm">
                <input type="hidden" id="shortName" name="short_name" />
                <label for="sessionName">Session Name:</label>
                <input type="text" id="sessionName" name="session" required />
                <label for="totalTeam">Total Team:</label>
                <input type="number" id="totalTeam" name="total_team" required />
                <button type="submit" class="btn">Submit</button>
            </form>
        `;

            // Rebind the form submission handler
            sessionForm = document.getElementById('sessionForm');
            sessionForm.addEventListener('submit', handleFormSubmit);
            function handleFormSubmit(e) {
                e.preventDefault();
                const formData = new FormData(sessionForm);
                fetch('insert_session.php', {
                    method: 'POST',
                    body: formData,
                })
                    .then((response) => response.json())
                    .then((data) => {
                        alert(data.message);
                        if (data.success) {
                            showButton.click(); // Load sessions after creation
                        }
                    });
            }
        });

        // Show Sessions
        showButton.addEventListener('click', () => {
            createForm.style.display = 'none';
            showSessions.style.display = 'block';
            fetchSessions();
        });

        // Handle Form Submission for Create Session


        // Fetch Sessions
        function fetchSessions() {
            fetch('fetch_sessions.php')
                .then((response) => response.json())
                .then((data) => {
                    sessionsTable.innerHTML = ''; // Clear table
                    data.sessions.forEach((session) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td>${session.short_name}</td>
                        <td>${session.session}</td>
                        <td>${session.total_team}</td>
                        <td style="background-color: ${session.open == 1 ? 'green' : 'black'}; color: white;">
                            ${session.open == 1 ? 'open' : 'close'}
                        </td>
                        <td style="background-color: ${session.project_open == 1 ? 'green' : 'black'}; color: white;">
                            ${session.project_open == 1 ? 'open' : 'close'}
                        </td>
                        <td>
                            <button class="editBtn" data-id="${session.id}">Edit</button>
                            <button class="deleteBtn" data-id="${session.id}">Delete</button>
                        </td>
                    `;
                        sessionsTable.appendChild(row);
                    });

                    // Add event listeners for edit and delete buttons
                    document.querySelectorAll('.editBtn').forEach((btn) =>
                        btn.addEventListener('click', handleEdit)
                    );
                    document.querySelectorAll('.deleteBtn').forEach((btn) =>
                        btn.addEventListener('click', handleDelete)
                    );
                });
        }

        // Handle Edit
        function handleEdit(e) {
            const id = e.target.dataset.id;

            // Fetch the existing data for the selected session
            fetch(`get_session.php?id=${id}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        const session = data.session;

                        // Populate the form with the existing values
                        createForm.style.display = 'block';
                        showSessions.style.display = 'none';
                        createForm.innerHTML = `
                        <h3>Edit Session</h3>
                        <form id="editSessionForm">
                            <input type="hidden" name="id" value="${id}" />
                            <label for="sessionName">Session Name:</label>
                            <input type="text" id="sessionName" name="session" value="${session.session}" required />
                            <label for="totalTeam">Total Team:</label>
                            <input type="number" id="totalTeam" name="total_team" value="${session.total_team}" required />
                            <label for="open">Slot Open:</label>
                            <label class="switch">
                                <input type="checkbox" id="openSwitch" ${session.open == "1" ? "checked" : ""} />
                                <span class="slider round"></span>
                            </label>
                            <input type="hidden" id="open" name="open" value="${session.open}" />
                            <label for="projectOpen">Project Slot Open:</label>
                            <label class="switch">
                                <input type="checkbox" id="projectOpenSwitch" ${session.project_open == "1" ? "checked" : ""} />
                                <span class="slider round"></span>
                            </label>
                            <input type="hidden" id="projectOpen" name="project_open" value="${session.project_open}" />
                            <button type="submit" class="btn">Update Session</button>
                        </form>
                    `;

                        // CSS for the switch
                        const style = document.createElement('style');
                        style.innerHTML = `
                        .switch {
                            position: relative;
                            display: inline-block;
                            width: 34px;
                            height: 20px;
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
                            transition: 0.4s;
                            border-radius: 20px;
                        }

                        .slider:before {
                            position: absolute;
                            content: "";
                            height: 14px;
                            width: 14px;
                            left: 3px;
                            bottom: 3px;
                            background-color: white;
                            transition: 0.4s;
                            border-radius: 50%;
                        }

                        input:checked + .slider {
                            background-color: #2196F3;
                        }

                        input:checked + .slider:before {
                            transform: translateX(14px);
                        }
                    `;
                        document.head.appendChild(style);

                        // Add event listeners to synchronize the hidden inputs with the checkbox states
                        document.getElementById('openSwitch').addEventListener('change', function () {
                            document.getElementById('open').value = this.checked ? "1" : "0";
                        });

                        document.getElementById('projectOpenSwitch').addEventListener('change', function () {
                            document.getElementById('projectOpen').value = this.checked ? "1" : "0";
                        });

                        // Handle the edit form submission
                        const editForm = document.getElementById('editSessionForm');
                        editForm.addEventListener('submit', (event) => {
                            event.preventDefault();

                            // Send the updated data to the server
                            const formData = new FormData(editForm);
                            fetch('update_session.php', {
                                method: 'POST',
                                body: formData,
                            })
                                .then((response) => response.json())
                                .then((result) => {
                                    alert(result.message);
                                    if (result.success) {
                                        showButton.click(); // Reload sessions
                                    }
                                });
                        });
                    } else {
                        alert('Failed to fetch session data');
                    }
                });
        }

        // Handle Delete
        function handleDelete(e) {
            const id = e.target.dataset.id;
            if (confirm('Are you sure you want to delete this session?')) {
                fetch(`delete_session.php?id=${id}`, { method: 'GET' })
                    .then((response) => response.json())
                    .then((data) => {
                        alert(data.message);
                        if (data.success) {
                            fetchSessions(); // Reload sessions after deletion
                        }
                    });
            }
        }
    });

</script>


<script>
// Open the Notice Modal
function openNoticeModal() {
    document.getElementById('noticeModal').style.display = 'block';
}

// Close the Notice Modal
function closeNoticeModal() {
    document.getElementById('noticeModal').style.display = 'none';
}

// Handle form submission (AJAX)
document.getElementById("noticeForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent form from refreshing the page

    var notice = document.getElementById("notice").value;
    var session = document.getElementById("session").value;  // Get the selected session

    // AJAX to send form data to the PHP script
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "insert_notice.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            // On success, append the new notice to the list
            var newNotice = "<div class='notice-item'><p>" + notice + " (Session: " + session + ")</p></div>";
            document.querySelector(".personal-bests-container").innerHTML += newNotice;
            closeNoticeModal(); // Close the modal
        } else {
            alert("Error adding notice.");
        }
    };

    xhr.send("notice=" + encodeURIComponent(notice) + "&session=" + encodeURIComponent(session));
});
</script>

<style>
/* Basic Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    width: 300px;
    text-align: center;
}

#noticeModal textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* For the notice container */
.personal-bests-container {
    display: flex;
    flex-direction: column;
}

.notice-item p {
    margin: 10px 0;
}

button {
    margin-top: 10px;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}
</style>

</html>