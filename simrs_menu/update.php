<?php

// Include the database connection functions
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Assuming you are passing data to update via JSON in the request body
$input_data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($input_data['id'], $input_data['name'])) {
  // Extract data from the JSON body
  $id = $input_data['id'];
  $name = $input_data['name'];

  // Prepare the update query
  $query = "UPDATE simrs_unit SET name = :name WHERE id = :id";

  // Prepare the query
  $stmt = $pdo->prepare($query);

  // Bind parameters
  $stmt->bindParam(':name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);

  // Execute the query
  $stmt->execute();

  // Check if the update was successful
  if ($stmt->rowCount() > 0) {
    // Return success response
    http_response_code(200);
    echo json_encode(array("message" => "Record updated successfully"));
  } else {
    // Return error response if the record was not found or not updated
    http_response_code(404);
    echo json_encode(array("message" => "Record not found or could not be updated cause data is same"));
  }
} else {
  // Return error response if the request method is not PUT or required data is missing
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or missing required data"));
}
