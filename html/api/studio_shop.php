<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json; charset=UTF-8");

include_once("config/db_conection.php");
include_once("config/common_functions.php");

// Define table prefix
$TABLE_PREFIX = '';

// Upload directory setup
$upload_dir = get_upload_directory();
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!is_writable($upload_dir)) {
    error_log("Upload directory not writable: $upload_dir");
    http_response_code(500);
    echo json_encode(["error" => "Upload directory is not writable"]);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);
error_log("Received data size: " . (strlen(json_encode($data)) / 1024) . "KB");

// Validation
if (empty(trim($data['name'])) || empty(trim($data['address']))) {
    error_log("Validation failed: name/address missing");
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields: name and address"]);
    exit();
}

// Sanitize
$company_email = isset($data['company_email']) ? filter_var(trim($data['company_email']), FILTER_SANITIZE_EMAIL) : null;
$phone = isset($data['phone']) ? filter_var(trim($data['phone']), FILTER_SANITIZE_STRING) : null;
$nearest_station = isset($data['nearest_station']) ? filter_var(trim($data['nearest_station']), FILTER_SANITIZE_STRING) : null;
$business_hours = isset($data['business_hours']) ? filter_var(trim($data['business_hours']), FILTER_SANITIZE_STRING) : null;
$holidays = isset($data['holidays']) ? filter_var(trim($data['holidays']), FILTER_SANITIZE_STRING) : null;
$map_url = isset($data['map_url']) ? filter_var(trim($data['map_url']), FILTER_SANITIZE_URL) : null;

// Begin DB transaction
$conn->begin_transaction();

try {
    // Insert shop
    $stmt = $conn->prepare("INSERT INTO {$TABLE_PREFIX}studio_shops 
        (name, address, company_email, phone, nearest_station, business_hours, holidays, map_url, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("ssssssss", $data['name'], $data['address'], $company_email, $phone, $nearest_station, $business_hours, $holidays, $map_url);

    if (!$stmt->execute()) {
        throw new Exception("Shop insert failed: " . $stmt->error);
    }

    $shop_id = $stmt->insert_id;
    $stmt->close();
    error_log("Shop inserted with ID: $shop_id");

    // Image upload (only 1 image allowed)
    $image_urls = [];
    if (isset($data['gallery_images']) && is_array($data['gallery_images']) && !empty($data['gallery_images'])) {
        $image_data = $data['gallery_images'][0];
        error_log("Image string received (truncated): " . substr($image_data, 0, 30));

        if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $matches)) {
            $image_type = strtolower($matches[1]);

            if (!in_array($image_type, ['png', 'jpg', 'jpeg', 'gif'])) {
                throw new Exception("Unsupported image type: $image_type");
            }

            $image_base64 = substr($image_data, strpos($image_data, ',') + 1);
            $decoded_image = base64_decode($image_base64);

            if ($decoded_image === false) {
                throw new Exception("base64_decode failed — invalid base64 format");
            }

            $filename = 'shop_' . $shop_id . '_' . time() . '.' . $image_type;
            $filepath = $upload_dir . $filename;
            $url = generate_image_url($filename);

            if (file_put_contents($filepath, $decoded_image) === false) {
                throw new Exception("Failed to write image to path: $filepath");
            }

            // Insert image into DB
            $img_stmt = $conn->prepare("INSERT INTO {$TABLE_PREFIX}studio_shop_images (shop_id, image_url) VALUES (?, ?)");
            $img_stmt->bind_param("is", $shop_id, $url);
            if (!$img_stmt->execute()) {
                throw new Exception("Failed to insert image URL: " . $img_stmt->error);
            }
            $img_stmt->close();

            $image_urls[] = $url;
            error_log("Image saved: $filepath and inserted URL: $url");
        } else {
            throw new Exception("Invalid image format. Does not match expected base64 header");
        }
    } else {
        error_log("No gallery image provided");
    }

    // Commit transaction
    $conn->commit();
    error_log("Transaction committed");

    echo json_encode([
        "success" => true,
        "message" => "Shop and image added successfully",
        "shop_id" => $shop_id,
        "image_urls" => $image_urls
    ]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error occurred: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>