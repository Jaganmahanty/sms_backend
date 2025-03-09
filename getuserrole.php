<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once "connection/db.php";

$response = [
    'success' => false,
    'member' => [],
    'admin_w' => [],
    'admin_s' => []
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== "GET") {
        echo json_encode(['success' => false, 'message' => "Invalid request method, use GET!"]);
        exit;
    }

    if (!isset($_GET["mobile"]) || empty(trim($_GET["mobile"]))) {
        echo json_encode(['success' => false, 'message' => "Mobile number is required!"]);
        exit;
    }

    $mobile = trim($_GET["mobile"]);

    // Check role from 'member' table
    $stmt = $conn->prepare("
    SELECT a.fname, b.name, b.accesscode 
        FROM member a
        JOIN societies b on a.accesscode = b.accesscode 
        WHERE a.mobile = ? ");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['member'][] = [
                'name' => $row['fname'],
                'socname' => $row['name'],
                'accesscode' => $row['accesscode'],
                'message' => "Member Found!"
            ];
        }
        $response['success'] = true;
    }
    $stmt->close();

    // Check role from 'admin_w' table (Wing Admin)
    $stmt = $conn->prepare("
        SELECT a.fname, b.name, b.accesscode  
        FROM admin_w a
        JOIN societies b ON a.accesscode = b.accesscode 
        WHERE a.mobile = ? ");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['admin_w'][] = [
                'name' => $row['fname'],
                'socname' => $row['name'],
                'accesscode' => $row['accesscode'],
                'message' => "Wing Admin Found!"
            ];
        }
        $response['success'] = true;
    }
    $stmt->close();

    // Check role from 'admin_s' table and join with 'society' table
    $stmt = $conn->prepare("
        SELECT a.fname, b.name, b.accesscode 
        FROM admin_s a
        JOIN societies b ON a.accesscode = b.accesscode 
        WHERE a.mobile = ?
    ");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['admin_s'][] = [
                'name' => $row['fname'],
                'socname' => $row['name'],
                'accesscode' => $row['accesscode'],
                'message' => "Society Admin Found!"
            ];
        }
        $response['success'] = true;
    }
    $stmt->close();

    // If no roles were found, return a failure response
    if (!$response['success']) {
        echo json_encode(['success' => false, 'message' => "User not found or has no roles!"]);
        exit;
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>