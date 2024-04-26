<?php

// Include the database connection functions
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Construct the base SQL query
$query = "SELECT * FROM simrs_unit WHERE 1";

// Check if the 'id' parameter is provided in the GET request
if (isset($_GET['id'])) {
  // Add a WHERE clause to filter by ID
  $query .= " AND id = :id";
}

// Prepare the query
$stmt = $pdo->prepare($query);

// Bind parameters if necessary
if (isset($_GET['id'])) {
  $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
}

// Execute the query
$stmt->execute();

// Fetch data as associative array
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output data as JSON
header('Content-Type: application/json');
echo json_encode($data);
