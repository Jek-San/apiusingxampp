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

    // Iterate over each indicator data
    foreach ($input_data as $indicator_data) {
      // Check if the indicator exists in the database
      $indicator_id = $indicator_data['indicator_id'];
      $indicator_name = $indicator_data['name'];
      $menu_id = $indicator_data['menu_id'];

      // Check if the indicator already exists
      $indicator_query = "SELECT id FROM simrs_indicator WHERE id = :indicator_id";
      $indicator_stmt = $pdo->prepare($indicator_query);
      $indicator_stmt->bindValue(':indicator_id', $indicator_id);
      $indicator_stmt->execute();
      $existing_indicator = $indicator_stmt->fetch(PDO::FETCH_ASSOC);

      if (!$existing_indicator) {
        // If the indicator does not exist, insert it into the database
        $insert_indicator_query = "INSERT INTO simrs_indicator (id, name, menu_id) VALUES (:indicator_id, :name, :menu_id)";
        $insert_indicator_stmt = $pdo->prepare($insert_indicator_query);
        $insert_indicator_stmt->bindValue(':indicator_id', $indicator_id);
        $insert_indicator_stmt->bindValue(':name', $indicator_name);
        $insert_indicator_stmt->bindValue(':menu_id', $menu_id);
        $insert_indicator_stmt->execute();
      }

      // Iterate over N and D values
      foreach (['N', 'D'] as $type) {
        $values = $indicator_data['values'][$type]['data'];
        foreach ($values as $value) {
          $date = $value['date'];
          $value = $value['value'];

          // Check if the value already exists
          $existing_value_query = "SELECT * FROM simrs_indicator_values WHERE indicator_id = :indicator_id AND date = :date AND type = :type";
          $existing_value_stmt = $pdo->prepare($existing_value_query);
          $existing_value_stmt->bindValue(':indicator_id', $indicator_id);
          $existing_value_stmt->bindValue(':date', $date);
          $existing_value_stmt->bindValue(':type', $type);
          $existing_value_stmt->execute();
          $existing_value = $existing_value_stmt->fetch(PDO::FETCH_ASSOC);

          if ($existing_value) {
            // If the value exists, update it
            $update_value_query = "UPDATE simrs_indicator_values SET value = :value WHERE indicator_id = :indicator_id AND date = :date AND type = :type";
            $update_value_stmt = $pdo->prepare($update_value_query);
            $update_value_stmt->bindValue(':value', $value);
            $update_value_stmt->bindValue(':indicator_id', $indicator_id);
            $update_value_stmt->bindValue(':date', $date);
            $update_value_stmt->bindValue(':type', $type);
            $update_value_stmt->execute();
          } else {
            // If the value does not exist, insert it
            $insert_value_query = "INSERT INTO simrs_indicator_values (indicator_id, date, value, type) VALUES (:indicator_id, :date, :value, :type)";
            $insert_value_stmt = $pdo->prepare($insert_value_query);
            $insert_value_stmt->bindValue(':indicator_id', $indicator_id);
            $insert_value_stmt->bindValue(':date', $date);
            $insert_value_stmt->bindValue(':value', $value);
            $insert_value_stmt->bindValue(':type', $type);
            $insert_value_stmt->execute();
          }
        }
      }
    }

    // Commit transaction
    $pdo->commit();

    // Return success response
    http_response_code(200);
    echo json_encode(array("message" => "Data updated successfully"));
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
