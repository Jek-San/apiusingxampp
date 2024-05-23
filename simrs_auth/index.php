<?php

require("config.php");
require("db.php");

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

if ($request_uri === '/login' && $request_method === 'POST') {
  require("login.php");
  exit;
}

switch ($request_method) {
  case 'GET':
    require("read.php");
    break;
  case 'POST':
    require("create.php");
    break;
  case 'PUT':
    require("update.php");
    break;
  case 'DELETE':
    require("delete.php");
    break;
  default:
    http_response_code(405);
    echo "Method Not Allowed";
    break;
}
