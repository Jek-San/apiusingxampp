<?php

// Function to establish database connection using PDO
function connectToDatabase()
{
  $host = 'localhost';
  $dbname = 'sik';
  $username = 'root';
  $password = '';

  try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  } catch (PDOException $e) {
    die("Failed to connect to database: " . $e->getMessage());
  }
}

// Function to execute SQL queries with prepared statements using PDO
function executeQuery($pdo, $query, $params = array())
{
  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
  } catch (PDOException $e) {
    die("Error executing query: " . $e->getMessage());
  }
}

// Function to fetch data as associative array using PDO
function fetchData($stmt)
{
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to close database connection (PDO automatically closes when the script ends)
function closeConnection($pdo)
{
  $pdo = null; // Set the PDO object to null to close the connection
}
