<?php

// Function to search GitHub for a specific code snippet
function searchGithubCode($query) {
    // URL encode the query to handle special characters
    $query = urlencode($query);
    
    // GitHub API URL for searching code
    $url = "https://api.github.com/search/code?q=$query";
    
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-Client');  // Required by GitHub API
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    // Execute cURL and get the result
    $response = curl_exec($ch);
    
    // Check if there was an error in the cURL request
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    }
    
    // Close the cURL session
    curl_close($ch);
    
    // Decode the JSON response from GitHub
    $data = json_decode($response, true);
    
    // Return the search results (items that matched the query)
    return $data['items'] ?? [];
}

// Define parts of your HTML code snippet to search (using smaller pieces for better matches)
$codeSnippets = [
    '<meta charset="UTF-8" />',
    '<script type="module" src="/src/main.jsx"></script>',
    '<link rel="icon" href="/Logo.png">',
    '<title>Dev Search</title>'
];

// Loop through each code snippet and search for matches
foreach ($codeSnippets as $codeSnippet) {
    echo "Searching for snippet: $codeSnippet\n";
    
    // Search for the code snippet on GitHub
    $matches = searchGithubCode($codeSnippet);
    
    // Output the results
    if (count($matches) > 0) {
        echo "Found " . count($matches) . " matches on GitHub for: $codeSnippet\n";
        foreach ($matches as $match) {
            echo "Repository: " . $match['repository']['full_name'] . "\n";
            echo "File: " . $match['name'] . "\n";
            echo "URL: " . $match['html_url'] . "\n\n";
        }
    } else {
        echo "No matches found for: $codeSnippet\n\n";
    }
}

?>
