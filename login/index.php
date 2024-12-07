<?php
// Include the database connection
include '../database.php';

session_start();
// If the user is already logged in, redirect them to their role-based dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'teacher') {
        header("Location: ../teacher/");
        exit;
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: ../student/");
        exit;
    }
}
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? null;

    if ($action == "signin") {
        $email = $_POST['email'];
        $password = md5($_POST['password']); // Hash the input password with md5

        // Query to check the user credentials
        $query = "SELECT id, password, role,active,name FROM users WHERE email = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $stored_password, $role, $active, $name);
            $stmt->fetch();
            if ($active == 1) {
                if ($password === $stored_password) {
                    // Redirect based on role
                    if ($role === "teacher") {
                        // Assume you already have a database connection in $conn
                        $query = "SELECT short_name FROM teachers WHERE user_id = ?";
                        $stmt = $connection->prepare($query);
                        $stmt->bind_param("i", $id); // Bind the $id parameter
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $short_name = $row['short_name'];

                            // Set session variables
                            $_SESSION['role'] = 'teacher';
                            $_SESSION['email'] = $email;
                            $_SESSION['id'] = $id;
                            $_SESSION['name'] = $name;
                            $_SESSION['short_name'] = $short_name; // Store short_name in the session

                            header("Location: ../teacher/");
                            exit;
                        } else {
                            // Handle the case where no matching ID is found
                            echo "No teacher found with the given ID.";
                            exit;
                        }
                    } elseif ($role === "student") {

                        // Query to fetch the id from the users table
                        $query1 = "SELECT id FROM users WHERE email = ?";
                        $stmt1 = $connection->prepare($query1);
                        $stmt1->bind_param("s", $email);
                        $stmt1->execute();
                        $stmt1->bind_result($id);
                        $stmt1->fetch();
                        $stmt1->close(); // Close the first statement



                        if ($id) {
                            // Query the students table using the ID
                            $query2 = "SELECT student_id, session, short_name,semester FROM students WHERE user_id = ?";
                            $stmt2 = $connection->prepare($query2);
                            $stmt2->bind_param("i", $id);
                            $stmt2->execute();
                            $stmt2->bind_result($student_id, $session, $short_name,$semister);
                            $stmt2->fetch();
                            $stmt2->close();

                            if ($student_id) {
                                $_SESSION['student_id'] = $student_id;
                                $_SESSION['session'] = $session;
                                $_SESSION['semester'] = $semister;
                                $_SESSION['role'] = 'student';
                                $_SESSION['name'] = $name;
                                $_SESSION['user_id'] = $id;
                                $_SESSION['short_name'] = $short_name;
                                error_log($student_id . $session );
                                // Check if the student is part of a team
                                $check_team_query = "SELECT * FROM student_team WHERE student_id = ? AND session = ? ";
                                $check_team_stmt = $connection->prepare($check_team_query);
                                $check_team_stmt->bind_param("ss", $student_id, $session);
                                $check_team_stmt->execute();
                                $check_team_result = $check_team_stmt->get_result();

                                if ($check_team_result->num_rows > 0) {
                                    $team_data = $check_team_result->fetch_assoc();
                                    $leader_id = $team_data['leader_id'];
                                    $_SESSION['leader_id'] = $leader_id;
                                        
                                    if ($student_id != $leader_id && empty($short_name)) {
                                        $_SESSION['short_name'] = '***';
                                        // Fetch leader's user_id from students table
                                        $fetch_user_id_query = "SELECT user_id FROM students WHERE student_id = ?";
                                        $fetch_user_id_stmt = $connection->prepare($fetch_user_id_query);
                                        $fetch_user_id_stmt->bind_param("s", $leader_id);
                                        $fetch_user_id_stmt->execute();
                                        $user_id_result = $fetch_user_id_stmt->get_result();

                                        if ($user_id_result->num_rows > 0) {
                                            $user_id_data = $user_id_result->fetch_assoc();
                                            $user_id = $user_id_data['user_id'];

                                            // Fetch leader's name from users table
                                            $fetch_name_query = "SELECT name FROM users WHERE id = ?";
                                            $fetch_name_stmt = $connection->prepare($fetch_name_query);
                                            $fetch_name_stmt->bind_param("i", $user_id);
                                            $fetch_name_stmt->execute();
                                            $name_result = $fetch_name_stmt->get_result();

                                            if ($name_result->num_rows > 0) {
                                                $name_data = $name_result->fetch_assoc();
                                                $leader_name = $name_data['name'];
                                            } else {
                                                $leader_name = "Unknown Leader";
                                            }
                                            $fetch_name_stmt->close();
                                        } else {
                                            $leader_name = "Unknown Leader";
                                        }

                                        $fetch_user_id_stmt->close();

                                        // Show confirmation prompt
                                        echo "<script>
                                                const studentId = '$student_id';
                                                
                                                
                                                const leader_id= '$leader_id';

                                                if (confirm('You are now in a team. The team leader is $leader_name ($leader_id). If you want to stay, click OK. Otherwise, click Cancel to remove yourself from the team.')) {
                                                    // Proceed with the team
                                                    fetch('insert_user.php', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                        },
                                                        body: JSON.stringify({
                                                            leader_id: leader_id,
                                                            
                                                            student_id: studentId
                                                        }),
                                                    })
                                                    .then(response => response.text())
                                                    .then(data => {
                                                        alert(data);
                                                        window.location.href = '../student/'; // Redirect to the desired URL
                                                    })
                                                    .catch(error => {
                                                        console.error('Error:', error);
                                                    });
                                                } else {
                                                    const userInput = prompt('Type REMOVE to confirm removal from the team:');
                                                    if (userInput === 'REMOVE') {
                                                        fetch('remove_team.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                            },
                                                            body: JSON.stringify({
                                                                student_id: studentId,
                                                                session: session
                                                                
                                                            }),
                                                        })
                                                        .then(response => response.text())
                                                        .then(data => {
                                                            alert(data);
                                                            window.location.href = '../student/'; // Redirect to the desired URL
                                                        })
                                                        .catch(error => {
                                                            console.error('Error:', error);
                                                        });
                                                    }
                                                }
                                            </script>";
                                    } else {
                                        header("Location: ../student/");
                                        exit;
                                    }
                                } else {
                                    // If the student is not part of a team, redirect normally
                                    header("Location: ../student/");
                                    exit;
                                }
                            } else {
                                echo "No student ID found for the given user ID.";
                            }
                        }


                    }
                } else {
                    $error_message = "Your password is wrong.";
                }
            } else {
                $error_message = "Your Account is InActive";
            }
        } else {
            $error_message = "Your email  is wrong.";
        }
    } else {

        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = md5($_POST['password']); // Hash password with md5
        $role = $_POST['role'];


        $check_query = "SELECT * FROM users WHERE email = ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "Error: Duplicate email  found.";
        } else {



            if ($role == "student") {

                $student_id = $_POST['student_id'];
                $session = $_POST['session1'];
                $semester = $_POST['semester'];







                // Check for duplicate student_id in the "students" table
                $check_student_query = "SELECT * FROM students WHERE student_id = ?";
                $check_student_stmt = $connection->prepare($check_student_query);
                $check_student_stmt->bind_param("s", $student_id);
                $check_student_stmt->execute();
                $check_student_result = $check_student_stmt->get_result();

                if ($check_student_result->num_rows > 0) {
                    echo "Error: Duplicate student ID found.";
                } else {

                    $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
                    $stmt = $connection->prepare($query);
                    $stmt->bind_param("ssss", $name, $email, $password, $role);
                    if ($stmt->execute()) {
                        $user_id = $stmt->insert_id;
                        $student_query = "INSERT INTO students (user_id, student_id, session, semester) VALUES (?, ?, ?, ?)";
                        $student_stmt = $connection->prepare($student_query);
                        $student_stmt->bind_param("isss", $user_id, $student_id, $session, $semester);
                        $student_stmt->execute();
                        echo "Student registered successfully!";
                    } else {
                        echo "Error: " . $stmt->error;
                    }
                }








            } elseif ($role == "teacher") {
                $teacher_short_name = $_POST['teacher_short_Name'];
                // Check for duplicate student_id in the "students" table
                $check_teacher_query = "SELECT * FROM teachers WHERE short_name = ?";
                $check_teacher_stmt = $connection->prepare($check_teacher_query);
                $check_teacher_stmt->bind_param("s", $teacher_short_name);
                $check_teacher_stmt->execute();
                $check_teacher_result = $check_teacher_stmt->get_result();

                if ($check_teacher_result->num_rows > 0) {
                    echo "Error: Duplicate teacher ID found.";
                } else {
                    $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
                    $stmt = $connection->prepare($query);
                    $stmt->bind_param("ssss", $name, $email, $password, $role);
                    if ($stmt->execute()) {
                        $user_id = $stmt->insert_id;
                        $teacher_query = "INSERT INTO teachers (user_id, short_name) VALUES (?, ?)";
                        $teacher_stmt = $connection->prepare($teacher_query);
                        $teacher_stmt->bind_param("is", $user_id, $teacher_short_name);
                        $teacher_stmt->execute();
                        echo "Teacher registered successfully!";
                    } else {
                        echo "Error: " . $stmt->error;
                    }
                }
            }

        }



    }
}
?>


