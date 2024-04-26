<?php
require("config.php");

// Check if noRekamMedik is Found
if (isset($_POST['noRekamMedis'])) {
  $noRekamMedis = $_POST['noRekamMedis']; // Corrected variable name
  // Now you can use $noRekamMedis in your PHP code as needed
} else {
  echo "No noRekamMedis parameter found in the request."; // Corrected parameter name
  exit(); // Exit script if noRekamMedis parameter is not found
}

// Check if files are uploaded
if (
  isset($_FILES['ktpImage']) && isset($_FILES['kkImage'])
) {
  $ktpImageFile = $_FILES['ktpImage'];
  $kkImageFile = $_FILES['kkImage'];

  // Generate unique filenames for each file
  $customKtpFileName = $noRekamMedis . '_ktp'; // Corrected variable name
  $customKkFileName = $noRekamMedis . '_kk'; // Corrected variable name

  // Get file extensions
  $imageFileExtension = pathinfo($ktpImageFile['name'], PATHINFO_EXTENSION);
  $kkFileExtension = pathinfo($kkImageFile['name'], PATHINFO_EXTENSION);

  // Construct final filenames with extensions
  $finalKtpFileName = $customKtpFileName . '.' . $imageFileExtension;
  $finalKkFileName = $customKkFileName . '.' . $kkFileExtension;

  // Move uploaded files to desired directories with custom filenames
  $ktpUploadPath = '../uploads/' . $finalKtpFileName;
  $kkUploadPath = '../uploads/' . $finalKkFileName;

  // Check if both files were successfully uploaded
  if (move_uploaded_file($ktpImageFile['tmp_name'], $ktpUploadPath) && move_uploaded_file($kkImageFile['tmp_name'], $kkUploadPath)) {
    // Insert filenames into the database
    $sql = "INSERT INTO simrs_dokumenpasien (no_rkm_medis, pathname_ktp, pathname_kk) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $noRekamMedis, $finalKtpFileName, $finalKkFileName); // Corrected variable name

    if ($stmt->execute()) {
      echo "KTP and KK uploaded successfully and inserted into the database.";
    } else {
      echo "Failed to insert data into the database.";
    }
  } else {
    echo "Failed to upload files.";
  }
} else {
  echo "No files uploaded.";
}

// Close the database connection
$mysqli->close();
