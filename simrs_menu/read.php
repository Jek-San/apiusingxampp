<?php

// Include the database connection functions
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Construct the base SQL query
$query = "SELECT m.id AS menu_id, m.name AS menu_name, u.id AS unit_id, u.name AS unit_name
FROM simrs_menu AS m
JOIN simrs_unit AS u ON m.unit_id = u.id
";

// Check if the 'id' parameter is provided in the GET request
if (isset($_GET['id'])) {
  // Add a WHERE clause to filter by ID
  $query .= " AND m.id = :id";
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
