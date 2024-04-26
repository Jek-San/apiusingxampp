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

  // Check if files are uploaded
  if (!empty($_FILES['persetujuanTindakanPembiusanSedasi']) || !empty($_FILES['persetujuanTindakanPembedahan'])) {
    $uploadedFiles = array();

    // Check and upload persetujuanTindakanPembiusanSedasi file
    if (!empty($_FILES['persetujuanTindakanPembiusanSedasi'])) {
      $file1 = $_FILES['persetujuanTindakanPembiusanSedasi'];
      $customFile1Name = $noRawat . '_dok_persetujuanTindakanPembiusanSedasi';
      $file1Extension = pathinfo($file1['name'], PATHINFO_EXTENSION);
      $finalFile1Name = $customFile1Name . '.' . $file1Extension;
      $file1Path = '../uploads/' . $finalFile1Name;

      if (move_uploaded_file($file1['tmp_name'], $file1Path)) {
        $uploadedFiles[] = 'persetujuanTindakanPembiusanSedasi';
      } else {
        throw new Exception("Failed to upload files.");
      }
    }

    // Check and upload persetujuanTindakanPembedahan file
    if (!empty($_FILES['persetujuanTindakanPembedahan'])) {
      $file2 = $_FILES['persetujuanTindakanPembedahan'];
      $customFile2Name = $noRawat . '_dok_persetujuanTindakanPembedahan';
      $file2Extension = pathinfo($file2['name'], PATHINFO_EXTENSION);
      $finalFile2Name = $customFile2Name . '.' . $file2Extension;
      $file2Path = '../uploads/' . $finalFile2Name;

      if (move_uploaded_file($file2['tmp_name'], $file2Path)) {
        $uploadedFiles[] = 'persetujuanTindakanPembedahan';
      } else {
        throw new Exception("Failed to upload files.");
      }
    }

    // Insert filenames into the database
    $sql = "INSERT INTO simrs_berkas_persetujuan_ok (no_rawat, pathname_persetujuanTindakanPembiusanSedasi, pathname_persetujuanTindakanPembedahan) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $noRawat, $finalFile1Name ?? null, $finalFile2Name ?? null);

    if ($stmt->execute()) {
      $response['status'] = "success";
      $response['message'] = "Files uploaded successfully: " . implode(', ', $uploadedFiles);
    } else {
      throw new Exception("Failed to insert data into the database.");
    }
  } else {
    throw new Exception("No files uploaded.");
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
