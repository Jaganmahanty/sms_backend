<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
    
require_once "connection/db.php";

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {
    $uploadDir = "uploads/";  
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if not exists
    }

    $imageName = basename($_FILES["image"]["name"]);
    $targetFilePath = $uploadDir . time() . "_" . $imageName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        $sql = "INSERT INTO images (image_path) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $targetFilePath);

        if ($stmt->execute()) {
            $response["status"] = "success";
            $response["message"] = "Image uploaded successfully.";
            $response["image_url"] = "https://bearpridejewelry.com/" . $targetFilePath;
        } else {
            $response["message"] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $response["message"] = "Failed to upload image.";
    }
} else {
    $response["message"] = "Invalid request.";
}

$conn->close();
echo json_encode($response);
?>
