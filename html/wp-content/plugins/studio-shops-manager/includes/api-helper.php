<?php
/**
 * Helper function to make internal API calls
 * Uses direct database operations instead of HTTP requests
 */

// Include database connection
require_once ABSPATH . 'api/config/db_conection.php';
require_once ABSPATH . 'api/config/common_functions.php';

/**
 * Get or create database connection
 * @return PDO Database connection
 * @throws Exception If connection fails
 */
function get_db_connection() {
    static $mysqli_conn = null;
    
    if ($mysqli_conn !== null) {
        return $mysqli_conn;
    }
    
    // Environment detection
    if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === 'localhost:8080' || $_SERVER['HTTP_HOST'] === 'localhost') {
        // Local environment (Docker)
        $host = "mysql-678studio";
        $db_name = "wordpress_678";
        $username = "wp_user";
        $password = "password";
    } else {
        // Server environment
        $host = "localhost";
        $db_name = "xb592942_sugamonavishop";
        $username = "xb592942_sugamo";
        $password = "Sugamonavi12345";
    }
    
    try {
        $mysqli_conn = new mysqli($host, $username, $password, $db_name);
        
        if ($mysqli_conn->connect_error) {
            throw new Exception('Database connection failed: ' . $mysqli_conn->connect_error);
        }
        
        // 文字セット設定を強化
        if (!$mysqli_conn->set_charset("utf8mb4")) {
            throw new Exception("Error loading character set utf8mb4: " . $mysqli_conn->error);
        }
        
        // 追加の文字エンコーディング設定
        $mysqli_conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $mysqli_conn->query("SET CHARACTER SET utf8mb4");
        
        return $mysqli_conn;
    } catch(Exception $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

// Note: get_upload_directory() function is provided by api/config/common_functions.php

// Note: generate_image_url() function is provided by api/config/common_functions.php

/**
 * Process and save base64 images
 * @param array $images Base64 image data array
 * @param int $shop_id Shop ID
 * @param int $category_id Category ID (optional)
 * @return array Array of saved image URLs
 * @throws Exception If image processing fails
 */
function process_and_save_images($images, $shop_id, $category_id = null) {
    $image_urls = array();
    
    // Use WordPress uploads directory for better compatibility
    $wp_upload_dir = wp_upload_dir();
    $upload_dir = $wp_upload_dir['basedir'] . '/studio-shops/';
    
    
    if (!is_dir($upload_dir)) {
        if (!wp_mkdir_p($upload_dir)) {
            error_log("ERROR - Could not create upload directory: " . $upload_dir);
            return array();
        }
    }
    
    foreach ($images as $index => $image_data) {
        if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $matches)) {
            $image_type = strtolower($matches[1]);
            
            if (!in_array($image_type, ['png', 'jpg', 'jpeg', 'gif'])) {
                continue;
            }
            
            $image_base64 = substr($image_data, strpos($image_data, ',') + 1);
            $decoded_image = base64_decode($image_base64);
            
            if ($decoded_image === false) {
                continue;
            }
            
            $prefix = $category_id ? 'category_' . $shop_id . '_' . $category_id : 'shop_' . $shop_id;
            $filename = $prefix . '_' . time() . '_' . $index . '.' . $image_type;
            $filepath = $upload_dir . $filename;
            
            // Save the actual file to filesystem
            if (file_put_contents($filepath, $decoded_image) !== false) {
                // Generate URL using WordPress uploads directory
                $wp_upload_dir = wp_upload_dir();
                $url = $wp_upload_dir['baseurl'] . '/studio-shops/' . $filename;
                
                // Store the file URL instead of Base64 data
                $image_urls[] = array(
                    'filename' => $filename,
                    'url' => $url, // Store the file URL
                    'filepath' => $filepath
                );
                
                wp_debug_log_info("Image file saved successfully", [
                    'filename' => $filename,
                    'url' => $url,
                    'file_size' => filesize($filepath)
                ]);
            } else {
                wp_debug_log_error("Failed to save image file", [
                    'filename' => $filename,
                    'filepath' => $filepath
                ]);
            }
        }
    }
    
    return $image_urls;
}

