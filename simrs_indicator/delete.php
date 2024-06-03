<?php

// Include the database connection functions
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Assuming you are passing the ID of the record to update via JSON in the request body
$input_data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($input_data['id'])) {
  // Extract the ID of the record to update
  $id = $input_data['id'];

  // Prepare the update query
  $query = "UPDATE simrs_indicator SET is_active = 0 WHERE id = :id";

  // Prepare the query
  $stmt = $pdo->prepare($query);

  // Bind parameters
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);

  // Execute the query
  $stmt->execute();

  // Check if any rows were affected
  if ($stmt->rowCount() > 0) {
    // Return success response
    http_response_code(200);
    echo json_encode(array("message" => "Indicator updated successfully"));
  } else {
    // Return error response if the record was not found or not updated
    http_response_code(404);
    echo json_encode(array("message" => "Indicator not found or could not be updated"));
  }
} else {
  // Return error response if the request method is not DELETE or required data is missing
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or missing required data"));
}
