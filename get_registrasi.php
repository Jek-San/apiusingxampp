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

$query = "SELECT reg_periksa.*, pasien.nm_pasien FROM reg_periksa LEFT JOIN pasien ON reg_periksa.no_rkm_medis=pasien.no_rkm_medis WHERE tgl_registrasi BETWEEN '$fromDate' AND '$toDate' AND (reg_periksa.no_rawat LIKE '%$searchQuery%' or reg_periksa.no_rkm_medis LIKE '%$searchQuery%' or pasien.nm_pasien LIKE '%$searchQuery%' or reg_periksa.status_lanjut LIKE '%$searchQuery%') order by reg_periksa.tgl_registrasi desc";

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
