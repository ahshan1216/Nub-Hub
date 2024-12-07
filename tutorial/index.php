<?php
session_start();
include '../database.php'; // Include database connection

// Check if the user is logged in and retrieve their role from the session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$short_name = isset($_SESSION['short_name']) ? $_SESSION['short_name'] : null;

// Fetch all tutorial links from the database
$query = "SELECT * FROM tutorials ORDER BY created_at DESC";
$result = $connection->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial Links - Dashboard</title> <!-- Page Title Added Here -->
    <style>
        /* Basic styling for the tutorial list and modal */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        .tutorial-list {
            margin: 20px 0;
        }

        .tutorial-item {
            background-color: #fff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .tutorial-item a {
            color: #007bff;
            text-decoration: none;
        }

        .tutorial-item a:hover {
            text-decoration: underline;
        }

        .tutorial-item button {
            margin-left: 20px;
            padding: 5px 10px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .tutorial-item button:hover {
            background-color: #ff1a1a;
        }

        .add-link-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .add-link-btn:hover {
            background-color: #45a049;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
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

        .modal-content input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .modal-content button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal-content button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h1>Tutorial Links</h1>

<?php if ($role === 'faculty' || $role === 'teacher'): ?>
    <!-- Show Add New Link button if the user is a faculty or teacher -->
    <button class="add-link-btn" onclick="openModal()">Add New Link</button>
<?php endif; ?>

<div class="tutorial-list">
    <?php
    if ($result->num_rows > 0) {
        // Loop through the tutorials and display them
        while ($row = $result->fetch_assoc()) {
            echo "
            <div class='tutorial-item'>
                <a href='" . htmlspecialchars($row['link']) . "' target='_blank'>" . htmlspecialchars($row['title']) . "</a>"; // Display tutorial title here

            // Show delete button if user is faculty or teacher
            if ($role === 'faculty' || $role === 'teacher') {
                echo "<button onclick='deleteTutorial(" . $row['id'] . ")'>Delete</button>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>No tutorials available.</p>";
    }
    ?>
</div>

<!-- Modal to add new tutorial link -->
<div id="tutorialModal" class="modal">
    <div class="modal-content">
        <h2>Add New Tutorial Link</h2>
        <form id="tutorialForm">
            <input type="text" id="tutorialTitle" name="title" placeholder="Enter tutorial title" required>
            <input type="url" id="tutorialLink" name="link" placeholder="Enter tutorial link" required>
            <button type="submit">Submit</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
// Open the modal
function openModal() {
    document.getElementById('tutorialModal').style.display = 'flex';
}

// Close the modal
function closeModal() {
    document.getElementById('tutorialModal').style.display = 'none';
}

// Handle form submission (AJAX to submit the new tutorial link)
document.getElementById('tutorialForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent page refresh

    var title = document.getElementById('tutorialTitle').value;
    var link = document.getElementById('tutorialLink').value;

    // Create a new AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_tutorial.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            // On success, reload the page to show the new tutorial link
            window.location.reload();
        } else {
            alert("Error adding tutorial.");
        }
    };
    xhr.send('title=' + encodeURIComponent(title) + '&link=' + encodeURIComponent(link));
});

// Delete tutorial link
function deleteTutorial(id) {
    if (confirm('Are you sure you want to delete this tutorial?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_tutorial.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                // On success, reload the page to remove the deleted tutorial
                window.location.reload();
            } else {
                alert("Error deleting tutorial.");
            }
        };
        xhr.send('id=' + id);
    }
}
</script>

</body>
</html>
