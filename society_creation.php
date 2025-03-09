<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once "connection/db.php";

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
        $response['message'] = "Invalid request method, use POST!";
        echo json_encode($response);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["society"], $data["wings"])) {
        $response['message'] = "Invalid JSON input!";
        echo json_encode($response);
        exit;
    }

    // Start with Society Data
    $user_mobile = trim($data["mobile"]);
    $socName = trim($data["society"]["socName"]);
    $socAddr = trim($data["society"]["socAddr"]);

    // Check if Society exists
    $sql = "select id, accesscode from societies where name = ? and addr = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $socName, $socAddr);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($societyId, $existingAccessCode);
        $stmt->fetch();
        $stmt->close();

        $response['success'] = false;
        $response['message'] = "Society already exists!";
        $response['soc'] = [
            'id' => $societyId,
            'name' => $socName,
            'address' => $socAddr,
            'accessCode' => $existingAccessCode
        ];

        echo json_encode($response);
        exit;
    } else {

        do {
            $accessCode = rand(10000000, 99999999);
            $stmt = $conn->prepare("select id from societies where accesscode = ?");
            $stmt->bind_param("i", $accessCode);
            $stmt->execute();
            $stmt->store_result();
            $codeExists = $stmt->num_rows > 0;
            $stmt->close();
        } while ($codeExists);

        // Insert Society if it doesn't exist
        $stmt = $conn->prepare("insert into societies (name, addr, accesscode) values (?, ?, ?)");
        $stmt->bind_param("ssi", $socName, $socAddr, $accessCode);
        $stmt->execute();
        $societyId = $stmt->insert_id;
        $stmt->close();
    }

    // Now Insert Wings and Flats
    $wings = $data["wings"];
    foreach ($wings as $wing) {
        $wingName = trim($wing["wingName"]);
        $noOfFloors = intval($wing["noofFloor"]);
        $flatsPerFloor = intval($wing["flatsPerFloor"]);

        // Insert Wing
        $stmt = $conn->prepare("insert into wings (controlid, w_name, no_floor, flat_per_floor) values (?, ?, ?, ?)");
        $stmt->bind_param("isii", $societyId, $wingName, $noOfFloors, $flatsPerFloor);
        $stmt->execute();
        $wingId = $stmt->insert_id;
        $stmt->close();

        // Insert Flats
        $flatStatus = $wing["flats"];
        foreach ($flatStatus as $flat) {
            $flatNumber = trim($flat["flat"]);
            $active = trim($flat["active"]);

            $stmt = $conn->prepare("insert into flats (controlid, parcontrolid, flat_no, isactive) values (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $wingId, $societyId, $flatNumber, $active);
            $stmt->execute();
            $stmt->close();
        }
    }

    $stmt = $conn->prepare("select fname, lname, pin from users where mobile = ?");
    $stmt->bind_param("s", $user_mobile);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($fname, $lname, $pin);
    $stmt->fetch();

    // Set user role to sco_admin
    $stmt = $conn->prepare("update users set role = 'soc_admin' where mobile = ?");
    $stmt->bind_param("s", $user_mobile);
    $stmt->execute();

    // insert into soc_admin
    $stmt = $conn->prepare("Insert into admin_s (accesscode, fname, lname, mobile, pin) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $accessCode, $fname, $lname, $user_mobile, $pin);
    $stmt->execute();

    $response['success'] = true;
    $response['message'] = "Society created successfully !!";
    $response['soc'] = [
        'id' => $societyId,
        'name' => $socName,
        'address' => $socAddr,
        'accesscode' => $accessCode
    ];
    echo json_encode($response);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}

$conn->close();
?>