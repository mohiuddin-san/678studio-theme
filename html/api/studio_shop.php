<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Content-Type
header("Content-Type: application/json; charset=UTF-8");

// DB connection
include_once("config/db_conection.php");

// Define table prefix (set to empty string if no prefix is needed)
$TABLE_PREFIX = ''; // Change to 'wp_' or other prefix if required

// Ensure upload directory exists and is writable
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/studio_shop_galary/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!is_writable($upload_dir)) {
    error_log("Upload directory not writable: $upload_dir");
    http_response_code(500);
    echo json_encode(["error" => "Upload directory is not writable"]);
    exit();
}

// JSON input
$data = json_decode(file_get_contents("php://input"), true);
error_log("Received API data, size: " . (strlen(json_encode($data)) / 1024) . "KB");

// Validate required fields
if (!isset($data['name']) || !isset($data['address']) || empty(trim($data['name'])) || empty(trim($data['address']))) {
    error_log("Validation failed: Missing or empty required fields");
    http_response_code(400);
    echo json_encode(["error" => "Missing or empty required fields: name and address"]);
    exit();
}

// Sanitize optional fields
$company_email = isset($data['company_email']) ? filter_var(trim($data['company_email']), FILTER_SANITIZE_EMAIL) : null;
$phone = isset($data['phone']) ? filter_var(trim($data['phone']), FILTER_SANITIZE_STRING) : null;
$nearest_station = isset($data['nearest_station']) ? filter_var(trim($data['nearest_station']), FILTER_SANITIZE_STRING) : null;
$business_hours = isset($data['business_hours']) ? filter_var(trim($data['business_hours']), FILTER_SANITIZE_STRING) : null;
$holidays = isset($data['holidays']) ? filter_var(trim($data['holidays']), FILTER_SANITIZE_STRING) : null;
$map_url = isset($data['map_url']) ? filter_var(trim($data['map_url']), FILTER_SANITIZE_URL) : null;

// Start transaction
$conn->begin_transaction();

try {
    // Insert shop data
    $stmt = $conn->prepare("INSERT INTO {$TABLE_PREFIX}studio_shops 
        (name, address, company_email, phone, nearest_station, business_hours, holidays, map_url, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param(
        "ssssssss",
        $data['name'],
        $data['address'],
        $company_email,
        $phone,
        $nearest_station,
        $business_hours,
        $holidays,
        $map_url
    );

    if (!$stmt->execute()) {
        throw new Exception("Shop insert failed: " . $stmt->error);
    }
    $shop_id = $stmt->insert_id;
    $stmt->close();
    error_log("Shop inserted, shop_id: $shop_id");
    error_log("Company Email saved: $company_email");

    // Handle gallery images
    $image_urls = [];
    if (isset($data['gallery_images']) && is_array($data['gallery_images']) && !empty($data['gallery_images'])) {
        error_log("Processing " . count($data['gallery_images']) . " gallery images");
        $img_stmt = $conn->prepare("INSERT INTO {$TABLE_PREFIX}studio_shop_images (shop_id, image_url) VALUES (?, ?)");
        foreach ($data['gallery_images'] as $index => $image_data) {
            error_log("Processing image $index, size: " . (strlen($image_data) / 1024) . "KB");
            if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $matches)) {
                $image_type = $matches[1];
                if (!in_array($image_type, ['png', 'jpg', 'jpeg', 'gif'])) {
                    error_log("Unsupported image type at index $index: $image_type");
                    continue;
                }
                $image_data = substr($image_data, strpos($image_data, ',') + 1);
                $image_size = strlen($image_data) * 0.75; // Approximate size in bytes
                if ($image_size > 5 * 1024 * 1024) {
                    error_log("Image at index $index too large: " . ($image_size / 1024 / 1024) . "MB");
                    continue;
                }
                $image_data = base64_decode($image_data);
                if ($image_data === false) {
                    error_log("Invalid base64 image data at index $index");
                    continue;
                }

                $filename = 'shop_' . $shop_id . '_' . time() . '_' . $index . '.' . $image_type;
                $filepath = $upload_dir . $filename;
                $url = 'https://678photo.com/studio_shop_galary/' . $filename;

                if (file_put_contents($filepath, $image_data) === false) {
                    error_log("Failed to save image at index $index to $filepath");
                    throw new Exception("Failed to save image at index $index");
                }
                error_log("Image saved: $filepath");

                $img_stmt->bind_param("is", $shop_id, $url);
                if (!$img_stmt->execute()) {
                    error_log("Image insert failed at index $index: " . $img_stmt->error);
                    throw new Exception("Image insert failed at index $index: " . $img_stmt->error);
                }
                $image_urls[] = $url;
                error_log("Image URL inserted: $url");
            } else {
                error_log("Invalid image format at index $index");
                continue;
            }
        }
        $img_stmt->close();
    } else {
        error_log("No gallery images provided or invalid format");
    }

    // Commit transaction
    $conn->commit();
    error_log("Transaction committed, saved " . count($image_urls) . " images");
    echo json_encode([
        "success" => true,
        "message" => "Shop and images added successfully",
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