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

// Debugging: Log received data
error_log("Received data - Shop ID: $shop_id, Gallery: " . print_r($gallery, true));

$conn->begin_transaction();
try {
    // Get existing categories with both id and name
    $existing_categories = [];
    $res = $conn->query("SELECT id, category_name FROM {$TABLE_PREFIX}studio_shop_categories WHERE shop_id = $shop_id");
    while ($row = $res->fetch_assoc()) {
        $existing_categories[$row['id'] = $row['category_name'];
    }

    // Also create a name-to-id mapping for easier lookup
    $category_name_to_id = array_flip($existing_categories);

    $existing_images = [];
    $res = $conn->query("SELECT id, category_id, image_url FROM {$TABLE_PREFIX}studio_shop_catgorie_images WHERE shop_id = $shop_id");
    while ($row = $res->fetch_assoc()) {
        $existing_images[$row['category_id']][$row['id']] = $row['image_url'];
    }

    // Prepare statements
    $insert_cat_stmt = $conn->prepare("INSERT INTO {$TABLE_PREFIX}studio_shop_categories (shop_id, category_name) VALUES (?, ?)");
    $update_cat_stmt = $conn->prepare("UPDATE {$TABLE_PREFIX}studio_shop_categories SET category_name = ? WHERE id = ?");
    $insert_img_stmt = $conn->prepare("INSERT INTO {$TABLE_PREFIX}studio_shop_catgorie_images (shop_id, category_id, image_url) VALUES (?, ?, ?)");
    $delete_img_stmt = $conn->prepare("DELETE FROM {$TABLE_PREFIX}studio_shop_catgorie_images WHERE id = ?");
    $delete_cat_stmt = $conn->prepare("DELETE FROM {$TABLE_PREFIX}studio_shop_categories WHERE id = ?");

    foreach ($gallery as $entry) {
        $category_id = isset($entry['category_id']) ? intval($entry['category_id']) : null;
        $category_name = trim($entry['category_name'] ?? '');
        $images = $entry['images'] ?? [];

        if (empty($category_name) {
            continue;
        }

        // Debugging: Log category processing
        error_log("Processing category - ID: $category_id, Name: $category_name");

        // Handle category - first try by ID, then by name
        if ($category_id && isset($existing_categories[$category_id])) {
            // Category exists by ID - update if name changed
            if ($existing_categories[$category_id] !== $category_name) {
                $update_cat_stmt->bind_param("si", $category_name, $category_id);
                $update_cat_stmt->execute();
                error_log("Updated category ID $category_id to name '$category_name'");
            }
        } else {
            // Try to find by name if ID not provided or not found
            if (isset($category_name_to_id[$category_name])) {
                $category_id = $category_name_to_id[$category_name];
                error_log("Found existing category by name '$category_name' with ID $category_id");
            } else {
                // Create new category
                $insert_cat_stmt->bind_param("is", $shop_id, $category_name);
                $insert_cat_stmt->execute();
                $category_id = $insert_cat_stmt->insert_id;
                $existing_categories[$category_id] = $category_name;
                $category_name_to_id[$category_name] = $category_id;
                error_log("Created new category '$category_name' with ID $category_id");
            }
        }

        // Process images for this category
        $current_image_ids = [];
        foreach ($images as $img) {
            if (!empty($img['id'])) {
                // Existing image - just track the ID
                $current_image_ids[] = intval($img['id']);
            } elseif (!empty($img['base64']) && preg_match('/^data:image\/(\w+);base64,/', $img['base64'], $matches)) {
                // New image - process base64 data
                $image_type = strtolower($matches[1]);
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($image_type, $allowed_types)) continue;

                $image_data = base64_decode(substr($img['base64'], strpos($img['base64'], ',') + 1));
                if ($image_data === false) continue;

                $filename = 'shop_' . $shop_id . '_' . $category_id . '_' . uniqid() . '.' . $image_type;
                $filepath = $upload_dir . $filename;
                $url = 'https://678photo.com/studio_shop_galary/' . $filename;

                if (file_put_contents($filepath, $image_data) === false) {
                    throw new Exception("Failed to save image $filename");
                }

                $insert_img_stmt->bind_param("iis", $shop_id, $category_id, $url);
                $insert_img_stmt->execute();
                $current_image_ids[] = $insert_img_stmt->insert_id;
                error_log("Added new image to category ID $category_id");
            }
        }

        // Delete images that were removed from this category
        $existing_in_cat = $existing_images[$category_id] ?? [];
        foreach ($existing_in_cat as $img_id => $img_url) {
            if (!in_array($img_id, $current_image_ids)) {
                // Delete file
                $img_path = $upload_dir . basename($img_url);
                if (file_exists($img_path)) {
                    if (!unlink($img_path)) {
                        error_log("Warning: Failed to delete image file $img_path");
                    }
                }

                $delete_img_stmt->bind_param("i", $img_id);
                $delete_img_stmt->execute();
                error_log("Deleted image ID $img_id from category ID $category_id");
            }
        }
    }

    // Delete categories that are no longer present
    $current_category_ids = [];
    foreach ($gallery as $entry) {
        if (isset($entry['category_id'])) {
            $current_category_ids[] = intval($entry['category_id']);
        }
    }
    
    foreach ($existing_categories as $cat_id => $cat_name) {
        if (!in_array($cat_id, $current_category_ids)) {
            // First delete all images in this category
            $delete_img_stmt->bind_param("i", $cat_id);
            $delete_img_stmt->execute();
            
            // Then delete the category itself
            $delete_cat_stmt->bind_param("i", $cat_id);
            $delete_cat_stmt->execute();
            error_log("Deleted category ID $cat_id ('$cat_name') as it was removed");
        }
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Gallery updated successfully."]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    error_log("Error in API: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>