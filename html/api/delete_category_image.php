<?php
include_once("config/db_conection.php");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$data = json_decode(file_get_contents("php://input"), true);
$image_id = $data['image_id'] ?? null;

if (!$image_id) {
    echo json_encode(["error" => "Missing image_id"]);
    exit;
}

$del_stmt = $conn->prepare("DELETE FROM studio_shop_catgorie_images WHERE id = ?");
$del_stmt->bind_param("i", $image_id);

if ($del_stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Image deleted from category"]);
} else {
    echo json_encode(["error" => $del_stmt->error]);
}
$conn->close();