/**
 * Insert images into database
 * @param mysqli $conn Database connection
 * @param array $processed_images Processed image data
 * @param int $shop_id Shop ID
 * @param int $category_id Category ID (optional, for category images)
 * @return array URLs of successfully inserted images
 */
function insert_images_to_db($conn, $processed_images, $shop_id, $category_id = null) {
    $inserted_urls = array();
    
    
    foreach ($processed_images as $image_info) {
        if ($category_id) {
            // Category image
            $stmt = $conn->prepare("INSERT INTO studio_shop_catgorie_images (shop_id, category_id, image_url, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $shop_id, $category_id, $image_info['url']);
        } else {
            // Main gallery image - ギャラリー画像をBase64として保存
            $stmt = $conn->prepare("INSERT INTO studio_shop_images (shop_id, image_url, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $shop_id, $image_info['url']);
        }
        
        if ($stmt->execute()) {
            $inserted_urls[] = $image_info['url'];
        } else {
            error_log("ERROR - insert_images_to_db: Failed to insert image: " . $stmt->error);
        }
        $stmt->close();
    }
    
    return $inserted_urls;
}

/**
 * Create standardized API response
 * @param bool $success Success status
 * @param string $message Response message
 * @param array $data Additional data (optional)
 * @return array Standardized response array
 */
function create_api_response($success, $message, $data = array()) {
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($success && !empty($data)) {
        $response = array_merge($response, $data);
    } elseif (!$success) {
        $response['error'] = $message;
    }
    
    return $response;
}

/**
 * Validate required fields in data array
 * @param array $data Data array to validate
 * @param array $required_fields Array of required field names
 * @return array|null Returns error response if validation fails, null if passes
 */
function validate_required_fields($data, $required_fields) {
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && empty(trim($data[$field])))) {
            return create_api_response(false, "Missing required field: {$field}");
        }
    }
    return null;
}

