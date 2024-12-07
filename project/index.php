<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NUB HUB</title>
  <style>
    /* General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f9;
      color: #333;
      line-height: 1.6;
      padding: 20px;
    }

    header {
      text-align: center;
      margin-bottom: 20px;
      position: relative;
    }

    header h1 {
      color: #222;
      font-size: 2rem;
      margin-bottom: 10px;
    }

    .back-button {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 10px 15px;
      border-radius: 4px;
      font-size: 0.9rem;
      cursor: pointer;
      text-decoration: none;
    }

    .back-button:hover {
      background-color: #0056b3;
    }

    .search-container {
      text-align: center;
      margin-bottom: 20px;
    }

    .search-container input[type="text"] {
      width: 60%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    main {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }

    .card {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 300px;
      padding: 20px;
      transition: transform 0.2s ease-in-out;
      text-align: center;
    }

    .card:hover {
      transform: scale(1.05);
    }

    .card a {
      text-decoration: none;
      color: #007bff;
      font-size: 1.2rem;
    }

    .card p {
      font-size: 0.9rem;
      margin-bottom: 10px;
      color: #555;
    }

    .tag {
      display: inline-block;
      margin-right: 5px;
      padding: 3px 10px;
      font-size: 0.8rem;
      border-radius: 12px;
      background: #eee;
      color: #555;
    }

    .tag.public {
      background: #28a745;
      color: #fff;
    }

    .date {
      display: block;
      margin-top: 10px;
      font-size: 0.8rem;
      color: #666;
    }
  </style>
</head>
<body>
  <header>
    <a href="../" class="back-button">← Back</a>
    <h1>NUB HUB</h1>
  </header>
  <div class="search-container">
    <input type="text" id="search-box" placeholder="Search by project name or student ID...">
  </div>
  <main id="results">
    <!-- Dynamic results will be displayed here -->
  </main>

  <script>
  // Get the query parameter from the URL
  const urlParams = new URLSearchParams(window.location.search);
  const searchQuery = urlParams.get('search') || ''; // Default to empty string if not provided

  // Pre-fill the search box with the query
  const searchBox = document.getElementById('search-box');
  searchBox.value = searchQuery;

  const resultsContainer = document.getElementById('results');

  // Function to fetch projects based on the search term
  function fetchProjects(searchTerm = '') {
    fetch(`search.php?search=${encodeURIComponent(searchTerm)}`)
      .then(response => response.text())
      .then(data => {
        resultsContainer.innerHTML = data;
      })
      .catch(error => {
        console.error('Error fetching search results:', error);
      });
  }

  // Fetch projects based on the initial query parameter
  fetchProjects(searchQuery);

  // Event listener for real-time search
  searchBox.addEventListener('input', function () {
    const searchTerm = searchBox.value;
    fetchProjects(searchTerm); // Fetch results based on the search term
  });
</script>

</body>
</html>
