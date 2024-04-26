<?php

// Include the database connection functions
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Assuming you are passing data to create via JSON in the request body
$input_data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input_data['name']) && isset($input_data['unit_id'])) {
  // Extract data from the JSON body
  $name = $input_data['name'];
  $unit_id = $input_data['unit_id'];

  // Prepare the insert query
  $query = "INSERT INTO simrs_menu (name, unit_id) VALUES (:name, :unit_id)";
  // Prepare the query
  $stmt = $pdo->prepare($query);

  // Bind parameters
  $stmt->bindParam(':name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);

  // Execute the query
  $stmt->execute();

  // Check if the insert was successful
  if ($stmt->rowCount() > 0) {
    // Get the ID of the newly inserted record
    $new_id = $pdo->lastInsertId();

    // Return success response with the ID of the newly created record
    http_response_code(201);
    echo json_encode(array("message" => "Record created successfully", "id" => $new_id));
  } else {
    // Return error response if the insert operation fails
    http_response_code(500);
    echo json_encode(array("message" => "Failed to create record"));
  }
} else {
  // Return error response if the request method is not POST or required data is missing
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or missing required data"));
}
