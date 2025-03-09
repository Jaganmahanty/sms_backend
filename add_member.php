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

        if (
            empty($data['accesscode']) ||
            // empty($data['fname']) ||
            // empty($data['lname']) ||
            empty($data['mobile'])
        ) {
            $response['message'] = "All fields are required!";
            echo json_encode($response);
            exit;
        }

        $accesscode = trim($data['accesscode']);
        $mobile = trim($data['mobile']);

        // Check if `accesscode` exists in `societies`
        $check_society_query = "select name from societies where accesscode = ?";
        $stmt = $conn->prepare($check_society_query);
        $stmt->bind_param("s", $accesscode);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $response['message'] = "Invalid accesscode! Society not found .";
            echo json_encode($response);
            exit;
        }
        $stmt->close();

        // Get `id` from both `admin_s` and `admin_w`
        $check_admin_query = "select s.id as sa_id, w.id as wa_id 
                              from admin_s s 
                              inner join admin_w w on s.accesscode = w.accesscode
                              where s.accesscode = ?";

        $stmt = $conn->prepare($check_admin_query);
        $stmt->bind_param("s", $accesscode);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $response['message'] = "No matching admins found for this accesscode !";
            echo json_encode($response);
            exit;
        }
        $stmt->bind_result($sa_id, $wa_id);
        $stmt->fetch();
        $stmt->close();

        $query = "select fname, lname, pin from users where mobile = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $response['message'] = "No user found with this mobile number!";
            echo json_encode($response);
            exit;
        }

        $stmt->bind_result($fname, $lname, $pin);
        $stmt->fetch();
        $stmt->close();

        // Insert new member into the `members` table
        $insert_query = "insert into member 
        (fname, lname, mobile, pin, accesscode, controlid, parcontrolid, created_at, isapproved) 
        values (?, ?, ?, ?, ?, ?, ?, CONVERT_TZ(NOW(), '+00:00', '+05:30'), 'N')";

        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssssss", $fname, $lname, $mobile, $pin, $accesscode, $wa_id, $sa_id);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Member added successfully !";
            $response['id'] = $stmt->insert_id;
        } else {
            $response['message'] = "Failed to insert member: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['message'] = "Invalid request method!";
    }

    $conn->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>