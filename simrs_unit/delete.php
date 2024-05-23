<?php

require_once("config.php");
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Assuming you are passing the ID of the record to delete via JSON in the request body
$input_data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($input_data['id'])) {
  // Extract the ID of the record to delete
  $id = $input_data['id'];

  // Check if there are related records in other tables
  $query = "SELECT COUNT(*) AS count FROM simrs_menu WHERE unit_id = :id";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  // If related records exist, return an error response
  if ($result['count'] > 0) {
    http_response_code(400);
    echo json_encode(array("message" => "Cannot delete unit because it has related records in other tables"));
    exit();
  }

  // Prepare the delete query
  $delete_query = "DELETE FROM simrs_unit WHERE id = :id";

  // Prepare the query
  $delete_stmt = $pdo->prepare($delete_query);
  $delete_stmt->bindParam(':id', $id, PDO::PARAM_INT);

  // Execute the delete query
  if ($delete_stmt->execute()) {
    // Check if any rows were affected
    if ($delete_stmt->rowCount() > 0) {
      // Return success response
      http_response_code(200);
      echo json_encode(array("message" => "Record deleted successfully"));
    } else {
      // Return error response if the record was not found or not deleted
      http_response_code(404);
      echo json_encode(array("message" => "Record not found or could not be deleted"));
    }
  } else {
    // Return error response if the deletion query fails
    http_response_code(500);
    echo json_encode(array("message" => "Internal server error occurred while deleting record"));
  }
} else {
  // Return error response if the request method is not DELETE or required data is missing
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or missing required data"));
}
