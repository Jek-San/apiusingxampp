<?php
require_once("config.php");
require_once('db.php');

// Establish database connection using PDO
$pdo = connectToDatabase();

// Get unit ID from the query parameters
$unit_id = isset($_GET['unit_id']) ? $_GET['unit_id'] : null;

// Check if unit ID is provided
if ($unit_id !== null) {
  // Prepare the query to fetch menus and their associated indicators for the provided unit ID
  $query = "SELECT m.id AS id, m.name AS name 
              FROM simrs_unit u 
              JOIN simrs_menu m ON u.id = m.unit_id 
              WHERE u.id = :unit_id";

  // Execute the query
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':unit_id', $unit_id, PDO::PARAM_INT);
  $stmt->execute();

  // Fetch data as associative array
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Output data as JSON
  header('Content-Type: application/json');
  echo json_encode($result);
} else {
  // Handle case when unit ID is not provided in the query parameters
  http_response_code(400); // Bad request
  echo json_encode(array("error" => "Unit ID is required"));
}
