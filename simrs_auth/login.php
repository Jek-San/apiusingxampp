<?php

require_once("db.php");
require_once("config.php");

$pdo = connectToDatabase();

$input_data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input_data['username']) && isset($input_data['password'])) {
  $username = $input_data['username'];
  $password = $input_data['password'];

  // Prepare the query
  $query = "SELECT * FROM simrs_mutu_users WHERE username = :username";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':username', $username, PDO::PARAM_STR);

  // Execute the query
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user['password'])) {
    // Password matches
    http_response_code(200);
    echo json_encode(array(
      "message" => "Login successful",
      "unit_id" => $user['unit_id'],
      "is_admin" => $user['is_admin'],
      "userName" => $user['username']
    ));
  } else {
    // Invalid credentials
    http_response_code(401);
    echo json_encode(array("message" => "Invalid username or password"));
  }
} else {
  // Invalid request
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or missing required data"));
}
