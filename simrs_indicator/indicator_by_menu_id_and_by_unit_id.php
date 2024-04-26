<?php

// Include the database connection functions
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Prepare the query to fetch all menus with their associated indicators
$query = "SELECT m.name AS menu_name, i.id AS id_indicator, i.name AS name_indicator
          FROM simrs_menu m 
          LEFT JOIN simrs_indicator i ON m.id = i.menu_id
          ORDER BY m.id";

// Execute the query
$stmt = $pdo->prepare($query);
$stmt->execute();

// Fetch data as associative array
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize data by menu names
$result = array();
foreach ($data as $row) {
  $menu_name = $row['menu_name'];
  $id_indicator = $row['id_indicator'];
  $name_indicator = $row['name_indicator'];

  // Add indicator to the menu group
  if (!isset($result[$menu_name])) {
    $result[$menu_name] = array();
  }
  if ($id_indicator !== null || $name_indicator !== null) {
    $result[$menu_name][] = array(
      "id_indicator" => $id_indicator,
      "name_indicator" => $name_indicator
    );
  }
}

// Output data as JSON
header('Content-Type: application/json');
echo json_encode($result);
