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
    if ($_SERVER['REQUEST_METHOD'] == "POST") {

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
        $pin = trim($data['pin']);
        $creator = "System";
        // $creator = trim($data['user']);
        $role = "temp";

        if (empty($data['fname']) || empty($data['lname']) || empty($data['mobile']) || empty($data['pin'])) {
            $response['message'] = "All Fields Are Required !!!";
            echo json_encode($response);
            exit;
        }

        //Insert new user ..
        if ($id == 0) {
            // Check for existing user ..
            $check_user = 'select id from users where mobile = ?';
            $check_stmt = $conn->prepare($check_user);
            $check_stmt->bind_param("s", $mobile);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $response['message'] = "User with this mobile number already registered !";
                echo json_encode($response);
                exit;
            }
            $check_stmt->close();

            $sql = "insert into users (fname, lname, mobile, pin, role, _creator, _regdate, _regtime) 
                    VALUES (?, ?, ?, ?, ?, ?, 
                    CONVERT_TZ(NOW(), '+00:00', '+05:30'), 
                    CONVERT_TZ(NOW(), '+00:00', '+05:30'))";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $fname, $lname, $mobile, $pin, $role, $creator);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'User Registered Successfully !!';
                $response['id'] = $stmt->insert_id;
            } else {
                $response['message'] = "Registration failed " . $stmt->error;
            }

        } else {
            // Update existing user ..
            $check_sql = "select id from users where id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                $response['message'] = "User not found !!";
                echo json_encode($response);
                exit;
            }
            $check_stmt->close();

            $sql = "update users set fname = ?, lname = ?, mobile = ?, pin = ?, _modifyuser = ?,
                    _modifydate = CONVERT_TZ(NOW(), '+00:00', '+05:30'), 
                    _modifytime  = CONVERT_TZ(NOW(), '+00:00', '+05:30') 
                    where id = ? ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $fname, $lname, $mobile, $pin, $creator, $id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "User updated successfully !!";
                $response['id'] = $id;
            } else {
                $response['message'] = "Update failed : " . $stmt->$error;
            }
        }

    } else {
        $response['message'] = 'Invalid request method used';
    }

    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);

?>