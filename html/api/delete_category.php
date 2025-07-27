<?php
include_once("config/db_conection.php");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$data = json_decode(file_get_contents("php://input"), true);
$category_id = $data['category_id'] ?? null;

if (!$category_id) {
    echo json_encode(["error" => "Missing category_id"]);
    exit;
}

// First delete related images
$conn->query("DELETE FROM studio_shop_catgorie_images WHERE category_id = $category_id");

// Then delete the category
$del_stmt = $conn->prepare("DELETE FROM studio_shop_categories WHERE id = ?");
$del_stmt->bind_param("i", $category_id);

if ($del_stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Category deleted"]);
} else {
    echo json_encode(["error" => $del_stmt->error]);
}
$conn->close();