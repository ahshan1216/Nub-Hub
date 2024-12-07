<?php
// Function to search GitHub repositories
function searchGithubRepos($query) {
    // Encode the search query for the URL
    $query = urlencode($query);
    
    // GitHub API URL
    $url = "https://api.github.com/search/repositories?q=$query";
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-Client'); // GitHub requires a user-agent
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    // Execute cURL and get the result
    $response = curl_exec($ch);
    
    // Check if the request was successful
    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    
    // Close cURL
    curl_close($ch);
    
    // Decode the JSON response
    $data = json_decode($response, true);
    
    // Return the search results
    return $data['items'];
}

// Handle form submission
if (isset($_POST['search'])) {
    $searchQuery = $_POST['searchQuery'];
    $results = searchGithubRepos($searchQuery);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search GitHub Projects</title>
</head>
<body>
    <h1>Search GitHub Projects</h1>
    
    <form method="POST">
        <input type="text" name="searchQuery" placeholder="Enter project name..." required>
        <button type="submit" name="search">Search</button>
    </form>
    
    <?php if (isset($results)): ?>
        <h2>Search Results:</h2>
        <ul>
            <?php foreach ($results as $repo): ?>
                <li>
                    <a href="<?= $repo['html_url']; ?>" target="_blank"><?= $repo['full_name']; ?></a><br>
                    Description: <?= $repo['description']; ?><br>
                    Stars: <?= $repo['stargazers_count']; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
