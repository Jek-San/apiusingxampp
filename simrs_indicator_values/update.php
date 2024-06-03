<?php

// Include the database connection functions
require_once("db.php");
require("config.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Assuming you are passing data to update via JSON in the request body
$input_data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'PUT' && !empty($input_data)) {
  // Begin transaction
  $pdo->beginTransaction();

  foreach ($input_data as $indicator_data) {
    $indicator_id = $indicator_data['indicator_id'];
    $indicator_name = $indicator_data['name'];
    $menu_id = isset($indicator_data['menu_id']) ? $indicator_data['menu_id'] : null;  // Check if 'menu_id' exists

    if ($menu_id === null) {
      $pdo->rollBack();
      http_response_code(400);
      echo json_encode(array("message" => "menu_id is required"));
      exit();
    }

    $indicator_query = "SELECT id FROM simrs_indicator WHERE id = :indicator_id";
    $indicator_stmt = $pdo->prepare($indicator_query);
    $indicator_stmt->bindValue(':indicator_id', $indicator_id);
    $indicator_stmt->execute();
    $existing_indicator = $indicator_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing_indicator) {
      $insert_indicator_query = "INSERT INTO simrs_indicator (id, name, menu_id) VALUES (:indicator_id, :name, :menu_id)";
      $insert_indicator_stmt = $pdo->prepare($insert_indicator_query);
      $insert_indicator_stmt->bindValue(':indicator_id', $indicator_id);
      $insert_indicator_stmt->bindValue(':name', $indicator_name);
      $insert_indicator_stmt->bindValue(':menu_id', $menu_id);
      if (!$insert_indicator_stmt->execute()) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(array("message" => "Error inserting indicator"));
        exit();
      }
    }

    foreach (['N', 'D'] as $type) {
      $values = $indicator_data['values'][$type]['data'];
      foreach ($values as $value) {
        $date = $value['date'];
        $value = $value['value'];

        $existing_value_query = "SELECT * FROM simrs_indicator_values WHERE indicator_id = :indicator_id AND date = :date AND type = :type";
        $existing_value_stmt = $pdo->prepare($existing_value_query);
        $existing_value_stmt->bindValue(':indicator_id', $indicator_id);
        $existing_value_stmt->bindValue(':date', $date);
        $existing_value_stmt->bindValue(':type', $type);
        $existing_value_stmt->execute();
        $existing_value = $existing_value_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_value) {
          $update_value_query = "UPDATE simrs_indicator_values SET value = :value WHERE indicator_id = :indicator_id AND date = :date AND type = :type";
          $update_value_stmt = $pdo->prepare($update_value_query);
          $update_value_stmt->bindValue(':value', $value);
          $update_value_stmt->bindValue(':indicator_id', $indicator_id);
          $update_value_stmt->bindValue(':date', $date);
          $update_value_stmt->bindValue(':type', $type);
          if (!$update_value_stmt->execute()) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(array("message" => "Error updating value"));
            exit();
          }
        } else {
          $insert_value_query = "INSERT INTO simrs_indicator_values (indicator_id, date, value, type) VALUES (:indicator_id, :date, :value, :type)";
          $insert_value_stmt = $pdo->prepare($insert_value_query);
          $insert_value_stmt->bindValue(':indicator_id', $indicator_id);
          $insert_value_stmt->bindValue(':date', $date);
          $insert_value_stmt->bindValue(':value', $value);
          $insert_value_stmt->bindValue(':type', $type);
          if (!$insert_value_stmt->execute()) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(array("message" => "Error inserting value"));
            exit();
          }
        }
      }
    }
  }

  $pdo->commit();
  http_response_code(200);
  echo json_encode(array("message" => "Data updated successfully"));
} else {
  http_response_code(400);
  echo json_encode(array("message" => "Invalid request or empty input data"));
}
