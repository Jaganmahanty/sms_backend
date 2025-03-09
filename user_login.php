<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once "connection/db.php";

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
        $response['message'] = "Invalid request method, use Post !";
        echo json_encode($response);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['mobile'] || empty($data['pin']))) {
        $response['success'] = false;
        $response['message'] = "Mobile number and Pin are required !!";
        echo json_encode($response);
        exit;
    }

    $mobile = trim($data['mobile']);
    $pin = trim($data['pin']);

    $sql = "select id, fname, lname from users where mobile  = ? and pin = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $mobile, $pin);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $response['message'] = "User not found !!";
        echo json_encode($response);
        exit;
    }

    $stmt->bind_result($id, $fname, $lname);
    $stmt->fetch();

    $response['success'] = true;
    $response['message'] = 'User logged in successfully !!';
    $response['user'] = ['id' => $id, 'fname' => $fname, 'lname' => $lname];

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

?>