<html>

<head>
    <link rel="stylesheet" href="./style.css">
    <style>
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form role="form text-left" id="sessionForm" method="post" action="index.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="signup">
                <div class="mb-3">

                    <input type="text" class="form-control" name="name" id="name" placeholder="Name" aria-label="Name"
                        required>
                </div>
                <div class="mb-3">

                    <input type="email" class="form-control" name="email" id="email" placeholder="Email"
                        aria-label="Email" required>
                </div>
                <div class="mb-3">

                    <input type="password" class="form-control" name="password" id="password" placeholder="Password"
                        aria-label="Password" required>
                </div>
                <div class="mb-3">
                    <label for="role">Role:</label>
                    <select id="role" name="role" class="form-select" onchange="showForm()" required>
                        <option value="">Select Option</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div id="studentForm" style="display: none;">
                    <div class="mb-3">

                        <input type="number" class="form-control" name="student_id" id="student_id"
                            placeholder="Student ID" aria-label="Student ID" required>
                    </div>


                    <div class="mb-3">
                        <small id="sessionHint" style="color: red;"></small>
                        <input type="text" class="form-control" name="session1" id="session"
                            placeholder="Session (e.g., spring24)" aria-label="sess" required>


                    </div>
                    <div class="mb-3">

                        <input type="number" class="form-control" name="semester" id="semester" placeholder="Semester"
                            aria-label="Semester" required>
                    </div>

                </div>
                <div id="teacherForm" style="display: none;">
                    <div class="mb-3">

                        <input type="text" class="form-control" name="teacher_short_Name" id="teacher_short_Name"
                            placeholder="Teacher’s Name (Acronym):" aria-label="teacher_short_Name" required>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" id="submitButton" class="btn bg-gradient-dark w-100 my-4 mb-2" disabled
                        style="background-color: #ccc; cursor: not-allowed; border: none;">Sign up</button>
                </div>

            </form>
        </div>
        <div class="form-container sign-in-container">

            <form role="form text-left" id="signinForm" method="post" action="index.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="signin">
                <h1>Sign in</h1>

                <span>or use your account</span>
                <?php if (!empty($error_message)): ?>
                    <p class="error-message"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <input type="email" class="form-control" name="email" id="email" placeholder="Email" aria-label="Email">
                <input type="password" class="form-control" name="password" id="password" placeholder="Password"
                    aria-label="Password" required>
                <!-- <a href="#">Forgot your password?</a> -->
                <button>Sign In</button>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please login with your personal info</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details and start journey with us</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>



