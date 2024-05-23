<?php

// Include the database connection functions
require_once("db.php");
require_once("config.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Get the menu ID from the query parameters
$unit_id = isset($_GET['unit_id']) ? $_GET['unit_id'] : null;

// Check if unit ID is provided
if ($unit_id !== null) {
  // Prepare the query to fetch indicators for the provided unit ID
  $query = "SELECT id, name
              FROM simrs_menu 
              WHERE unit_id = :unit_id";

  // Execute the query
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':unit_id', $unit_id, PDO::PARAM_INT);
  $stmt->execute();

  // Fetch data as associative array
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Output data as JSON
  header('Content-Type: application/json');
  echo json_encode($data);

  // Exit script
  exit;
} else {
  // Handle case when unit ID is not provided in the query parameters
  http_response_code(400); // Bad request
  echo json_encode(array("error" => "Unit ID is required"));

  // Exit script
  exit;
}
