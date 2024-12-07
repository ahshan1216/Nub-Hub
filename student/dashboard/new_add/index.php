<?php
// Start the session
session_start();
include '../../../database.php';
// Check if the user is logged in and has the "student" role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    // Redirect to the login page if the user is not a student or not logged in
    header("Location: ../index.php");
    exit;
}

// Start the session and retrieve the email
$student_id = $_SESSION['student_id'];

$name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];
$add_new_session = $_SESSION['session_value'];







// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {



    // Get the selected faculty
    $selectedFaculty = $_POST['selected_faculty']; // Assuming you have a field with name="faculty"
    $_SESSION['short_name'] = $selectedFaculty;
    $session = $_POST['session1'];
    $semester = $_POST['semester'];

    // Insert data into the `students` table
    $insertStmt = $connection->prepare("INSERT INTO students (short_name, student_id, session,semester,user_id) VALUES (?, ?, ?,?,?)");
    $insertStmt->bind_param("ssssi", $selectedFaculty, $student_id, $session, $semester, $user_id);

    if ($insertStmt->execute()) {
        echo "Data inserted successfully into students.";
    } else {
        echo "Error inserting data into students: " . $insertStmt->error;
    }

    $insertStmt->close();



    // Insert the leader into `student_team`
    $stmt = $connection->prepare("INSERT INTO student_team (leader_id, student_id, student_name, session,semester) VALUES (?,?, ?, ?, ?)");
    $stmt->bind_param("sssss", $student_id, $student_id, $name, $session,$semester); // Leader is their own student_id
    if ($stmt->execute()) {
        echo "Leader $name added successfully.<br>";
    } else {
        echo "Error adding leader $name: " . $stmt->error . "<br>";
    }
    $stmt->close();
    // Insert team members into `student_team`
    foreach ($_POST as $key => $value) {
        if (preg_match('/team_member_(\d+)_id/', $key, $matches)) {
            $index = $matches[1];
            $memberId = $value;
            $memberName = $_POST["team_member_{$index}_name"];
            $stmt = $connection->prepare("INSERT INTO student_team (leader_id, student_id, student_name, session,semester) VALUES (?,?, ?, ?, ?)");
            $stmt->bind_param("sssss", $student_id, $memberId, $memberName, $session,$semester);
            if ($stmt->execute()) {
                echo "Team member $memberName added successfully.<br>";
            } else {
                echo "Error adding team member $memberName: " . $stmt->error . "<br>";
            }
            $stmt->close();
        }
    }
    header("Location: /student/dashboard");

    $connection->close();
}







?>

<html>

<head>

    <link rel="stylesheet" href="assets/res/style.css">
    <style>
        .upload-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .upload-item {
            text-align: center;
        }

        .image-preview {
            margin-top: 10px;
            width: 200px;
            height: 200px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f9f9f9;
            overflow: hidden;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }

        .faculty-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            /* Two columns */
            gap: 20px;
            /* Space between the boxes */
            margin-top: 20px;
        }

        .faculty-box {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;

        }

        .faculty-box:hover {
            background-color: #f1f1f1;
        }

        .faculty-box.selected {
            background-color: lightgreen;
            border-color: green;
        }

        .submit,
        .previous {
            margin-top: 20px;
        }
    </style>

</head>


