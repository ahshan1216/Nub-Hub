<?php
session_start();
include '../../database.php';

require_once("cPanelApi.php");
$api = new cPanelApi("grohonn.com", "grohonnc", "Fzdhg6ZIW53#6(");



$student_id = $_SESSION['student_id'];

$ql3 = "SELECT * FROM students WHERE student_id = '$student_id'";
$esult3 = $connection->query($ql3);

if ($esult3->num_rows > 0) {
    // Fetch the result
    $ow3 = $esult3->fetch_assoc();


    $session = $ow3['session'];
    $short_name = $ow3['short_name'];
    $active= $ow3['active'];
}





// Fetch leader_id from student_team table
$team_query = "SELECT leader_id FROM student_team WHERE student_id = ?";
$team_stmt = $connection->prepare($team_query);
$team_stmt->bind_param('s', $student_id);
$team_stmt->execute();
$team_result = $team_stmt->get_result();

if ($team_result->num_rows > 0) {
    $team_row = $team_result->fetch_assoc();
    $leader_id = $team_row['leader_id'];

}







$slot_query = "SELECT project_open FROM teacher_slot WHERE short_name = ? AND session = ?";
$slot_stmt = $connection->prepare($slot_query);
$slot_stmt->bind_param('ss', $short_name, $session);
$slot_stmt->execute();
$slot_result = $slot_stmt->get_result();

if ($slot_result->num_rows > 0) {
    $slot_row = $slot_result->fetch_assoc();
    $project_open = $slot_row['project_open'];

    if ($project_open == 1 && $active == 1) {

        $prefix = "grohonnc_";
        $suffix = $leader_id;
        $databaseName = $prefix . $suffix;

        $response = $api->checkDataBaseMySQL($databaseName);

        // Decode the JSON response
        $responseArray = json_decode($response, true);

        if (!$responseArray['status']) {
            function generateRandomPassword($length = 14)
            {
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
                $randomPassword = '';
                $maxIndex = strlen($characters) - 1;

                for ($i = 0; $i < $length; $i++) {
                    $randomPassword .= $characters[random_int(0, $maxIndex)];
                }

                return $randomPassword;
            }

            $site_url1 = $_SESSION['full_site_ur'];
            $database_file_size = $_SESSION['size1'];
            $file_size_in_mb = floor($database_file_size / (1024 * 1024)); // Convert to MB and round down
            $randomPassword = generateRandomPassword(12);
            $api->createDataBaseMySQL($databaseName);
            $api->createUserMySQL($databaseName, $randomPassword);
            $api->setPrivilegesMySQL($databaseName, $databaseName);

            $api->ftpCreate($leader_id, $randomPassword, $file_size_in_mb);
            $api->ftpHomeDir($leader_id, "/public_html/subdomainmy/nubps.xyz/project/project_file/$site_url1");
            // Update the FTP password in the students table


            $updateQuery = "UPDATE students SET ftp_pass = ? WHERE student_id = ?";
            $updateStmt = $connection->prepare($updateQuery);

            // Bind parameters (s = string, i = integer)
            $updateStmt->bind_param("ss", $randomPassword, $leader_id);

            // Execute the query and check the result
            if ($updateStmt->execute()) {
                // echo "FTP password updated successfully.<br>";
                $main_domain = $_SERVER['HTTP_HOST'];
                $weblink = $main_domain.'/project/project_file/'.$site_url1;
            } else {
                echo "Error updating FTP password: " . $connection->error;
            }

            // Close the update statement
            $updateStmt->close();



        } else {
            // Fetch the updated data from the students table
            $fetchQuery = "SELECT * FROM students WHERE student_id = ?";
            $fetchStmt = $connection->prepare($fetchQuery);

            // Bind the parameter (i = integer)
            $fetchStmt->bind_param("i", $leader_id);

            // Execute the query and get the result
            if ($fetchStmt->execute()) {
                $result = $fetchStmt->get_result();

                // Fetch the data as an associative array
                if ($studentData = $result->fetch_assoc()) {

                    $randomPassword = $studentData['ftp_pass'];
                    $site_url1 = $_SESSION['full_site_ur'];
                    $main_domain = $_SERVER['HTTP_HOST'];
                    $weblink = $main_domain.'/project/project_file/'.$site_url1;
                    // Add other fields you want to display
                } else {
                    echo "No data found for student ID: $leader_id.";
                }
            } else {
                echo "Error fetching data: " . $connection->error;
            }

            // Close the fetch statement
            $fetchStmt->close();

        }

    } else {


        header("Location: restricted.php");

        exit;
    }
} else {
    die(json_encode(['success' => false, 'message' => 'No matching teacher slot found']));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credentials</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            font-size: 1.5rem;
            color: #444;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h2 {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 10px;
        }

        .credentials {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
        }

        .footer {
            text-align: center;
            font-size: 0.8rem;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Your Credentials</h1>
        <div class="section">
            <h2>Database Credentials:</h2>
            <p style="font-size: 14px; margin-bottom: 15px;">Your Website link is 
        <a href="<?php echo 'https://'.$weblink; ?>" style="color: #007bff; text-decoration: none;"><?php echo $weblink; ?></a>
    </p>
            <div class="credentials">
                $servername = "localhost";<br>
                $username = "<?php echo $databaseName ?>";<br>
                $password = "<?php echo $randomPassword ?>";<br>
                $dbname = "<?php echo $databaseName ?>";
            </div>
        </div>
        <div class="section">
            <h2>FTP Credentials:</h2>
            <div class="credentials">
                hostname = ftp.grohonn.com<br>
                ftp port = 21<br>
                username = <?php echo $leader_id ?>@grohonn.com<br>
                password = <?php echo $randomPassword ?>
            </div>
        </div>
        <div class="footer">
            This information is confidential. Please keep it secure.
        </div>
    </div>
</body>

</html>