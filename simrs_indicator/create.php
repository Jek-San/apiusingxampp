<?php

// Include the database connection functions
require_once("db.php");
require_once('config.php');

// Establish database connection using PDO
$pdo = connectToDatabase();

// Assuming you are passing data to create via JSON in the request body
$input_data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input_data['name']) && isset($input_data['menu_id'])) {
  // Extract data from the JSON body
  $name = $input_data['name'];
  $menu_id = $input_data['menu_id'];

  // Prepare the insert query for simrs_indicator
  $query = "INSERT INTO simrs_indicator (name, menu_id) VALUES (:name, :menu_id)";
  // Prepare the query
  $stmt = $pdo->prepare($query);

  // Bind parameters
  $stmt->bindParam(':name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':menu_id', $menu_id, PDO::PARAM_INT); // Fix the variable name here

  // Execute the query
  $stmt->execute();

  // Check if the insert was successful
  if ($stmt->rowCount() > 0) {
    // Get the ID of the newly inserted record
    $new_id = $pdo->lastInsertId();

    // Prepare the insert query for simrs_naming with default values
    $query_naming = "INSERT INTO simrs_naming (indicator_id, n_name, d_name) VALUES (:indicator_id, :n_name, :d_name)";
    $stmt_naming = $pdo->prepare($query_naming);

    // Bind parameters with default values
    $default_n_name = "N Name Indicator";
    $default_d_name = "D Name Indicator";
    $stmt_naming->bindParam(':indicator_id', $new_id, PDO::PARAM_INT);
    $stmt_naming->bindParam(':n_name', $default_n_name, PDO::PARAM_STR);
    $stmt_naming->bindParam(':d_name', $default_d_name, PDO::PARAM_STR);

    // Execute the query for simrs_naming
    $stmt_naming->execute();

    // Check if the insert was successful
    if ($stmt_naming->rowCount() > 0) {
      // Return success response with the ID of the newly created record
      http_response_code(201);
      echo json_encode(array("message" => "Record created successfully", "id" => $new_id));
    } else {
      // Return error response if the insert operation for simrs_naming fails
      http_response_code(500);
      echo json_encode(array("message" => "Failed to create naming record"));
    }
  } else {
    // Return error response if the insert operation for simrs_indicator fails
    http_response_code(500);
    echo json_encode(array("message" => "Failed to create indicator record"));
  }
} else {
  // Return error response if the request method is not POST or required data is missing
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or missing required data"));
}
