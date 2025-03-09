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
    'wings' => []
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== "GET") {
        echo json_encode(['success' => false, 'message' => "Invalid request method, use GET!"]);
        exit;
    }

    if (!isset($_GET["code"]) || empty(trim($_GET["code"]))) {
        echo json_encode(['success' => false, 'message' => "Access code is required!"]);
        exit;
    }

    $accesscode = trim($_GET["code"]);

    // Fetch Society ID using Access Code
    $stmt = $conn->prepare("select id from societies where accesscode = ?");
    $stmt->bind_param("s", $accesscode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => "No society found for this access code!"]);
        exit;
    }

    $row = $result->fetch_assoc();
    $societyId = $row['id'];
    $stmt->close();

    // Fetch Wing Names using Society ID (controlid)
    $stmt = $conn->prepare("select w_name from wings where controlid = ?");
    $stmt->bind_param("i", $societyId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['wings'][] = $row['w_name'];
        }
        $response['success'] = true;
    } else {
        $response['message'] = "No wings found for this society!";
    }

    $stmt->close();
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>