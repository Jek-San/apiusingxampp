<?php
require("config.php");
// Query to retrieve data with optional search parameter
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$noRawat = isset($_GET['noRawat']) ? $_GET['noRawat'] : '';
if (!empty($noRawat)) {
  $noRawat = $mysqli->real_escape_string($noRawat);
}

$query = "SELECT simrs_foto_resep.* FROM simrs_foto_resep WHERE simrs_foto_resep.no_rawat = '$noRawat'"; // Using parameterized query to avoid SQL injection

// Execute query
$result = $mysqli->query($query);

// Check if query was successful
if ($result) {
  // Fetch result as associative array
  $data = array();
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  // Return JSON response
  header('Content-Type: application/json');
  echo json_encode($data);
} else {
  // Error handling
  echo "Failed to retrieve data: " . $mysqli->error;
}

// Close database connection
$mysqli->close();
