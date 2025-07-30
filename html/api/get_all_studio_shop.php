<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
$TABLE_PREFIX = ''; // Example: 'wp_' if you have prefix

// Note: Base64 to file conversion is no longer needed after migration
// All images are now stored as file URLs in the database

try {
    // Fetch all shops
    $stmt = $conn->prepare("SELECT id, name, address, phone, nearest_station, business_hours, holidays, map_url, created_at, company_email, main_image  
                            FROM {$TABLE_PREFIX}studio_shops");
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch shops: " . $stmt->error);
    }
    $shops_result = $stmt->get_result();
    $shops = [];

    while ($shop = $shops_result->fetch_assoc()) {
        $shop_id = $shop['id'];

        // 1️⃣ General shop images with image IDs
        $img_stmt = $conn->prepare("SELECT id, image_url FROM {$TABLE_PREFIX}studio_shop_images WHERE shop_id = ?");
        $img_stmt->bind_param("i", $shop_id);
        if (!$img_stmt->execute()) {
            throw new Exception("Failed to fetch images for shop_id $shop_id: " . $img_stmt->error);
        }
        $img_result = $img_stmt->get_result();
        $image_urls = [];
        $image_data = [];
        while ($img = $img_result->fetch_assoc()) {
            $image_urls[] = $img['image_url']; // Keep for backward compatibility
            $image_data[] = [
                'id' => $img['id'],
                'url' => $img['image_url']
            ];
        }
        $img_stmt->close();
        $shop['image_urls'] = $image_urls; // Keep for backward compatibility
        $shop['main_gallery_images'] = $image_data; // New structure with IDs
        
        // main_image is now already a file URL from database

        // 2️⃣ Category-wise images with category name and image ID (optional - only if tables exist)
        $category_images = [];
        
        // Check if category tables exist before querying
        $table_check = $conn->query("SHOW TABLES LIKE '{$TABLE_PREFIX}studio_shop_catgorie_images'");
        if ($table_check && $table_check->num_rows > 0) {
            $cat_stmt = $conn->prepare("
                SELECT c.category_name, i.image_url, i.id as image_id
                FROM {$TABLE_PREFIX}studio_shop_catgorie_images i
                JOIN {$TABLE_PREFIX}studio_shop_categories c ON i.category_id = c.id
                WHERE i.shop_id = ?
            ");
            $cat_stmt->bind_param("i", $shop_id);
            if ($cat_stmt->execute()) {
                $cat_result = $cat_stmt->get_result();

                while ($row = $cat_result->fetch_assoc()) {
                    $category = $row['category_name'];
                    $img_url = $row['image_url'];
                    $img_id = $row['image_id'];

                    if (!isset($category_images[$category])) {
                        $category_images[$category] = [];
                    }
                    $category_images[$category][] = [
                        'url' => $img_url,
                        'id' => $img_id
                    ];
                }
            }
            $cat_stmt->close();
        }
        
        $shop['category_images'] = $category_images;

        $shops[] = $shop;
    }
    $stmt->close();

    // Final JSON response
    echo json_encode([
        "success" => true,
        "message" => "Shops retrieved successfully",
        "shops" => $shops
    ]);
} catch (Exception $e) {
    error_log("Error in get_studio_shops.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>