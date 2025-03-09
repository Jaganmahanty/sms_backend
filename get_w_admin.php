<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once "connection/db.php";

$response = ['success' => false, 'message' => '', 'result' => []];

try {
    if ($_SERVER['REQUEST_METHOD'] != "GET") {
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit;
    }

    if (!isset($_GET['accesscode']) || empty(trim($_GET['accesscode']))) {
        $response['message'] = "Accesscode is required!";
        echo json_encode($response);
        exit;
    }

    $accesscode = trim($_GET['accesscode']);

    $sql = "select wa.id, wa.fname, wa.lname, wa.mobile, wa.accesscode from admin_w wa
            inner JOIN admin_s sa ON wa.accesscode = sa.accesscode
            WHERE sa.accesscode = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $accesscode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['result'][] = [
                'id' => $row['id'],
                'fname' => $row['fname'],
                'lname' => $row['lname'],
                'mobile' => $row['mobile'],
                'accesscode' => $row['accesscode']
            ];
        }
        $response['success'] = true;
    } else {
        $response['message'] = "No matching Wing Admins found.";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response['message'] = "Exception: " . $e->getMessage();
}

echo json_encode($response);
?>