<?php
require("config.php");
$searchQuery = '';
$fromDate = date("Y-m-d");
$toDate = date("Y-m-d");

if (isset($_GET['search'])) {
  $searchQuery = $mysqli->real_escape_string($_GET['search']);
}

if (isset($_GET['fromDate'])) {
  $fromDate = $mysqli->real_escape_string(date("Y-m-d", strtotime($_GET['fromDate'])));
}

if (isset($_GET['toDate'])) {
  $toDate = $mysqli->real_escape_string(date("Y-m-d", strtotime($_GET['toDate'])));
}

$query = "SELECT reg_periksa.*, pasien.nm_pasien, simrs_foto_resep.pathname_foto , resep_obat.tgl_perawatan
FROM reg_periksa 
LEFT JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
RIGHT JOIN simrs_foto_resep ON reg_periksa.no_rawat = simrs_foto_resep.no_rawat
LEFT JOIN resep_obat ON reg_periksa.no_rawat = resep_obat.no_rawat 
WHERE  (reg_periksa.no_rawat LIKE '%$searchQuery%' 
               OR reg_periksa.no_rkm_medis LIKE '%$searchQuery%' 
               OR pasien.nm_pasien LIKE '%$searchQuery%' 
               OR reg_periksa.status_lanjut LIKE '%$searchQuery%') 
ORDER BY reg_periksa.tgl_registrasi DESC;
";
$result = $mysqli->query($query);

if ($result) {
  $data = array();
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  header('Content-Type: application/json');
  echo json_encode($data);
} else {
  echo "Failed to retrieve data";
}

$mysqli->close();