<body>
    <!-- multistep form -->
    <form id="msform" method="POST" enctype="multipart/form-data">
        <!-- progressbar -->
        <ul id="progressbar">
            <li class="active">New Adding</li>
            <li>Account Setup</li>
            <li>Team Details</li>
        </ul>

        <!-- fieldsets -->
        <fieldset>
            <h2 class="fs-title">New Session and Semester Adding</h2>
            <h3 class="fs-subtitle">This is step 1</h3>

            <div class="mb-3">
                <small id="sessionHint" style="color: red;"></small>
                <input type="text" class="form-control" name="session1" id="session"
                    placeholder="Session (e.g., spring24)" aria-label="sess" required>


            </div>
            <div class="mb-3">

                <input type="number" class="form-control" name="semester" id="semester" placeholder="Semester"
                    aria-label="Semester">
            </div>

            <input type="button" name="next" class="next action-button" id="nextButton1" value="Next" disabled />
        </fieldset>

        <fieldset class="fieldset1">
            <h2 class="fs-title">Choice Active Faculty</h2>
            <h3 class="fs-subtitle">Contact With Faculty then Select</h3>
            <input type="hidden" name="selected_faculty" id="selected_faculty" />

            <!-- Faculty Boxes -->
            <div class="faculty-container" id="faculty-container">
                <?= $facultyOptions; ?>
            </div>
            <input type="button" name="previous" class="previous action-button" value="Previous" />
            <input type="button" name="next" id="nextButton" class="next action-button" value="Next" disabled
                style="pointer-events: none; opacity: 0.5;" onclick="return validateSelection();" />

        </fieldset>


        <fieldset class="fieldset1">
            <h2 class="fs-title">Team Details</h2>
            <h3 class="fs-subtitle">You Already A Leader. Fill Up Other Member </h3>
            <div id="team-input-fields"></div>
            <!-- Previous and Submit Buttons -->
            <input type="button" name="previous" class="previous action-button" value="Previous" />


            <input type="submit" name="submit" class="submit action-button" value="Submit" />
        </fieldset>





    </form>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
<script src="assets/res/script.js"></script>
<!-- <script>
// Wait for the document to fully load
document.addEventListener('DOMContentLoaded', function () {
    const facultyContainer = document.getElementById('faculty-container');
    const selectedFacultyInput = document.getElementById('selected_faculty');
    const teamInputFields = $('#team-input-fields');
    const nextButton = document.getElementById('nextButton');
    
    // Disable the Next button by default
    nextButton.disabled = true;
    nextButton.style.pointerEvents = 'none';
    nextButton.style.opacity = '0.5';

    // Function to fetch faculty options via AJAX
    function fetchFacultyOptions() {
        $.ajax({
            url: 'fetch_faculty.php', // Path to your PHP file
            method: 'GET',
            data: { fetch_faculty: 1 },
            dataType: 'json',
            success: function (response) {
                // Check if the response has options
                if (response.options) {
                    facultyContainer.innerHTML = response.options; // Update faculty container with options
                    attachFacultyBoxEvents();  // Attach events to the new faculty boxes
                } else {
                    facultyContainer.innerHTML = "<div class='faculty-box'>No active faculty available</div>";
                }
            },
            error: function () {
                alert('Error fetching faculty data.');
            }
        });
    }

    // Function to attach click events to the faculty boxes
    function attachFacultyBoxEvents() {
        const facultyBoxes = document.querySelectorAll('.faculty-box');
        
        facultyBoxes.forEach(box => {
            box.addEventListener('click', function () {
                // Deselect other boxes
                facultyBoxes.forEach(b => b.classList.remove('selected'));
                
                // Select the clicked box
                this.classList.add('selected');
                
                // Set the hidden input value to the faculty selected
                selectedFacultyInput.value = this.getAttribute('data-name');
                const shortName = selectedFacultyInput.value;

                // Enable the Next button after selection
                nextButton.disabled = false;
                nextButton.style.pointerEvents = 'auto';
                nextButton.style.opacity = '1';

                // Fetch and display team members based on selected faculty
                fetchTeamMembers(shortName);
            });
        });
    }

    // Function to fetch team members based on selected faculty
    function fetchTeamMembers(shortName) {
        $.ajax({
            url: 'fetch_total_team.php',
            method: 'POST',
            data: { short_name: shortName },
            dataType: 'json',
            success: function (response) {
                if (response.total_team) {
                    teamInputFields.empty(); // Clear previous inputs

                    const totalTeam = response.total_team;
                    for (let i = 1; i < totalTeam; i++) {
                        $('#team-input-fields').append(`
                            <div class="team-member-fields">
                                <input type="text" name="team_member_${i}_name" placeholder="Team Member ${i + 1} Name" />
                                <input type="number" name="team_member_${i}_id" placeholder="Team Member ${i + 1} ID" />
                            </div>
                        `);
                    }
                } else {
                    alert('Failed to fetch team details.');
                }
            },
            error: function () {
                alert('Error fetching team data.');
            }
        });
    }

    // Validate Selection before Submit
    function validateSelection() {
        const selectedFaculty = document.querySelector('.faculty-box.selected');
        if (!selectedFaculty) {
            alert("Please select a faculty before submitting.");
            return false;
        }

        // Optionally, you can retrieve the selected faculty name:
        const selectedFacultyName = selectedFaculty.getAttribute('data-name');
        console.log("Selected Faculty: " + selectedFacultyName); // Use this for further processing.

        return true; // Proceed to submit
    }

    // Fetch faculty options on page load
    fetchFacultyOptions();
});

