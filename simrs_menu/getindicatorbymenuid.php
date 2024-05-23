<?php

// Include the database connection functions
require_once("db.php");
require_once("config.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Get the menu ID from the query parameters
$menu_id = isset($_GET['menu_id']) ? $_GET['menu_id'] : null;

// Check if menu ID is provided
if ($menu_id !== null) {
  // Prepare the query to fetch indicators for the provided menu ID
  $query = "SELECT i.id AS id, i.name AS name
              FROM simrs_indicator i
              WHERE i.menu_id = :menu_id";

  // Execute the query
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);
  $stmt->execute();

  // Fetch data as associative array
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Output data as JSON
  header('Content-Type: application/json');
  echo json_encode($data);
} else {
  // Handle case when menu ID is not provided in the query parameters
  http_response_code(400); // Bad request
  echo json_encode(array("error" => "Menu ID is required"));
}
