<?php
session_start();
$student_id = $_SESSION['student_id'];

$short_name = $_SESSION['short_name'];
$session = $_SESSION['session'];
$name = $_SESSION['name'];



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background-color: #f9f9f9; color: #333;">
    <div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #fff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <h1 style="text-align: center; color: #4CAF50;">Student Information</h1>
        <p style="font-size: 1.2em;">
            Hi, <strong><?php echo htmlspecialchars($name); ?></strong> - 
            <strong><?php echo htmlspecialchars($student_id); ?></strong>.
        </p>
        <p style="font-size: 1.2em;">
            Your current session is <strong><?php echo htmlspecialchars($session); ?></strong> 
            and Faculty is <strong><?php echo htmlspecialchars($short_name); ?></strong>.
        </p>
        <p style="font-size: 1.1em; color: #d9534f; font-weight: bold;">
            In this Faculty, according to the session, the Project Room is closed. Please contact your teacher.
        </p>
        <div style="text-align: center; margin-top: 20px;">
            <a href="https://nubps.xyz/student/dashboard" 
               style="display: inline-block; text-decoration: none; padding: 10px 20px; background-color: #4CAF50; color: #fff; border-radius: 5px; font-size: 1em; font-weight: bold;">
               Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