</script> -->


<script>
    // const value=0;
    document.getElementById('session').addEventListener('input', function () {
        const inputField = this;
        const value = inputField.value.trim().toLowerCase();
        const hint = document.getElementById('sessionHint');
        const submitButton = document.getElementById('nextButton1');
        const role = 'student';

        if (role !== "student") {
            hint.textContent = ''; // Clear any hint text
            submitButton.disabled = false; // Enable submit button for non-students
            submitButton.style.backgroundColor = ''; // Reset button color
            submitButton.style.cursor = ''; // Reset cursor
            submitButton.style.border = ''; // Reset border
            return;
        }

        // Regex to match correct format (spring24, fall24, summer24)
        const validFormat = /^(spring|fall|summer)\d{2}$/;
        const validSeason = /(spring|fall|summer)/i;
        const validYear = /\d{2,4}/;

        if (!validFormat.test(value)) {
            let suggestion = '';

            // Check for valid season
            const seasonMatch = value.match(validSeason);
            if (seasonMatch) {
                const season = seasonMatch[1].toLowerCase();

                // Check for year and extract last two digits
                const yearMatch = value.match(validYear);
                if (yearMatch) {
                    let year = yearMatch[0];
                    if (year.length === 4) {
                        year = year.slice(-2); // Take the last two digits if a four-digit year is provided
                    }
                    suggestion = season + year;
                } else {
                    // If no valid year, default to a generic suggestion
                    suggestion = season + '24'; // Default year
                }
            }

            if (suggestion) {
                hint.textContent = `Did you mean "${suggestion}"?`;
            } else {
                hint.textContent = 'Invalid format. Please use spring24, fall24, or summer24.';
            }

            hint.style.color = 'red'; // Alert text in red
            submitButton.disabled = true; // Disable submit button
            submitButton.style.backgroundColor = '#ccc'; // Change button color to grey
            submitButton.style.cursor = 'not-allowed'; // Show not-allowed cursor
            submitButton.style.border = 'none'; // Make border consistent
        } else {
            hint.textContent = ''; // Clear the hint if the input is valid
            submitButton.disabled = false; // Enable submit button
            submitButton.style.backgroundColor = ''; // Reset to default button color
            submitButton.style.cursor = ''; // Reset cursor to default
            submitButton.style.border = ''; // Reset border
        }

    });











    document.addEventListener('DOMContentLoaded', function () {
        const facultyContainer = document.getElementById('faculty-container');
        const selectedFacultyInput = document.getElementById('selected_faculty');
        const teamInputFields = $('#team-input-fields');
        const nextButton = document.getElementById('nextButton');


        // Disable the Next button by default
        nextButton.disabled = true;

        nextButton.style.pointerEvents = 'none';
        nextButton.style.opacity = '0.5';

        // Function to fetch faculty options via AJAX
        function fetchFacultyOptions(value, semesterValue) {
            $.ajax({
                url: 'fetch_faculty.php', // Path to your PHP file
                method: 'GET',
                data: { fetch_faculty: 1, value: value, semesterValue: semesterValue },
                dataType: 'json',
                success: function (response) {
                    if (response.options) {
                        facultyContainer.innerHTML = response.options; // Update faculty container with options
                        attachFacultyBoxEvents();  // Attach events to the new faculty boxes
                    } else {
                        facultyContainer.innerHTML = "<div class='faculty-box'>No active faculty available</div>";
                    }
                },
                error: function () {
                    alert('Error fetching faculty data.');
                }
            });
        }

        // Function to attach click events to the faculty boxes
        function attachFacultyBoxEvents() {
            const facultyBoxes = document.querySelectorAll('.faculty-box');

            facultyBoxes.forEach(box => {
                box.addEventListener('click', function () {
                    // Deselect other boxes
                    facultyBoxes.forEach(b => b.classList.remove('selected'));

                    // Select the clicked box
                    this.classList.add('selected');

                    // Set the hidden input value to the faculty selected
                    selectedFacultyInput.value = this.getAttribute('data-name');
                    const shortName = selectedFacultyInput.value;

                    // Enable the Next button after selection
                    nextButton.disabled = false;
                    nextButton.style.pointerEvents = 'auto';
                    nextButton.style.opacity = '1';

                    // Fetch and display team members based on selected faculty
                    fetchTeamMembers(shortName);
                });
            });
        }

        // Function to fetch team members based on selected faculty
        function fetchTeamMembers(shortName) {
            $.ajax({
                url: 'fetch_total_team.php',
                method: 'POST',
                data: { short_name: shortName },
                dataType: 'json',
                success: function (response) {
                    if (response.total_team) {
                        teamInputFields.empty(); // Clear previous inputs

                        const totalTeam = response.total_team;
                        for (let i = 1; i < totalTeam; i++) {  // Start at i = 0 to create inputs for all members
                            $('#team-input-fields').append(`
                            <div class="team-member-fields">
                                <input type="text" name="team_member_${i + 1}_name" placeholder="Team Member ${i + 1} Name" />
                                <input type="number" name="team_member_${i + 1}_id" placeholder="Team Member ${i + 1} ID" />
                            </div>
                        `);
                        }
                    } else {
                        alert('Failed to fetch team details.');
                    }
                },
                error: function () {
                    alert('Error fetching team data.');
                }
            });
        }

        // Validate Selection before Submit
        function validateSelection() {
            const selectedFaculty = document.querySelector('.faculty-box.selected');
            if (!selectedFaculty) {
                alert("Please select a faculty before submitting.");
                return false;
            }

            const selectedFacultyName = selectedFaculty.getAttribute('data-name');
            console.log("Selected Faculty: " + selectedFacultyName); // Use this for further processing.

            return true; // Proceed to submit
        }

        // Fetch faculty options on page load
// Add event listeners for both fields
document.getElementById('session').addEventListener('input', updateValues);
document.getElementById('semester').addEventListener('input', updateValues);

function updateValues() {
    // Get the values of both fields
    const sessionValue = document.getElementById('session').value.trim().toLowerCase();
    const semesterValue = document.getElementById('semester').value;

    console.log('Session:', sessionValue);
    console.log('Semester:', semesterValue);

    // Call your function with updated values
    fetchFacultyOptions(sessionValue, semesterValue);
}


        // Initial fetch on page load
        fetchFacultyOptions(value, semesterValue);
    });

    $(document).on('submit', '#msform', function (e) {
        e.preventDefault();

        // Check if semester value is valid
        var semesterField = document.getElementById("semester");
        if (!semesterField.value || semesterField.value <= 0) {
            alert("Please enter a valid semester value.");
            semesterField.focus(); // Focus on the semester input field
            return; // Stop the form submission
        }

        // Add an event listener for the input field

        const sessionValue = document.getElementById('session').value;  // Get value when form is submitted
        console.log(sessionValue);  // Print the value to the console


        // console.log(sessionValue);
        const teamMemberIds = [];
        $('#team-input-fields input[name*="_id"]').each(function () {
            teamMemberIds.push($(this).val());
        });
        $.post('check_team_member_ids.php', { ids: teamMemberIds, value: sessionValue }, function (response) {

            if (response.status === 'error') {
                alert('This ID already Has a Group ' + response.duplicates.join(', '));
            } else {
                // If no duplicates, programmatically submit the form
                const formElement = document.getElementById('msform');
                if (formElement) {
                    HTMLFormElement.prototype.submit.call(formElement); // Ensure native submission
                } else {
                    console.error('Form element with id "msform" not found!');
                }
            }
        }, 'json');
    });
</script>

<script>
    document.querySelector("form").addEventListener("submit", function (event) {
        var semesterField = document.getElementById("semester");
        if (!semesterField.value || semesterField.value <= 0) {
            event.preventDefault();  // Prevent form submission
            alert("Please enter a valid semester value.");
            semesterField.focus();  // Focus on the input field
        }
    });
</script>



</html>