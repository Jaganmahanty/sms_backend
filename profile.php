<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once "connection/db.php";

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] != "POST" && !isset($_GET['type'])) {
        $response['message'] = "Missing 'type' parameter!";
        echo json_encode($response);
        exit;
    }


    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        $type = $_GET['type'];
        // Fetch User Profile Data
        if (!isset($_GET['mobile'])) {
            $response['message'] = "Mobile number is required!";
            echo json_encode($response);
            exit;
        }

        $mobile = trim($_GET['mobile']);

        $sql = "select id, fname, lname, mobile, role from users where mobile = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $response['success'] = true;
            $response['message'] = "User found !";
            $response['record'] = $userData;
        } else {
            $response['message'] = "No user found with this mobile number !";
        }

        echo json_encode($response);
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
        // Update User Data
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['mobile'])) {
            $response['message'] = "Mobile number is required !";
            echo json_encode($response);
            exit;
        }

        $o_mobile = trim($data['o_mobile']);
        $mobile = trim($data['mobile']);
        $fname = isset($data['fname']) ? trim($data['fname']) : null;
        $lname = isset($data['lname']) ? trim($data['lname']) : null;
        $email = isset($data['email']) ? trim($data['email']) : null;
        $role = isset($data['role']) ? trim($data['role']) : null;

        $stmt = $conn->prepare("update users set fname = ?, lname = ?, mobile = ? WHERE mobile = ?");
        $stmt->bind_param("ssii", $fname, $lname, $mobile, $o_mobile);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "User updated successfully !";
        } else {
            $response['message'] = "Failed to update user !";
        }

        echo json_encode($response);
        exit;
    } else {
        $response['message'] = "Invalid 'type' parameter or request method!";
        echo json_encode($response);
        exit;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}

$conn->close();
?>