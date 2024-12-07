<?php
// Start the session
session_start();
include '../database.php';
// Check if the user is logged in and has the "student" role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    // Redirect to the login page if the user is not a student or not logged in
    header("Location: ../index.php");
    exit;
}
if($_SESSION['short_name'] == '***')
{
    echo '
<div style="font-family: Arial, sans-serif; padding: 20px; margin: 20px auto; max-width: 400px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; text-align: center; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
        Someone requested you as a team. Please logout.
    </p>
    <a href="../logout.php" style="text-decoration: none;">
        <button style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer;">
            Logout
        </button>
    </a>
</div>';
exit;
}
// Start the session and retrieve the email
$student_id = $_SESSION['student_id'];
$session = $_SESSION['session'];
$name = $_SESSION['name'];
$semester = $_SESSION['semester'];

// Check if the student already exists in the student_team table
$checkQuery = "SELECT COUNT(*) AS count FROM student_team WHERE student_id = ?";
$stmt = $connection->prepare($checkQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Redirect to the dashboard if the student is already in the table
if ($row['count'] > 0) {
    header("Location: /student/dashboard");
    exit;
}




// Fetch faculty list where open is 1
$query = "SELECT short_name,total_team FROM teacher_slot WHERE open = 1 AND session='$session' ";
$result = $connection->query($query);


// Generate options
$facultyOptions = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $facultyOptions .= "<div class='faculty-box' data-name='" . htmlspecialchars($row['short_name']) . "'>" . htmlspecialchars($row['short_name']) . "</div>";
    }
    $hasActiveFaculty = true;
} else {
    $facultyOptions = "<div class='faculty-box'>No active faculty available</div>";
}



$photoDir = 'assets/student_photo/';
$idCardDir = 'assets/student_idcard/';

