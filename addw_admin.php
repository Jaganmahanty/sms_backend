<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once "connection/db.php";

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
        $response['message'] = 'Invalid request method used';
        echo json_encode($response);
        exit;
    }

    if (!isset($_GET['id'])) {
        $response['message'] = "Id not found !!";
        echo json_encode($response);
        exit;
    }

    $id = intval($_GET['id']);
    $data = json_decode(file_get_contents("php://input"), true);

    $fname = trim($data['fname']);
    $lname = trim($data['lname']);
    $mobile = trim($data['mobile']);
    $s_mobile = trim($data['s_mobile']);
    $pin = trim($data['pin']);
    $creator = "System";  // Change if needed
    $role = "w_admin";

    if (empty($fname) || empty($lname) || empty($mobile) || empty($pin) || empty($data['wing'])) {
        $response['message'] = "All Fields Are Required !!!";
        echo json_encode($response);
        exit;
    }

    if ($id == 0) {  
        // Check if user already exists
        $check_stmt = $conn->prepare("select id from users where mobile = ?");
        $check_stmt->bind_param("s", $mobile);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $response['message'] = "Admin with this mobile number already registered!";
            echo json_encode($response);
            exit;
        }
        $check_stmt->close();

        // Fetch controlId and accesscode from admin_s
        $stmt_admin_s = $conn->prepare("SELECT id, accesscode FROM admin_s WHERE mobile = ?");
        $stmt_admin_s->bind_param("s", $s_mobile);
        $stmt_admin_s->execute();
        $stmt_admin_s->store_result();

        if ($stmt_admin_s->num_rows > 0) {
            $stmt_admin_s->bind_result($controlId, $accesscode);
            $stmt_admin_s->fetch();
            $stmt_admin_s->close();

            // Insert into admin_w
            $stmt_admin_w = $conn->prepare("INSERT INTO admin_w (controlid, accesscode, fname, lname, mobile, pin, created_at) 
                                            VALUES (?, ?, ?, ?, ?, ?, CONVERT_TZ(NOW(), '+00:00', '+05:30'))");
            $stmt_admin_w->bind_param("ssssss", $controlId, $accesscode, $fname, $lname, $mobile, $pin);

            if (!$stmt_admin_w->execute()) {
                $response['message'] = "Admin_w insertion failed: " . $stmt_admin_w->error;
                echo json_encode($response);
                exit;
            }
            $stmt_admin_w->close();

            // Insert into users
            $stmt_users = $conn->prepare("INSERT INTO users (fname, lname, mobile, pin, role, _creator, _regdate, _regtime) 
                                          VALUES (?, ?, ?, ?, ?, ?, 
                                          CONVERT_TZ(NOW(), '+00:00', '+05:30'), 
                                          CONVERT_TZ(NOW(), '+00:00', '+05:30'))");
            $stmt_users->bind_param("ssssss", $fname, $lname, $mobile, $pin, $role, $creator);

            if ($stmt_users->execute()) {
                $response['success'] = true;
                $response['message'] = 'Wing Admin Added Successfully!';
                $response['id'] = $stmt_users->insert_id;
            } else {
                $response['message'] = "User insertion failed: " . $stmt_users->error;
            }
            $stmt_users->close();
        } else {
            $response['message'] = "Society Admin with this mobile number not found!";
        }
    }

    $conn->close();
} catch (Exception $e) {
    $response['message'] = "Exception: " . $e->getMessage();
}

echo json_encode($response);
?>
