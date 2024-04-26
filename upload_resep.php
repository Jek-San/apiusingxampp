<?php
require("config.php");
// Check if noRawat is Found
if (isset($_POST['noRawat'])) {
  $noRawat = $_POST['noRawat'];
} else {
  echo "No noRawat parameter found in the request.";
  exit(); // Exit script if noResep parameter is not found
}

// Check if files are uploaded
if (isset($_FILES['fotoResep'])) {
  $fotoResepFile = $_FILES['fotoResep'];

  // Generate unique filename for the file
  $customfotoFileName =
    preg_replace(
      "/[^0-9]/",
      "",
      $noRawat
    ) . '_foto_resep' . date("Y-m-d His");

  // Get file extension
  $imageFileExtension = pathinfo($fotoResepFile['name'], PATHINFO_EXTENSION);

  // Construct final filename with extension
  $finalFotoResep = $customfotoFileName . '.' . $imageFileExtension;

  // Move uploaded file to desired directory with custom filename
  $fotoResepPath = '../uploads/' . $finalFotoResep;

  // Check if the file was successfully uploaded
  if (move_uploaded_file($fotoResepFile['tmp_name'], $fotoResepPath)) {
    // Insert filename into the database
    $sql = "INSERT INTO simrs_foto_resep (no_rawat, pathname_foto, tgl_upload) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $noRawat, $finalFotoResep, date("Y-m-d H:i:s"));

    if ($stmt->execute()) {
      echo "Photo uploaded successfully and inserted into database.";
    } else {
      echo "Failed to insert data into database.";
    }
  } else {
    echo "Failed to upload file.";
  }
} else {
  echo "No file uploaded.";
}

// Close database connection
$mysqli->close();