// Initialize variables for file paths
$profilePicturePath = '';
$idCardPicturePath = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Profile Picture Upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profilePictureName = $student_id . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $profilePicturePath = $photoDir . $profilePictureName;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profilePicturePath);
    }

    // Handle ID Card Picture Upload
    if (isset($_FILES['id_card_picture']) && $_FILES['id_card_picture']['error'] == 0) {
        $idCardPictureName = $student_id . '.' . pathinfo($_FILES['id_card_picture']['name'], PATHINFO_EXTENSION);
        $idCardPicturePath = $idCardDir . $idCardPictureName;
        move_uploaded_file($_FILES['id_card_picture']['tmp_name'], $idCardPicturePath);
    }

    // Get the selected faculty
    $selectedFaculty = $_POST['selected_faculty']; // Assuming you have a field with name="faculty"
    $_SESSION['short_name'] = $selectedFaculty;

    // Update the `short_name` in the `students` table
    $updateStmt = $connection->prepare("UPDATE students SET short_name = ? WHERE student_id = ? AND session = ?");
    $updateStmt->bind_param("sss", $selectedFaculty, $student_id, $session);

    if ($updateStmt->execute()) {
        echo "short_name updated successfully in students.";
    } else {
        echo "Error updating short_name in students: " . $updateStmt->error;
    }

    $updateStmt->close();




    // Insert the leader into `student_team`
    $stmt = $connection->prepare("INSERT INTO student_team (leader_id, student_id, student_name, session,semester,short_name) VALUES (?, ?, ?, ?,?,?)");
    $stmt->bind_param("ssssss", $student_id, $student_id, $name, $session, $semester, $selectedFaculty); // Leader is their own student_id
    if ($stmt->execute()) {
        echo "Leader $name added successfully.<br>";
         // Define the folder path
    $folderPath = "../project/project_file/{$student_id}$session";

    // Check if the folder already exists, if not, create it
    if (!is_dir($folderPath)) {
        if (mkdir($folderPath, 0777, true)) { // 0777 gives full permissions; true enables recursive creation
            echo "Folder for student ID $student_id created successfully.<br>";
        } else {
            echo "Failed to create folder for student ID $student_id.<br>";
        }
    } else {
        echo "Folder for student ID $student_id already exists.<br>";
    }
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


            $userIdQuery = "SELECT user_id FROM students WHERE student_id = ?";
            $userStmt = $connection->prepare($userIdQuery);
            $userStmt->bind_param("s", $memberId);
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
        
                



            $stmt = $connection->prepare("INSERT INTO student_team (leader_id, student_id, student_name, session,semester,short_name) VALUES (?, ?, ?,?, ?,?)");
            $stmt->bind_param("ssssss", $student_id, $memberId, $name, $session, $semester, $selectedFaculty);
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
        <p>
    If You Think You Register Wrong Session and Semester. 
    Just <a href="delete_account.php" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">Click here to delete</a>
</p>

        <ul id="progressbar">
            <li class="active">Personal Details</li>
            <li>Account Setup</li>
            <li>Team Details</li>
            <a href="../../logout.php">
                <i class="fa fa-person-running nav-icon"></i>
                <span class="nav-text">Logout</span>
            </a>
        </ul>

        <!-- fieldsets -->
        <fieldset>
            <h2 class="fs-title">Account Validation</h2>
            <h3 class="fs-subtitle">This is step 1</h3>

            <div class="upload-grid">
                <!-- Profile Picture Upload -->
                <div class="upload-item">
                    <label for="profile_picture">Profile Picture:</label>
                    <input type="file" name="profile_picture" id="profile_picture" accept=".jpg" required />
                    <div id="profile_preview" class="image-preview">
                        <img id="profile_img" src="" alt="Profile Preview" />
                    </div>
                </div>

                <!-- ID Card Picture Upload -->
                <div class="upload-item">
                    <label for="id_card_picture">ID Card Picture:</label>
                    <input type="file" name="id_card_picture" id="id_card_picture" accept=".jpg" required />
                    <div id="id_card_preview" class="image-preview">
                        <img id="id_card_img" src="" alt="ID Card Preview" />
                    </div>
                </div>
            </div>

            <input type="button" name="next" class="next action-button" value="Next" disabled />
        </fieldset>

        <fieldset class="fieldset1">
            <h2 class="fs-title">Choice Active Faculty</h2>
            <h3 class="fs-subtitle">Contact With Faculty then Select</h3>
            <input type="hidden" name="selected_faculty" id="selected_faculty" />

            <!-- Faculty Boxes -->
            <div class="faculty-container">
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
<script>

    // Select Faculty Box and Toggle Background Color
    const facultyBoxes = document.querySelectorAll('.faculty-box');
    // Get the hidden input to store the selected faculty
    const selectedFacultyInput = document.getElementById('selected_faculty');
    const teamInputFields = $('#team-input-fields');

    const nextButton = document.getElementById('nextButton');

    // Disable the Next button by default
    nextButton.disabled = true;




    facultyBoxes.forEach(box => {
        box.addEventListener('click', function () {
            // Deselect other boxes
            facultyBoxes.forEach(b => b.classList.remove('selected'));

            // Select the clicked box
            this.classList.add('selected');
            // Set the hidden input value to the faculty selected
            selectedFacultyInput.value = this.getAttribute('data-name');
            shortName = selectedFacultyInput.value;

            // Enable the Next button after selection
            nextButton.disabled = false;
            nextButton.style.pointerEvents = 'auto';
            nextButton.style.opacity = '1';
            $.ajax({
                url: 'fetch_total_team.php',
                method: 'POST',
                data: { short_name: shortName },
                dataType: 'json',
                success: function (response) {
                    if (response.total_team) {
                        teamInputFields.empty(); // Clear previous inputs
console.log(response.total_team);
                        const totalTeam = response.total_team;
                        for (let i = 1; i < totalTeam; i++) {
                            $('#team-input-fields').append(`
        <div class="team-member-fields">
            <input type="text" name="team_member_${i}_name" placeholder="Team Member ${i + 1} Name" >
            <input type="number" name="team_member_${i}_id" placeholder="Team Member ${i + 1} ID" >
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





        });
    });

    // Validate Selection before Submit
    function validateSelection() {
        const selectedFaculty = document.querySelector('.faculty-box.selected');
        if (!selectedFaculty) {

            alert("Please select a faculty before submitting.");
            return false;
        }

        // Optionally, you can retrieve the selected faculty name:
        const selectedFacultyName = selectedFaculty.getAttribute('data-name');
        console.log("Selected Faculty: " + selectedFacultyName); // You can use this for further processing.

        return true; // Proceed to submit
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get the file input elements and the Next button
        const profilePicture = document.getElementById('profile_picture');
        const idCardPicture = document.getElementById('id_card_picture');
        const nextButton = document.querySelector('.next');

        // Function to check if both files are selected
        function checkFiles() {
            // If both files are selected, enable the Next button
            if (profilePicture.files.length > 0 && idCardPicture.files.length > 0) {
                nextButton.disabled = false;
            } else {
                nextButton.disabled = true;
            }
        }

        // Add event listeners to both file input fields to trigger the check
        profilePicture.addEventListener('change', checkFiles);
        idCardPicture.addEventListener('change', checkFiles);

        // Initial check in case the user has already selected files
        checkFiles();
    });






    // Function to preview an image
    function previewImage(input, imgElementId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById(imgElementId);
                img.src = e.target.result;
                img.style.display = "block"; // Show the image
            };
            reader.readAsDataURL(input.files[0]); // Read the file
        }
    }

    // Event listeners for file inputs
    document.getElementById("profile_picture").addEventListener("change", function () {
        previewImage(this, "profile_img");
    });

    document.getElementById("id_card_picture").addEventListener("change", function () {
        previewImage(this, "id_card_img");
    });
</script>
<script>
    $(document).on('submit', '#msform', function (e) {
        e.preventDefault();

        // Collect team member IDs from the input fields
        const teamMemberIds = [];
        const selectedFaculty = document.querySelector('.faculty-box.selected');
        const selectedFacultyName = selectedFaculty.getAttribute('data-name');

        $('#team-input-fields input[name*="_id"]').each(function () {
            teamMemberIds.push($(this).val());
        });

        // Send the IDs to the server via POST
        $.post(
            'check_team_member_ids.php',
            { ids: teamMemberIds, selectedFacultyName: selectedFacultyName },
            function handleResponse(response) {
                console.log(response);

                if (response.status === 'error') {
                    // Ensure nonExistent and duplicates are valid arrays
                    const nonExistentArray = Array.isArray(response.nonExistent) ? response.nonExistent : [];
                    const duplicatesArray = Array.isArray(response.duplicates) ? response.duplicates : [];
                    const conflictArray = Array.isArray(response.conflict) ? response.conflict : [];

                    const filteredNonExistent = nonExistentArray.filter(item => item.trim() !== "");
                    const filteredDuplicates = duplicatesArray.filter(item => item.trim() !== "");
                    const filteredConflicts = conflictArray.filter(item => item.trim() !== "");
                    if (filteredConflicts.length > 0) {
                        alert('Own ID is not allowed. If you need no members, then directly click submit.');
                        console.log('Conflict IDs (own ID):', filteredConflicts);
                    }

                    if (filteredNonExistent.length > 0) {
                        alert('The following IDs do not exist: ' + filteredNonExistent.join(', '));
                        console.log('Non-existent IDs:', filteredNonExistent);
                    }

                    if (filteredDuplicates.length > 0) {
                        alert('The following IDs are already in a group: ' + filteredDuplicates.join(', '));
                        console.log('Duplicate IDs:', filteredDuplicates);
                    }
                } else {
                    // If no errors, programmatically submit the form
                    const formElement = document.getElementById('msform');
                    if (formElement) {
                        HTMLFormElement.prototype.submit.call(formElement); // Ensure native submission
                    } else {
                        console.error('Form element with id "msform" not found!');
                    }
                }
            },
            'json'
        ).fail(function () {
            alert('An error occurred while checking IDs. Please try again.');
        });
    });
</script>


</html>