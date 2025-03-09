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
        $data = json_decode(file_get_contents("php://input"), true);

        // Check if required fields are provided
        if (!isset($data['mobile']) || !isset($data['pin'])) {
            $response['message'] = "Mobile number and new password are required !";
            echo json_encode($response);
            exit;
        }

        $mobile = trim($data['mobile']);
        $pin = trim($data['pin']);
        // $table = trim($data['table']);

        // Start transaction
        $conn->begin_transaction();

        // Update password in 'users' table
        $update_users_sql = "UPDATE users 
                     SET pin = ?, 
                         _modifyuser = '', 
                         _modifydate = CONVERT_TZ(NOW(), '+00:00', '+05:30'), 
                         _modifytime = CONVERT_TZ(NOW(), '+00:00', '+05:30') 
                     WHERE mobile = ?";
        $update_users_stmt = $conn->prepare($update_users_sql);
        $update_users_stmt->bind_param("ss", $pin, $mobile);
        $update_users_stmt->execute();

        // Update password in 'admin_w' table
        // $update_admin_w_sql = "update $table set pin = ? where mobile = ?";
        // $update_admin_w_stmt = $conn->prepare($update_admin_w_sql);
        // $update_admin_w_stmt->bind_param("ss", $pin, $mobile);
        // $update_admin_w_stmt->execute();

        // Check if any rows were affected in either table
        if ($update_users_stmt->affected_rows > 0) {
            $conn->commit(); // Commit transaction
            $response['success'] = true;
            $response['message'] = "Password updated successfully !";
        } else {
            $conn->rollback(); // Rollback if no update happened
            $response['message'] = "No matching record found or password update failed !";
        }

    } else {
        $response['message'] = "Invalid request method !";
    }

    echo json_encode($response);
    $conn->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}
?>