<?php
require("config.php");

$response = array();

try {
  // Check if noRawat is provided
  if (isset($_POST['noRawat'])) {
    $noRawat = preg_replace("/[^0-9]/", "", $_POST['noRawat']); // Sanitize the input to prevent SQL injection
  } else {
    throw new Exception("No noRawat parameter found in the request.");
  }

  // Check if file is uploaded
  if (!empty($_FILES['file'])) {
    $fileType = "persetujuanTindakanPembedahan";
    $file = $_FILES['file'];
    $customFileName = $noRawat . '_dok_' . $fileType;
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $finalFileName = $customFileName . '.' . $fileExtension;
    $filePath = '../uploads/' . $finalFileName;

    // Check if the row exists in the database
    $checkSql = "SELECT COUNT(*) as count FROM simrs_berkas_persetujuan WHERE no_rawat = ?";
    $checkStmt = $mysqli->prepare($checkSql);
    $checkStmt->bind_param('s', $noRawat);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $rowCount = $row['count'];

    if ($rowCount > 0) {
      // If row exists, update the file path
      $updateSql = "UPDATE simrs_berkas_persetujuan SET pathname_$fileType = ? WHERE no_rawat = ?";
      $updateStmt = $mysqli->prepare($updateSql);
      $updateStmt->bind_param('ss', $finalFileName, $noRawat);
      $updateSuccess = $updateStmt->execute();

      if ($updateSuccess) {
        $response['status'] = "success";
        $response['message'] = "File updated successfully: " . $finalFileName;
      } else {
        throw new Exception("Failed to update data in the database.");
      }
    } else {
      // If row does not exist, insert a new row
      $insertSql = "INSERT INTO simrs_berkas_persetujuan (no_rawat, pathname_$fileType) VALUES (?, ?)";
      $insertStmt = $mysqli->prepare($insertSql);
      $insertStmt->bind_param('ss', $noRawat, $finalFileName);

      if ($insertStmt->execute()) {
        $response['status'] = "success";
        $response['message'] = "File uploaded successfully: " . $finalFileName;
      } else {
        throw new Exception("Failed to insert data into the database.");
      }
    }

    // Move the uploaded file to the server
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
      throw new Exception("Failed to upload file.");
    }
  } else {
    throw new Exception("No file uploaded.");
  }
} catch (Exception $e) {
  $response['status'] = "error";
  $response['message'] = $e->getMessage();
  http_response_code(500); // Set HTTP status code to 500 (Internal Server Error)
}

// Close database connection
$mysqli->close();

// Set Content-Type header to indicate JSON response
header('Content-Type: application/json');

// Return response in JSON format
echo json_encode($response);
