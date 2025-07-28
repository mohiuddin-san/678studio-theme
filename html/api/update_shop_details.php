<?php
// Use WordPress database connection
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
global $wpdb;
$conn = $wpdb->dbh; // Use WordPress MySQLi connection
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/wp-content/debug.log');

// Log incoming request data
error_log("Raw POST data: " . file_get_contents("php://input"));
error_log("POST array: " . print_r($_POST, true));
error_log("FILES array: " . print_r($_FILES, true));

// Define table prefix
$TABLE_PREFIX = $wpdb->prefix;

// Upload directory setup
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

// Get form data
$shop_id = isset($_POST['shop_id']) ? filter_var(trim($_POST['shop_id']), FILTER_VALIDATE_INT) : null;
$name = isset($_POST['name']) ? filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING) : '';
$address = isset($_POST['address']) ? filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING) : '';
$phone = isset($_POST['phone']) ? filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING) : '';
$nearest_station = isset($_POST['nearest_station']) ? filter_var(trim($_POST['nearest_station']), FILTER_SANITIZE_STRING) : '';
$business_hours = isset($_POST['business_hours']) ? filter_var(trim($_POST['business_hours']), FILTER_SANITIZE_STRING) : '';
$holidays = isset($_POST['holidays']) ? filter_var(trim($_POST['holidays']), FILTER_SANITIZE_STRING) : '';
$map_url = isset($_POST['map_url']) ? filter_var(trim($_POST['map_url']), FILTER_SANITIZE_URL) : '';
$company_email = isset($_POST['company_email']) ? filter_var(trim($_POST['company_email']), FILTER_SANITIZE_EMAIL) : '';

if (!$shop_id || $shop_id <= 0) {
    error_log("Missing or invalid shop_id: " . ($shop_id ?? 'null'));
    http_response_code(400);
    echo json_encode(["error" => "Missing or invalid shop_id"]);
    exit();
}

// Check database connection
if (!$conn) {
    error_log("Database connection failed: " . $wpdb->last_error);
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Begin DB transaction
$conn->begin_transaction();

try {
    // Update shop details
    $stmt = $conn->prepare("
        UPDATE {$TABLE_PREFIX}studio_shops SET 
            name=?, address=?, phone=?, nearest_station=?, 
            business_hours=?, holidays=?, map_url=?, company_email=?
        WHERE id=?
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssssssssi", $name, $address, $phone, $nearest_station, $business_hours, $holidays, $map_url, $company_email, $shop_id);

    if (!$stmt->execute()) {
        throw new Exception("Shop update failed: " . $stmt->error);
    }
    $stmt->close();
    error_log("Shop updated with ID: $shop_id");

    // Image update (only 1 image allowed)
    $image_urls = [];
    if (isset($_FILES['gallery_images_flat']) && $_FILES['gallery_images_flat']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['gallery_images_flat'];
        $image_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($image_type, ['png', 'jpg', 'jpeg', 'gif'])) {
            throw new Exception("Unsupported image type: $image_type");
        }

        if ($file['size'] > 50 * 1024 * 1024) {
            throw new Exception("Image size exceeds 50MB limit");
        }

        // Delete existing image if any
        $old_img_stmt = $conn->prepare("SELECT image_url FROM {$TABLE_PREFIX}studio_shop_images WHERE shop_id = ?");
        if (!$old_img_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $old_img_stmt->bind_param("i", $shop_id);
        $old_img_stmt->execute();
        $result = $old_img_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $old_image_path = str_replace('https://678photo.com/studio_shop_galary/', $upload_dir, $row['image_url']);
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
                error_log("Deleted old image: $old_image_path");
            }
        }
        $old_img_stmt->close();

        // Save new image
        $filename = 'shop_' . $shop_id . '_' . time() . '.' . $image_type;
        $filepath = $upload_dir . $filename;
        $url = 'https://678photo.com/studio_shop_galary/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception("Failed to move uploaded file to: $filepath");
        }

        // Update or insert image in DB
        $img_stmt = $conn->prepare("
            INSERT INTO {$TABLE_PREFIX}studio_shop_images (shop_id, image_url) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE image_url = ?
        ");
        if (!$img_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $img_stmt->bind_param("iss", $shop_id, $url, $url);
        if (!$img_stmt->execute()) {
            throw new Exception("Failed to update/insert image URL: " . $img_stmt->error);
        }
        $img_stmt->close();

        $image_urls[] = $url;
        error_log("Image saved: $filepath and updated URL: $url");
    }

    // Commit transaction
    $conn->commit();
    error_log("Transaction committed for shop_id: $shop_id");

    echo json_encode([
        "success" => true,
        "message" => "Shop updated successfully",
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