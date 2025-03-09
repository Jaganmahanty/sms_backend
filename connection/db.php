<?php

$servername = "srv1326.hstgr.io";
$username = "u529253940_suman";
$password = "Suman_@2025";
$dbname = "u529253940_sumanSanchalan";

$conn = new mysqli(
    $servername,
    $username,
    $password,
    $dbname
);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

// echo json_encode("success");
// json_encode("success"); 
// echo "success";
?>