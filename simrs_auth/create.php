<?php

require_once("db.php");
require_once("config.php");

$pdo = connectToDatabase();

$input_data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input_data['username']) && isset($input_data['password']) && isset($input_data['unit_id'])) {
  $username = $input_data['username'];
  $password = $input_data['password'];
  $unit_id = $input_data['unit_id'];

  // Check if the username already exists
  $check_query = "SELECT * FROM simrs_mutu_users WHERE username = :username";
  $check_stmt = $pdo->prepare($check_query);
  $check_stmt->bindParam(':username', $username, PDO::PARAM_STR);
  $check_stmt->execute();
  $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_user) {
    // Username already exists
    http_response_code(409);
    echo json_encode(array("message" => "Username already exists"));
  } else {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the insert query
    $insert_query = "INSERT INTO simrs_mutu_users (username, password, unit_id) VALUES (:username, :password, :unit_id)";
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $insert_stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $insert_stmt->bindParam(':unit_id', $unit_id, PDO::PARAM_INT);


    // Execute the query
    if ($insert_stmt->execute()) {
      // User created successfully
      http_response_code(201);
      echo json_encode(array("message" => "User created successfully"));
    } else {
      // Failed to create user
      http_response_code(500);
      echo json_encode(array("message" => "Failed to create user"));
    }
  }
} else {
  // Invalid request
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or missing required data"));
}
