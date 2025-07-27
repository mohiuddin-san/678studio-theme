<?php
// CORS & headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
header("Content-Type: application/json; charset=UTF-8");

include_once("config/db_conection.php");
$TABLE_PREFIX = '';
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/studio_shop_galary/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$data = json_decode(file_get_contents("php://input"), true);
$shop_id = intval($data['shop_id'] ?? 0);
$gallery = $data['gallery'] ?? [];

if (!$shop_id || !is_array($gallery)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing shop_id/gallery"]);
    exit();
}

$conn->begin_transaction();
try {
    $cat_stmt = $conn->prepare("SELECT id FROM {$TABLE_PREFIX}studio_shop_categories WHERE shop_id = ? AND category_name = ?");
    $insert_cat_stmt = $conn->prepare("INSERT INTO {$TABLE_PREFIX}studio_shop_categories (shop_id, category_name) VALUES (?, ?)");
    $img_stmt = $conn->prepare("INSERT INTO {$TABLE_PREFIX}studio_shop_catgorie_images (shop_id, category_id, image_url) VALUES (?, ?, ?)");

    foreach ($gallery as $entry) {
        $category_name = trim($entry['category_name'] ?? '');
        $images = $entry['images'] ?? [];
        if (!$category_name || !is_array($images)) continue;

        // Check category existence
        $cat_stmt->bind_param("is", $shop_id, $category_name);
        $cat_stmt->execute();
        $cat_stmt->store_result();
        $cat_id = null;

        if ($cat_stmt->num_rows > 0) {
            $cat_stmt->bind_result($cat_id);
            $cat_stmt->fetch();
        } else {
            $insert_cat_stmt->bind_param("is", $shop_id, $category_name);
            $insert_cat_stmt->execute();
            $cat_id = $insert_cat_stmt->insert_id;
        }

        // Save each image
        foreach ($images as $index => $image_data) {
            if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $matches)) {
                $image_type = $matches[1];
                $image_data = base64_decode(substr($image_data, strpos($image_data, ',') + 1));
                if ($image_data === false) continue;

                $filename = 'shop_' . $shop_id . '_' . $cat_id . '_' . time() . '_' . $index . '.' . $image_type;
                $filepath = $upload_dir . $filename;
                $url = 'https://678photo.com/studio_shop_galary/' . $filename;

                if (file_put_contents($filepath, $image_data) === false) {
                    throw new Exception("Failed to save image $filename");
                }

                $img_stmt->bind_param("iis", $shop_id, $cat_id, $url);
                $img_stmt->execute();
            }
        }
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Images uploaded by category"]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
$conn->close();
?>