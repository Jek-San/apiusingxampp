<?php

// Include the database connection functions
require_once("db.php");
require_once('config.php');

// Establish database connection using PDO
$pdo = connectToDatabase();

// Assuming you are passing data to create via JSON in the request body
$input_data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input_data['indicator_id']) && isset($input_data['n_name']) && isset($input_data['d_name'])) {
  // Extract data from the JSON body
  $indicator_id = $input_data['indicator_id'];
  $n_name = $input_data['n_name'];
  $d_name = $input_data['d_name'];

  // Prepare the insert query
  $query = "INSERT INTO simrs_naming (indicator_id, n_name, d_name) VALUES (:indicator_id, :n_name,:d_name)";
  // Prepare the query
  $stmt = $pdo->prepare($query);

  // Bind parameters
  $stmt->bindParam(':indicator_id', $indicator_id, PDO::PARAM_INT);
  $stmt->bindParam(':n_name', $n_name, PDO::PARAM_STR);
  $stmt->bindParam(':d_name', $d_name, PDO::PARAM_STR); // Fix the variable name here

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