</body>
<script src="script.js"></script>
<script>
    function showForm() {
        var role = document.getElementById('role').value;
        var studentForm = document.getElementById('studentForm');
        var teacherForm = document.getElementById('teacherForm');
        const container = document.getElementById("container");

        // Reset required attribute for all fields
        var allInputs = document.querySelectorAll('input, select');
        allInputs.forEach(input => {
            input.removeAttribute('required');
        });

        // Reset the styles to the default when no role is selected


        if (role === 'student') {
            studentForm.style.display = 'block';
            teacherForm.style.display = 'none';
            container.style.minHeight = "658px";
            container.style.marginTop = "115px";
            // Set required attribute for student fields
            var studentInputs = studentForm.querySelectorAll('input, select');
            studentInputs.forEach(input => {
                input.setAttribute('required', true);
            });
        } else if (role === 'teacher') {
            studentForm.style.display = 'none';
            teacherForm.style.display = 'block';
            container.style.minHeight = "550px";
            container.style.marginTop = "50px";
            // Set required attribute for teacher fields
            var teacherInputs = teacherForm.querySelectorAll('input, select');
            teacherInputs.forEach(input => {
                input.setAttribute('required', true);
            });
        }
    }

</script>
<script>
    document.getElementById('session').addEventListener('input', function () {
        const inputField = this;
        const value = inputField.value.trim().toLowerCase();
        const hint = document.getElementById('sessionHint');
        const submitButton = document.getElementById('submitButton');
        const role = document.getElementById('role').value; // Get selected role

        // Apply validation only if role is "student"
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

    // Prevent form submission if button is disabled
    document.getElementById('sessionForm').addEventListener('submit', function (e) {
        const submitButton = document.getElementById('submitButton');
        const role = document.getElementById('role').value;

        if (role === "student" && submitButton.disabled) {
            e.preventDefault(); // Prevent form submission
            alert('Please correct the session field before submitting.');
        }
    });
</script>

</html>