function make_internal_api_call($endpoint, $data = array()) {
    global $conn;
    
    try {
        switch ($endpoint) {
            case 'studio_shop.php':
                return create_studio_shop($data);
            case 'update_shop_details.php':
                return update_studio_shop($data);
            case 'category_image_uploader.php':
                return upload_category_images($data);
            // update_shop_category_images.php removed - unused
            case 'delete_category_image.php':
                return delete_category_image($data);
            case 'delete_main_gallery_image.php':
                return delete_main_gallery_image($data);
            case 'delete_gallery_image.php':
                return delete_gallery_images($data);
            case 'delete_shop.php':
                return delete_shop($data);
            case 'get_all_studio_shop.php':
                return get_all_studio_shops($data);
            default:
                return array(
                    'success' => false,
                    'error' => 'Unknown endpoint: ' . $endpoint
                );
        }
    } catch (Exception $e) {
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

function create_studio_shop($data) {
    global $conn;
    
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        error_log("ERROR - create_studio_shop: Database connection failed: " . $e->getMessage());
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    // Validation
    if (empty(trim($data['name'])) || empty(trim($data['address']))) {
        return array(
            'success' => false,
            'error' => 'Missing required fields: name and address'
        );
    }
    
    // Sanitize
    $company_email = isset($data['company_email']) ? filter_var(trim($data['company_email']), FILTER_SANITIZE_EMAIL) : null;
    $phone = isset($data['phone']) ? sanitize_text_field(trim($data['phone'])) : null;
    $nearest_station = isset($data['nearest_station']) ? sanitize_text_field(trim($data['nearest_station'])) : null;
    $business_hours = isset($data['business_hours']) ? sanitize_text_field(trim($data['business_hours'])) : null;
    $holidays = isset($data['holidays']) ? sanitize_text_field(trim($data['holidays'])) : null;
    $map_url = isset($data['map_url']) ? filter_var(trim($data['map_url']), FILTER_SANITIZE_URL) : null;
    
    // Begin transaction
    $conn->autocommit(false);
    
    try {
        // Handle main image
        $main_image = isset($data['main_image']) ? $data['main_image'] : null;
        
        // Insert shop
        $stmt = $conn->prepare("INSERT INTO studio_shops 
            (name, address, company_email, phone, nearest_station, business_hours, holidays, map_url, main_image, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->bind_param("sssssssss", $data['name'], $data['address'], $company_email, $phone, $nearest_station, $business_hours, $holidays, $map_url, $main_image);
        
        if (!$stmt->execute()) {
            throw new Exception("Shop insert failed: " . $stmt->error);
        }
        
        $shop_id = $stmt->insert_id;
        $stmt->close();
        
        // Handle gallery images
        $image_urls = array();
        
        if (isset($data['gallery_images']) && is_array($data['gallery_images']) && !empty($data['gallery_images'])) {
            wp_debug_log_info("Processing gallery images", ['gallery_count' => count($data['gallery_images']), 'shop_id' => $shop_id]);
            $processed_images = process_and_save_images($data['gallery_images'], $shop_id);
            wp_debug_log_info("Processed gallery images", ['processed_count' => count($processed_images), 'shop_id' => $shop_id]);
            $image_urls = insert_images_to_db($conn, $processed_images, $shop_id);
            wp_debug_log_info("Inserted gallery images to DB", ['inserted_count' => count($image_urls), 'shop_id' => $shop_id]);
        }
        
        $conn->commit();
        
        // キャッシュをクリア
        if (function_exists('clear_studio_data_cache')) {
            clear_studio_data_cache();
        }
        
        // WordPressアクションを発火
        do_action('studio_shop_created', $shop_id);
        
        return array(
            'success' => true,
            'message' => 'Shop and image added successfully',
            'shop_id' => $shop_id,
            'image_urls' => $image_urls
        );
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

// Placeholder functions for other endpoints
function update_studio_shop($data) {
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    // Validation
    if (empty($data['shop_id'])) {
        return array(
            'success' => false,
            'error' => 'Missing shop_id for update'
        );
    }
    
    if (empty(trim($data['name'])) || empty(trim($data['address']))) {
        return array(
            'success' => false,
            'error' => 'Missing required fields: name and address'
        );
    }
    
    $shop_id = intval($data['shop_id']);
    
    // Sanitize
    $company_email = isset($data['company_email']) ? filter_var(trim($data['company_email']), FILTER_SANITIZE_EMAIL) : null;
    $phone = isset($data['phone']) ? sanitize_text_field(trim($data['phone'])) : null;
    $nearest_station = isset($data['nearest_station']) ? sanitize_text_field(trim($data['nearest_station'])) : null;
    $business_hours = isset($data['business_hours']) ? sanitize_text_field(trim($data['business_hours'])) : null;
    $holidays = isset($data['holidays']) ? sanitize_text_field(trim($data['holidays'])) : null;
    $map_url = isset($data['map_url']) ? filter_var(trim($data['map_url']), FILTER_SANITIZE_URL) : null;
    
    // Begin transaction
    $conn->autocommit(false);
    
    try {
        // Handle main image
        $main_image = isset($data['main_image']) ? $data['main_image'] : null;
        
        // Update shop (only update main_image if provided)
        if ($main_image) {
            $stmt = $conn->prepare("UPDATE studio_shops SET 
                name = ?, address = ?, company_email = ?, phone = ?, nearest_station = ?, 
                business_hours = ?, holidays = ?, map_url = ?, main_image = ?, updated_at = NOW()
                WHERE id = ?");
            $stmt->bind_param("sssssssssi", $data['name'], $data['address'], $company_email, $phone, $nearest_station, $business_hours, $holidays, $map_url, $main_image, $shop_id);
        } else {
            $stmt = $conn->prepare("UPDATE studio_shops SET 
                name = ?, address = ?, company_email = ?, phone = ?, nearest_station = ?, 
                business_hours = ?, holidays = ?, map_url = ?, updated_at = NOW()
                WHERE id = ?");
            $stmt->bind_param("ssssssssi", $data['name'], $data['address'], $company_email, $phone, $nearest_station, $business_hours, $holidays, $map_url, $shop_id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Shop update failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        // Handle gallery images (ADD to existing images, don't replace)
        $image_urls = array();
        if (isset($data['gallery_images']) && is_array($data['gallery_images']) && !empty($data['gallery_images'])) {
            $processed_images = process_and_save_images($data['gallery_images'], $shop_id);
            $image_urls = insert_images_to_db($conn, $processed_images, $shop_id);
        }
        
        $conn->commit();
        
        // キャッシュをクリア
        if (function_exists('clear_studio_data_cache')) {
            clear_studio_data_cache();
        }
        
        // WordPressアクションを発火
        do_action('studio_shop_updated', $shop_id);
        
        return array(
            'success' => true,
            'message' => 'Shop updated successfully',
            'shop_id' => $shop_id,
            'image_urls' => $image_urls
        );
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function upload_category_images($data) {
    
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    if (!isset($data['shop_id']) || !isset($data['gallery'])) {
        return array('success' => false, 'error' => 'Missing shop_id or gallery data');
    }
    
    $shop_id = intval($data['shop_id']);
    $gallery = $data['gallery'];
    
    $conn->begin_transaction();
    
    try {
        foreach ($gallery as $category_data) {
            $category_name = $category_data['category_name'];
            $images = $category_data['images'];
            
            // Insert or get category (shop-specific)
            $cat_stmt = $conn->prepare("INSERT IGNORE INTO studio_shop_categories (shop_id, category_name) VALUES (?, ?)");
            $cat_stmt->bind_param("is", $shop_id, $category_name);
            $cat_stmt->execute();
            $cat_stmt->close();
            
            // Get category ID
            $cat_id_stmt = $conn->prepare("SELECT id FROM studio_shop_categories WHERE shop_id = ? AND category_name = ?");
            $cat_id_stmt->bind_param("is", $shop_id, $category_name);
            $cat_id_stmt->execute();
            $result = $cat_id_stmt->get_result();
            $category_id = $result->fetch_assoc()['id'];
            $cat_id_stmt->close();
            
            // Process images
            $processed_images = process_and_save_images($images, $shop_id, $category_id);
            insert_images_to_db($conn, $processed_images, $shop_id, $category_id);
        }
        
        $conn->commit();
        
        // キャッシュをクリア
        if (function_exists('clear_studio_data_cache')) {
            clear_studio_data_cache();
        }
        
        // WordPressアクションを発火
        do_action('studio_category_updated', $shop_id);
        
        return array('success' => true, 'message' => 'Category images uploaded successfully');
        
    } catch (Exception $e) {
        $conn->rollback();
        return array('success' => false, 'error' => $e->getMessage());
    }
}

// update_category_images function removed - unused

function delete_category_image($data) {
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    $image_id = $data['image_id'] ?? null;
    
    if (!$image_id) {
        return array('success' => false, 'error' => 'Missing image_id');
    }
    
    $del_stmt = $conn->prepare("DELETE FROM studio_shop_catgorie_images WHERE id = ?");
    $del_stmt->bind_param("i", $image_id);
    
    if ($del_stmt->execute()) {
        $del_stmt->close();
        
        // キャッシュをクリア
        if (function_exists('clear_studio_data_cache')) {
            clear_studio_data_cache();
        }
        
        return array('success' => true, 'message' => 'Image deleted from category');
    } else {
        $error = $del_stmt->error;
        $del_stmt->close();
        return array('success' => false, 'error' => $error);
    }
}

function delete_entire_category($data) {
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    $shop_id = $data['shop_id'] ?? null;
    $category_name = $data['category_name'] ?? null;
    
    if (!$shop_id || !$category_name) {
        return array('success' => false, 'error' => 'Missing shop_id or category_name');
    }
    
    $conn->begin_transaction();
    
    try {
        // Get category ID first
        $cat_stmt = $conn->prepare("SELECT id FROM studio_shop_categories WHERE category_name = ?");
        $cat_stmt->bind_param("s", $category_name);
        $cat_stmt->execute();
        $result = $cat_stmt->get_result();
        
        if ($result->num_rows === 0) {
            $cat_stmt->close();
            throw new Exception("Category not found: " . $category_name);
        }
        
        $category_row = $result->fetch_assoc();
        $category_id = $category_row['id'];
        $cat_stmt->close();
        
        // Delete all images for this category and shop
        $del_images_stmt = $conn->prepare("DELETE FROM studio_shop_catgorie_images WHERE category_id = ? AND shop_id = ?");
        $del_images_stmt->bind_param("ii", $category_id, $shop_id);
        
        if (!$del_images_stmt->execute()) {
            throw new Exception("Failed to delete category images: " . $del_images_stmt->error);
        }
        
        $deleted_images_count = $del_images_stmt->affected_rows;
        $del_images_stmt->close();
        
        // Check if this category is used by other shops
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM studio_shop_catgorie_images WHERE category_id = ?");
        $check_stmt->bind_param("i", $category_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $remaining_usage = $check_result->fetch_assoc()['count'];
        $check_stmt->close();
        
        // If no other shops use this category, delete the category itself
        if ($remaining_usage == 0) {
            $del_category_stmt = $conn->prepare("DELETE FROM studio_shop_categories WHERE id = ?");
            $del_category_stmt->bind_param("i", $category_id);
            
            if (!$del_category_stmt->execute()) {
                throw new Exception("Failed to delete category: " . $del_category_stmt->error);
            }
            
            $del_category_stmt->close();
        }
        
        $conn->commit();
        
        // キャッシュをクリア
        if (function_exists('clear_studio_data_cache')) {
            clear_studio_data_cache();
        }
        
        // WordPressアクションを発火
        do_action('studio_category_deleted', $shop_id, $category_name);
        
        return array(
            'success' => true, 
            'message' => 'Category deleted successfully',
            'deleted_images' => $deleted_images_count,
            'category_completely_removed' => ($remaining_usage == 0)
        );
        
    } catch (Exception $e) {
        $conn->rollback();
        return array('success' => false, 'error' => $e->getMessage());
    }
}

function delete_gallery_images($data) {
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    $shop_id = $data['shop_id'] ?? null;
    $image_id = $data['image_id'] ?? null;
    $delete_all = isset($data['delete_all']) && $data['delete_all'];
    
    if (!$shop_id) {
        return array('success' => false, 'error' => 'Shop ID is required');
    }
    
    try {
        if ($delete_all) {
            // First get all image URLs for physical file deletion
            $get_stmt = $conn->prepare("SELECT image_url FROM studio_shop_images WHERE shop_id = ?");
            $get_stmt->bind_param("i", $shop_id);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            $image_urls = $result->fetch_all(MYSQLI_ASSOC);
            $get_stmt->close();
            
            // Delete physical files
            $wp_upload_dir = wp_upload_dir();
            $upload_dir = $wp_upload_dir['basedir'] . '/studio-shops/';
            $deleted_files = 0;
            
            foreach ($image_urls as $row) {
                $image_url = $row['image_url'];
                if (!empty($image_url) && strpos($image_url, 'data:image') !== 0) {
                    $filename = basename($image_url);
                    $filepath = $upload_dir . $filename;
                    if (file_exists($filepath) && unlink($filepath)) {
                        $deleted_files++;
                    }
                }
            }
            
            // Delete all gallery images from database
            $stmt = $conn->prepare("DELETE FROM studio_shop_images WHERE shop_id = ?");
            $stmt->bind_param("i", $shop_id);
            $result = $stmt->execute();
            
            if ($result) {
                $deleted_count = $stmt->affected_rows;
                $stmt->close();
                wp_debug_log_info("Deleted gallery images", ['db_count' => $deleted_count, 'file_count' => $deleted_files]);
                return array('success' => true, 'message' => "ギャラリー画像を{$deleted_count}枚削除しました。（ファイル{$deleted_files}個削除）");
            } else {
                $stmt->close();
                return array('success' => false, 'error' => 'Failed to delete gallery images');
            }
        } elseif ($image_id) {
            // Delete single image by ID
            $stmt = $conn->prepare("DELETE FROM studio_shop_images WHERE id = ? AND shop_id = ?");
            $stmt->bind_param("ii", $image_id, $shop_id);
            $result = $stmt->execute();
            
            if ($result && $stmt->affected_rows > 0) {
                $stmt->close();
                return array('success' => true, 'message' => 'ギャラリー画像を削除しました。');
            } else {
                $stmt->close();
                return array('success' => false, 'error' => 'Image not found or failed to delete');
            }
        } else {
            return array('success' => false, 'error' => 'Either image_id or delete_all parameter is required');
        }
    } catch(Exception $e) {
        return array('success' => false, 'error' => 'Database error: ' . $e->getMessage());
    }
}

function delete_main_gallery_image($data) {
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    $image_id = $data['image_id'] ?? null;
    
    if (!$image_id) {
        return array('success' => false, 'error' => 'Missing image_id');
    }
    
    // Get image URL before deletion for file cleanup
    $get_stmt = $conn->prepare("SELECT image_url FROM studio_shop_images WHERE id = ?");
    $get_stmt->bind_param("i", $image_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    
    if ($result->num_rows === 0) {
        $get_stmt->close();
        return array('success' => false, 'error' => 'Image not found');
    }
    
    $image_row = $result->fetch_assoc();
    $image_url = $image_row['image_url'];
    $get_stmt->close();
    
    // Delete from database
    $del_stmt = $conn->prepare("DELETE FROM studio_shop_images WHERE id = ?");
    $del_stmt->bind_param("i", $image_id);
    
    if ($del_stmt->execute()) {
        $del_stmt->close();
        
        // Delete physical file if it exists
        if (!empty($image_url) && strpos($image_url, 'data:image') !== 0) {
            // This is a file URL, not Base64 data
            $filename = basename($image_url);
            $wp_upload_dir = wp_upload_dir();
            $upload_dir = $wp_upload_dir['basedir'] . '/studio-shops/';
            $filepath = $upload_dir . $filename;
            
            if (file_exists($filepath)) {
                if (unlink($filepath)) {
                    wp_debug_log_info("Physical image file deleted", ['filepath' => $filepath]);
                } else {
                    wp_debug_log_error("Failed to delete physical image file", ['filepath' => $filepath]);
                }
            } else {
                wp_debug_log_info("Physical image file not found (may be Base64 data)", ['filepath' => $filepath]);
            }
        }
        
        // キャッシュをクリア
        if (function_exists('clear_studio_data_cache')) {
            clear_studio_data_cache();
        }
        
        return array('success' => true, 'message' => 'Main gallery image deleted successfully');
    } else {
        $error = $del_stmt->error;
        $del_stmt->close();
        return array('success' => false, 'error' => $error);
    }
}

function delete_shop($data) {
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    $shop_id = $data['shop_id'] ?? null;
    
    
    if (!$shop_id) {
        return array('success' => false, 'error' => 'Missing shop_id');
    }
    
    $conn->begin_transaction();
    
    try {
        // Get shop name for confirmation
        $name_stmt = $conn->prepare("SELECT name FROM studio_shops WHERE id = ?");
        $name_stmt->bind_param("i", $shop_id);
        $name_stmt->execute();
        $result = $name_stmt->get_result();
        
        if ($result->num_rows === 0) {
            $name_stmt->close();
            throw new Exception("Shop not found with ID: " . $shop_id);
        }
        
        $shop_row = $result->fetch_assoc();
        $shop_name = $shop_row['name'];
        $name_stmt->close();
        
        // Skip category images deletion - table doesn't exist in simplified system
        $deleted_cat_images_count = 0;
        
        // Get all main gallery images for physical file deletion
        $get_main_images_stmt = $conn->prepare("SELECT image_url FROM studio_shop_images WHERE shop_id = ?");
        $get_main_images_stmt->bind_param("i", $shop_id);
        $get_main_images_stmt->execute();
        $main_images_result = $get_main_images_stmt->get_result();
        $main_image_urls = $main_images_result->fetch_all(MYSQLI_ASSOC);
        $get_main_images_stmt->close();
        
        // Delete physical main gallery image files
        $wp_upload_dir = wp_upload_dir();
        $upload_dir = $wp_upload_dir['basedir'] . '/studio-shops/';
        $deleted_main_files = 0;
        
        foreach ($main_image_urls as $row) {
            $image_url = $row['image_url'];
            if (!empty($image_url) && strpos($image_url, 'data:image') !== 0) {
                $filename = basename($image_url);
                $filepath = $upload_dir . $filename;
                if (file_exists($filepath) && unlink($filepath)) {
                    $deleted_main_files++;
                }
            }
        }
        
        // Delete all main gallery images from database
        $del_main_images_stmt = $conn->prepare("DELETE FROM studio_shop_images WHERE shop_id = ?");
        $del_main_images_stmt->bind_param("i", $shop_id);
        $del_main_images_stmt->execute();
        $deleted_main_images_count = $del_main_images_stmt->affected_rows;
        $del_main_images_stmt->close();
        
        // Delete the shop itself
        $del_shop_stmt = $conn->prepare("DELETE FROM studio_shops WHERE id = ?");
        $del_shop_stmt->bind_param("i", $shop_id);
        
        if (!$del_shop_stmt->execute()) {
            throw new Exception("Failed to delete shop: " . $del_shop_stmt->error);
        }
        
        $del_shop_stmt->close();
        
        // Skip category cleanup - simplified system doesn't use categories
        $cleaned_categories = 0;
        
        $conn->commit();
        
        // キャッシュをクリア
        if (function_exists('clear_studio_data_cache')) {
            clear_studio_data_cache();
        }
        
        // WordPressアクションを発火
        do_action('studio_shop_deleted', $shop_id, $shop_name);
        
        wp_debug_log_info("Shop deleted successfully", [
            'shop_name' => $shop_name,
            'deleted_main_images_db' => $deleted_main_images_count,
            'deleted_main_files' => $deleted_main_files
        ]);
        
        return array(
            'success' => true, 
            'message' => 'Shop deleted successfully',
            'shop_name' => $shop_name,
            'deleted_category_images' => $deleted_cat_images_count,
            'deleted_main_images' => $deleted_main_images_count,
            'deleted_main_files' => $deleted_main_files,
            'cleaned_categories' => $cleaned_categories
        );
        
    } catch (Exception $e) {
        $conn->rollback();
        return array('success' => false, 'error' => $e->getMessage());
    }
}

function get_all_studio_shops($data) {
    try {
        $conn = get_db_connection();
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
    
    try {
        // Get all shops with their basic information
        $stmt = $conn->prepare("SELECT id, name, address, phone, nearest_station, business_hours, holidays, map_url, company_email, main_image, created_at FROM studio_shops ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $shops_data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        $shops = array();
        foreach ($shops_data as $row) {
            // Get gallery images for each shop
            $img_stmt = $conn->prepare("SELECT id, image_url FROM studio_shop_images WHERE shop_id = ? ORDER BY created_at ASC");
            $img_stmt->bind_param("i", $row['id']);
            $img_stmt->execute();
            $img_result = $img_stmt->get_result();
            $images = $img_result->fetch_all(MYSQLI_ASSOC);
            wp_debug_log_info("Shop gallery images retrieved", ['shop_id' => $row['id'], 'image_count' => count($images)]);
            $img_stmt->close();
            
            $image_urls = array();
            $main_gallery_images = array();
            foreach ($images as $img_row) {
                $image_urls[] = $img_row['image_url'];
                $main_gallery_images[] = array(
                    'id' => $img_row['id'],
                    'url' => $img_row['image_url']
                );
            }
            
            $row['image_urls'] = $image_urls;
            $row['main_gallery_images'] = $main_gallery_images;
            $row['category_images'] = array(); // Legacy compatibility
            
            $shops[] = $row;
        }
        
        return array(
            'success' => true,
            'message' => 'Shops retrieved successfully',
            'shops' => $shops
        );
        
    } catch (Exception $e) {
        return array('success' => false, 'error' => 'Database query failed: ' . $e->getMessage());
    }
}