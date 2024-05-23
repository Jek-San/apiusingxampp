<?php

// Include the database connection functions
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Assuming you are passing data to update via JSON in the request body
$input_data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && !empty($input_data)) {
  try {
    // Begin transaction
    $pdo->beginTransaction();

    // Define required fields
    $required_fields = ['id', 'name', 'menu_id'];

    // Check if all required fields are present
    $missing_fields = array_diff($required_fields, array_keys($input_data));
    if (!empty($missing_fields)) {
      // Return error response if any required data is missing
      http_response_code(400);
      echo json_encode(array("message" => "Missing required data: " . implode(', ', $missing_fields)));
      exit; // Stop further execution
    }

    // Prepare the update query
    $update_query = "UPDATE simrs_indicator SET ";
    $params = [];

    // Dynamically build the SET clause and collect parameters
    foreach ($input_data as $key => $value) {
      // Sanitize inputs
      $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

      // Add parameter to SET clause
      $update_query .= "$key = :$key, ";
      // Collect parameters for binding
      $params[":$key"] = $value;
    }

    // Remove trailing comma and space from SET clause
    $update_query = rtrim($update_query, ", ");

    // Add WHERE clause
    $update_query .= " WHERE id = :id";

    // Bind the ID parameter
    $params[':id'] = $input_data['id'];

    // Prepare the query
    $update_stmt = $pdo->prepare($update_query);

    // Bind parameters
    foreach ($params as $param => $value) {
      $update_stmt->bindValue($param, $value);
    }

    // Execute the update query
    $update_stmt->execute();

    // Commit transaction
    $pdo->commit();

    // Check if the update was successful
    if ($update_stmt->rowCount() > 0) {
      // Return success response
      http_response_code(200);
      echo json_encode(array("message" => "Record updated successfully"));
    } else {
      // Return error response if the record was not updated
      http_response_code(500);
      echo json_encode(array("message" => "Failed to update record"));
    }
  } catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    // Return error response
    http_response_code(500);
    echo json_encode(array("message" => "Database error: " . $e->getMessage()));
  }
} else {
  // Return error response if the request method is not PUT or input data is empty
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or empty input data"));
}
