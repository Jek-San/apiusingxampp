<?php
require("config.php");

// Check if noRekamMedik is found
if (isset($_POST['noRekamMedik'])) {
  $noRekamMedik = $_POST['noRekamMedis'];
  // Now you can use $noRekamMedik in your PHP code as needed
} else {
  $response['status'] = "error";
  $response['message'] = "No noRekamMedik parameter found in the request.";
  echo json_encode($response);
  exit(); // Exit script if noRekamMedik parameter is not found
}

// Check if files are uploaded
if (isset($_FILES['ktpImage']) || isset($_FILES['kkImage'])) {
  $uploadedFiles = array();

  // Check and upload KTP image
  if (isset($_FILES['ktpImage'])) {
    $ktpImageFile = $_FILES['ktpImage'];
    $customKtpFileName = $noRekamMedik . '_ktp';
    $imageFileExtension = pathinfo($ktpImageFile['name'], PATHINFO_EXTENSION);
    $finalKtpFileName = $customKtpFileName . '.' . $imageFileExtension;
    $ktpUploadPath = '../uploads/' . $finalKtpFileName;

    if (move_uploaded_file($ktpImageFile['tmp_name'], $ktpUploadPath)) {
      $uploadedFiles[] = 'KTP';
    }
  }

  // Check and upload KK image
  if (isset($_FILES['kkImage'])) {
    $kkImageFile = $_FILES['kkImage'];
    $customKkFileName = $noRekamMedik . '_kk';
    $kkFileExtension = pathinfo($kkImageFile['name'], PATHINFO_EXTENSION);
    $finalKkFileName = $customKkFileName . '.' . $kkFileExtension;
    $kkUploadPath = '../uploads/' . $finalKkFileName;

    if (move_uploaded_file($kkImageFile['tmp_name'], $kkUploadPath)) {
      $uploadedFiles[] = 'KK';
    }
  }

  // Check if any file was successfully uploaded
  if (!empty($uploadedFiles)) {
    // Insert filenames into the database
    $sql = "INSERT INTO simrs_dokumenpasien (no_rkm_medis, pathname_ktp, pathname_kk) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $noRekamMedik, isset($finalKtpFileName) ? $finalKtpFileName : null, isset($finalKkFileName) ? $finalKkFileName : null);

    if ($stmt->execute()) {
      $response['status'] = "success";
      $response['message'] = "Files uploaded successfully: " . implode(', ', $uploadedFiles);
    } else {
      $response['status'] = "error";
      $response['message'] = "Failed to insert data into database.";
    }
  } else {
    $response['status'] = "error";
    $response['message'] = "Failed to upload files.";
  }
} else {
  $response['status'] = "error";
  $response['message'] = "No files uploaded.";
}

// Close database connection
$mysqli->close();

// Return response in JSON format
echo json_encode($response);
