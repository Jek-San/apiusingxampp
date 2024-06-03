<?php

// Include the database connection functions
require_once("db.php");

// Establish database connection using PDO
$pdo = connectToDatabase();

// Check if the 'menu_id', 'month_index', and 'year' parameters are provided in the GET request
if (isset($_GET['menu_id']) && isset($_GET['month_index']) && isset($_GET['year'])) {
  // Extract month index, add 1 to match PHP's date format where January is 1
  $monthIndex = intval($_GET['month_index']) + 1;
  $year = intval($_GET['year']);

  // Construct the SQL query to retrieve indicators based on menu_id and month
  $query = "SELECT si.id AS indicator_id,
                     si.name AS name,
                     sn.n_name AS n_name,
                     sn.d_name AS d_name,
                     siv.date AS date,
                     siv.value AS value,
                     siv.type AS type
              FROM simrs_indicator si
              INNER JOIN simrs_menu sm ON si.menu_id = sm.id
              INNER JOIN simrs_naming sn ON si.id = sn.indicator_id
              LEFT JOIN simrs_indicator_values siv ON sn.indicator_id = siv.indicator_id
                                               AND MONTH(siv.date) = :month
                                               AND YEAR(siv.date) = :year
              WHERE sm.id = :menu_id AND si.is_active = 1";  // Only include active indicators
} else {
  // If any required parameter is missing, respond with an error message
  header('Content-Type: application/json');
  http_response_code(400);
  echo json_encode(array("message" => "Menu ID, month index, and year parameters are required"));
  exit;
}

// Prepare the query
$stmt = $pdo->prepare($query);

// Bind parameters
$stmt->bindParam(':menu_id', $_GET['menu_id'], PDO::PARAM_INT);
$stmt->bindParam(':month', $monthIndex, PDO::PARAM_INT);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);

// Execute the query
if (!$stmt->execute()) {
  // If the query fails to execute, respond with an error message
  header('Content-Type: application/json');
  http_response_code(500);
  echo json_encode(array("message" => "Failed to execute query"));
  exit;
}

// Fetch data as associative array
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize an empty array to store the result
$result = array();

// Loop through the data to organize it by indicator
foreach ($data as $row) {
  $indicatorId = $row['indicator_id'];
  $indicatorName = $row['name'];
  $nName = $row['n_name'];
  $dName = $row['d_name'];
  $date = $row['date'];
  $value = $row['value'];
  $type = $row['type'];

  // Check if the indicator already exists in the result array
  if (!isset($result[$indicatorId])) {
    // If not, initialize it
    $result[$indicatorId] = array(
      "indicator_id" => $indicatorId,
      "name" => $indicatorName,
      "menu_id" => $_GET['menu_id'],
      "values" => array(
        "N" => array(
          "n_name" => $nName,
          "data" => array(),
          "sumN" => 0 // Initialize sumN to 0
        ),
        "D" => array(
          "d_name" => $dName,
          "data" => array(),
          "sumD" => 0 // Initialize sumD to 0
        ),
        "percentageRatio" => 0 // Initialize percentageRatio to 0
      )
    );
  }

  // Add the value to the corresponding type (N or D)
  if ($date !== null && $value !== null) { // Check if date and value are not null
    // Calculate sumN and sumD
    $result[$indicatorId]["values"][$type]["sum" . $type] += $value;

    // Add the data
    $result[$indicatorId]["values"][$type]["data"][] = array(
      "date" => $date,
      "value" => $value
    );
  }
}

// Calculate the percentage ratio
foreach ($result as &$indicator) {
  $sumN = $indicator['values']['N']['sumN'];
  $sumD = $indicator['values']['D']['sumD'];
  if ($sumD != 0) {
    $indicator['values']['percentageRatio'] = ($sumN / $sumD) * 100;
  } else {
    $indicator['values']['percentageRatio'] = 0; // Avoid division by zero
  }
}
unset($indicator); // Unset reference variable to avoid accidental modification

// Output data as JSON
header('Content-Type: application/json');
echo json_encode(array_values($result)); // Convert associative array to indexed array
