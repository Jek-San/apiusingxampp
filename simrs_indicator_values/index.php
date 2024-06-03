<?php

require("config.php");
require("db.php");

// Handle CRUD operations based on request method
$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
  case 'GET':
    // Read operation
    // Include code from read.php
    require("read.php");
    break;
  case 'POST':
    // Create operation
    // Include code from create.php
    require("create.php");
    break;
  case 'PUT':
    // Update operation
    // Include code from update.php
    require("update.php");
    break;
  case 'DELETE':
    // Delete operation
    // Include code from delete.php
    require("delete.php");
    break;
  default:
    // Invalid request method
    http_response_code(405);
    echo "Method Not Allowedddd";
    break;
}
