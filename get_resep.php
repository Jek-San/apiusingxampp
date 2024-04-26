<?php
require("config.php");

// Query to retrieve users with optional search parameter
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM resep_obat";
if (!empty($searchQuery)) {
  $searchQuery = $mysqli->real_escape_string($searchQuery);
  $query .= " WHERE no_resep LIKE '%$searchQuery%'";
} else {
  // Limit the query to 20 records if no search query is provided
  $query .= " LIMIT 20";
}

// Execute query
$result = $mysqli->query($query);

// Check if query was successful
if ($result) {
  // Fetch result as associative array
  $reseps = array();
  while ($row = $result->fetch_assoc()) {
    $reseps[] = $row;
  }

  // Return JSON response
  header('Content-Type: application/json');
  echo json_encode($users);
} else {
  // Error handling
  echo "Failed to retrieve users";
}

// Close database connection
$mysqli->close();
