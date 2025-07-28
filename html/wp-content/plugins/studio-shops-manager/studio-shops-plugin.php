<?php
/*
Plugin Name: Studio Shops Manager
Description: Manage Studio Shops and their gallery images via API in WordPress admin dashboard.
Version: 1.7.4
Author: Your Name
*/

defined('ABSPATH') or die('No direct access allowed.');

// Include API helper
require_once plugin_dir_path(__FILE__) . 'includes/api-helper.php';

// AJAX handler for internal API calls
add_action('wp_ajax_studio_shop_internal_api', 'handle_studio_shop_internal_api');
add_action('wp_ajax_nopriv_studio_shop_internal_api', 'handle_studio_shop_internal_api');

function handle_studio_shop_internal_api() {
    // Check if data comes from FormData (POST) or JSON
    if (isset($_POST['endpoint'])) {
        // FormData submission - build data array from POST parameters
        $endpoint = $_POST['endpoint'];
        $data = array();
        
        // Extract all POST parameters except 'action' and 'endpoint'
        foreach ($_POST as $key => $value) {
            if ($key !== 'action' && $key !== 'endpoint') {
                $data[$key] = $value;
            }
        }
        
        // Handle special case where data is JSON encoded
        if (isset($_POST['data'])) {
            $json_data = json_decode(stripslashes($_POST['data']), true);
            if ($json_data) {
                $data = array_merge($data, $json_data);
            }
        }
        
        
    } else {
        // JSON submission (fallback)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['endpoint']) || !isset($input['data'])) {
            wp_die(json_encode(['success' => false, 'error' => 'Invalid request data']));
        }
        
        $endpoint = $input['endpoint'];
        $data = $input['data'];
        
    }
    
    $result = make_internal_api_call($endpoint, $data);
    
    wp_die(json_encode($result));
}

// AJAX handler for deleting category images
add_action('wp_ajax_delete_category_image', 'handle_delete_category_image');
add_action('wp_ajax_nopriv_delete_category_image', 'handle_delete_category_image');

function handle_delete_category_image() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'studio_shop_nonce')) {
        wp_die(json_encode(['success' => false, 'error' => 'Security check failed']));
    }
    
    $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
    
    if (!$image_id) {
        wp_die(json_encode(['success' => false, 'error' => 'Missing image ID']));
    }
    
    $result = delete_category_image(['image_id' => $image_id]);
    
    wp_die(json_encode($result));
}

// AJAX handler for deleting entire categories
add_action('wp_ajax_delete_category', 'handle_delete_category');
add_action('wp_ajax_nopriv_delete_category', 'handle_delete_category');

function handle_delete_category() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'studio_shop_nonce')) {
        wp_die(json_encode(['success' => false, 'error' => 'Security check failed']));
    }
    
    $shop_id = isset($_POST['shop_id']) ? intval($_POST['shop_id']) : 0;
    $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
    
    if (!$shop_id || !$category_name) {
        wp_die(json_encode(['success' => false, 'error' => 'Missing shop ID or category name']));
    }
    
    $result = delete_entire_category(['shop_id' => $shop_id, 'category_name' => $category_name]);
    
    wp_die(json_encode($result));
}

// Add admin menu for Studio Shops
add_action('admin_menu', 'studio_shops_menu');
function studio_shops_menu() {
    add_menu_page(
        'Studio Shops',
        'Studio Shops',
        'manage_options',
        'studio-shops',
        'studio_shops_admin_page',
        'dashicons-store',
        20
    );
}

// Get API base URL based on environment
function get_api_base_url() {
    // Check if we're in Docker environment (server-side call)
    if (defined('WP_HOME')) {
        $wp_home = WP_HOME;
        if (strpos($wp_home, 'localhost:8080') !== false) {
            // Use site URL for server-side API calls in Docker
            return home_url('/api/');
        }
    }
    
    if ($_SERVER['HTTP_HOST'] === 'localhost:8080' || $_SERVER['HTTP_HOST'] === 'localhost') {
        // Use home_url to ensure proper URL construction
        return home_url('/api/');
    } else {
        return 'https://678photo.com/api/';
    }
}

// Admin page rendering and form handling
function studio_shops_admin_page() {
    $api_base_url = get_api_base_url();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Studio Shops Manager', 'studio-shops'); ?></h1>
        
        <div>
            <label><input type="checkbox" id="update-mode" name="update_mode"> Update Existing Shop</label>
            <div id="shop-selector" style="display:none; margin-top:10px;">
                <select name="shop_id" id="shop-id-select">
                    <option value="">Select a Shop</option>
                </select>
                <button type="button" id="delete-shop-btn" style="margin-left: 15px; padding: 8px 16px; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer; display: none;"
                        onmouseover="this.style.background='#555'" 
                        onmouseout="this.style.background='#666'">
                    🗑️ このショップを削除
                </button>
            </div>
        </div>

        <?php
        // Check if form is submitted
        $is_update_mode = isset($_POST['update_mode']) && $_POST['update_mode'] === 'on';
        
        if (isset($_POST['submit_shop']) && check_admin_referer('studio_shops_save', 'studio_shops_nonce')) {
            $name = sanitize_text_field($_POST['name']);
            $address = sanitize_textarea_field($_POST['address']);
            $phone = sanitize_text_field($_POST['phone']);
            $nearest_station = sanitize_text_field($_POST['nearest_station']);
            $business_hours = sanitize_text_field($_POST['business_hours']);
            $holidays = sanitize_text_field($_POST['holidays']);
            $map_url = wp_kses($_POST['map_url'], [
                'iframe' => [
                    'src' => [],
                    'width' => [],
                    'height' => [],
                    'style' => [],
                    'frameborder' => [],
                    'allowfullscreen' => [],
                    'loading' => []
                ]
            ]);
            $company_email = sanitize_email($_POST['company_email']);
            $category_names = $_POST['category_name'] ?? [];
            $gallery_files = $_FILES['gallery_images'] ?? [];
            $shop_id = isset($_POST['shop_id']) ? sanitize_text_field($_POST['shop_id']) : '';


            // Validate shop_id for update mode
            if ($is_update_mode && empty($shop_id)) {
                echo '<div class="error"><p>' . esc_html__('Error: Shop ID is missing during update.', 'studio-shops') . '</p></div>';
            } else {

                // Handle main gallery images FIRST
                $main_gallery_images = [];
                
                if (!empty($_FILES['gallery_images_flat'])) {
                    $gallery_flat_files = $_FILES['gallery_images_flat'];
                    
                    // Check if it's a single file or multiple files
                    if (is_array($gallery_flat_files['name'])) {
                        // Multiple files
                        for ($i = 0; $i < count($gallery_flat_files['name']); $i++) {
                            if ($gallery_flat_files['error'][$i] === UPLOAD_ERR_OK) {
                                $tmp_name = $gallery_flat_files['tmp_name'][$i];
                                if (file_exists($tmp_name)) {
                                    $image_data = file_get_contents($tmp_name);
                                    $image_type = $gallery_flat_files['type'][$i];
                                    $base64_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                                    $main_gallery_images[] = $base64_image;
                                }
                            }
                        }
                    } else {
                        // Single file
                        if ($gallery_flat_files['error'] === UPLOAD_ERR_OK) {
                            $tmp_name = $gallery_flat_files['tmp_name'];
                            if (file_exists($tmp_name)) {
                                $image_data = file_get_contents($tmp_name);
                                $image_type = $gallery_flat_files['type'];
                                $base64_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                                $main_gallery_images[] = $base64_image;
                            }
                        }
                    }
                }

                // Now create the API data with the processed main gallery images
                $api_data = [
                    'name' => $name,
                    'address' => $address,
                    'phone' => $phone,
                    'nearest_station' => $nearest_station,
                    'business_hours' => $business_hours,
                    'holidays' => $holidays,
                    'map_url' => $map_url,
                    'company_email' => $company_email,
                    'gallery_images' => $main_gallery_images  // メインギャラリー画像をここに含める
                ];

                // Include shop_id in the API payload for update mode
                if ($is_update_mode && $shop_id) {
                    $api_data['shop_id'] = $shop_id;
                }


                echo '<div id="loader" style="padding:10px; font-weight:bold; color:blue;">Processing shop data, please wait...</div>';

                // Send API request for shop creation or update
                $api_endpoint = $is_update_mode ? 'update_shop_details.php' : 'studio_shop.php';
                
                // Make internal API call
                $response_body = make_internal_api_call($api_endpoint, $api_data);
                $response = array('body' => json_encode($response_body));
                
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo '<div class="error"><p>' . esc_html__('API request failed: ' . $error_message, 'studio-shops') . '</p></div>';
                } else {
                    $response_body = json_decode(wp_remote_retrieve_body($response), true);
                    if (isset($response_body['success']) && $response_body['success']) {
                        $shop_id = $is_update_mode ? $shop_id : ($response_body['shop_id'] ?? '');
                        
                        if ($shop_id) {
                            // Prepare category gallery payload
                            $category_gallery = [];
                            $has_category_images = false;
                            
                            // Remove duplicate category names but preserve their associated files
                            $unique_categories = [];
                            foreach ($category_names as $cat_index => $cat_name) {
                                $cat_name = sanitize_text_field($cat_name);
                                if (empty($cat_name)) {
                                    continue;
                                }
                                
                                // Store all indices for each unique category name
                                if (!isset($unique_categories[$cat_name])) {
                                    $unique_categories[$cat_name] = [];
                                }
                                $unique_categories[$cat_name][] = $cat_index;
                            }
                            
                            foreach ($unique_categories as $cat_name => $indices) {
                                
                                // Initialize category array
                                $category_gallery[$cat_name] = [];

                                // Process all files for all indices of this category
                                foreach ($indices as $cat_index) {
                                    
                                    if (isset($gallery_files['name'][$cat_index]) && is_array($gallery_files['name'][$cat_index])) {
                                        foreach ($gallery_files['name'][$cat_index] as $img_index => $img_name) {
                                            if (!empty($img_name) && $gallery_files['error'][$cat_index][$img_index] === UPLOAD_ERR_OK) {
                                                $tmp_name = $gallery_files['tmp_name'][$cat_index][$img_index];
                                                if (file_exists($tmp_name)) {
                                                    $image_data = file_get_contents($tmp_name);
                                                    $image_type = $gallery_files['type'][$cat_index][$img_index];
                                                    $base64_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                                                    $category_gallery[$cat_name][] = $base64_image;
                                                    $has_category_images = true;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $final_payload = [
                                'shop_id' => $shop_id,
                                'gallery' => []
                            ];

                            foreach ($category_gallery as $cat => $images) {
                                if (!empty($images)) {
                                    $final_payload['gallery'][] = [
                                        'category_name' => $cat,
                                        'images' => $images
                                    ];
                                }
                            }

                            // Main gallery images are now included in the main API call above
                            $main_success = true;

                            // Send category images to the appropriate API
                            $category_api_endpoint = $is_update_mode ? 
                                'update_shop_category_images.php' : 
                                'category_image_uploader.php';

                            if (!$has_category_images || empty($final_payload['gallery'])) {
                                // No category images to process, skip API call
                                $category_success = true;
                            } else {
                                
                                // Make internal API call
                                $image_response_body = make_internal_api_call($category_api_endpoint, $final_payload);
                                $image_response = array('body' => json_encode($image_response_body));

                                if (is_wp_error($image_response)) {
                                    echo '<div class="error"><p>' . esc_html__('Failed to upload category images: ' . $image_response->get_error_message(), 'studio-shops') . '</p></div>';
                                    $category_success = false;
                                } else {
                                    $image_response_body = json_decode(wp_remote_retrieve_body($image_response), true);
                                    if (!isset($image_response_body['success']) || !$image_response_body['success']) {
                                        $error_msg = $image_response_body['error'] ?? 'Unknown error';
                                        
                                        // Check if it's a duplicate entry error for update mode
                                        if ($is_update_mode && strpos($error_msg, 'Duplicate entry') !== false) {
                                            
                                            // Try to delete existing category images before inserting new ones
                                            $delete_payload = ['shop_id' => $shop_id];
                                            $delete_response = wp_remote_post($api_base_url . 'delete_shop_category_images.php', [
                                                'method' => 'POST',
                                                'headers' => ['Content-Type' => 'application/json'],
                                                'body' => json_encode($delete_payload),
                                                'timeout' => 30
                                            ]);
                                            
                                            if (!is_wp_error($delete_response)) {
                                                $delete_response_body = json_decode(wp_remote_retrieve_body($delete_response), true);
                                                // Retry the category image upload
                                                $retry_response = wp_remote_post($category_api_url, [
                                                    'method' => 'POST',
                                                    'headers' => ['Content-Type' => 'application/json'],
                                                    'body' => json_encode($final_payload),
                                                    'timeout' => 60
                                                ]);
                                                
                                                if (!is_wp_error($retry_response)) {
                                                    $retry_body = json_decode(wp_remote_retrieve_body($retry_response), true);
                                                    if (isset($retry_body['success']) && $retry_body['success']) {
                                                        $category_success = true;
                                                    } else {
                                                        echo '<div class="error"><p>' . esc_html__('Failed to upload category images after retry: ' . ($retry_body['error'] ?? 'Unknown error'), 'studio-shops') . '</p></div>';
                                                        $category_success = false;
                                                    }
                                                } else {
                                                    echo '<div class="error"><p>' . esc_html__('Failed to retry category image upload: ' . $retry_response->get_error_message(), 'studio-shops') . '</p></div>';
                                                    $category_success = false;
                                                }
                                            } else {
                                                echo '<div class="error"><p>' . esc_html__('Failed to delete existing category images: ' . $delete_response->get_error_message(), 'studio-shops') . '</p></div>';
                                                $category_success = false;
                                            }
                                        } else {
                                            echo '<div class="error"><p>' . esc_html__('Failed to upload category images: ' . $error_msg, 'studio-shops') . '</p></div>';
                                            $category_success = false;
                                        }
                                    }
                                }
                            }
                            
                            // Clear store detail page cache on successful update
                            if ($is_update_mode && ($main_success || $category_success)) {
                                delete_transient('studio_shop_' . $shop_id);
                            }
                            
                            // Show final success message
                            if ($main_success && $category_success) {
                                echo '<div class="updated"><p>' . esc_html__($is_update_mode ? 'Shop updated successfully!' : 'Shop created successfully!', 'studio-shops') . '</p></div>';
                                
                                // Add JavaScript to refresh shop list after successful creation/update
                                if (!$is_update_mode) {
                                    echo '<script>
                                        // Refresh shop list after new shop creation
                                        if (typeof fetchShops === "function") {
                                            setTimeout(() => {
                                                
                                                // Show loading indicator
                                                const shopSelect = document.getElementById("shop-id-select");
                                                if (shopSelect) {
                                                    shopSelect.innerHTML = "<option value=\"\">🔄 Updating shop list...</option>";
                                                }
                                                
                                                // Refresh the shop list
                                                fetchShops().then(() => {
                                                }).catch(error => {
                                                    if (shopSelect) {
                                                        shopSelect.innerHTML = "<option value=\"\">Select a Shop</option>";
                                                    }
                                                });
                                            }, 1000);
                                        }
                                    </script>';
                                }
                            }
                        } else {
                            echo '<div class="error"><p>' . esc_html__('Operation failed: Shop ID not returned from API.', 'studio-shops') . '</p></div>';
                        }
                    } else {
                        echo '<div class="error"><p>' . esc_html__('Operation failed: ' . ($response_body['error'] ?? 'Unknown error'), 'studio-shops') . '</p></div>';
                    }
                }
            }
        }
        ?>

        <h2><?php esc_html_e($is_update_mode ? 'Update Shop' : 'Add New Shop', 'studio-shops'); ?></h2>
        <form method="post" enctype="multipart/form-data" id="shop-form">
            <?php wp_nonce_field('studio_shops_save', 'studio_shops_nonce'); ?>
            <input type="hidden" name="update_mode" id="update_mode" value="<?php echo $is_update_mode ? 'on' : 'off'; ?>">
            <input type="hidden" name="shop_id" id="shop_id" value="">
            <!-- Shop Basic Information Section -->
            <div style="margin: 30px 0; padding: 25px; border: 2px solid #666; border-radius: 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 20px; font-weight: 600; display: flex; align-items: center;">
                    🏪 ショップ基本情報
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <!-- Left Column -->
                    <div>
                        <div style="margin-bottom: 15px;">
                            <label for="name" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                🏢 ショップ名 <span style="color: #dc3232;">*</span>
                            </label>
                            <input type="text" name="name" id="name" required 
                                   style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease;"
                                   onfocus="this.style.borderColor='#666'" 
                                   onblur="this.style.borderColor='#ddd'">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label for="phone" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                📞 電話番号
                            </label>
                            <input type="text" name="phone" id="phone" 
                                   style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease;"
                                   onfocus="this.style.borderColor='#666'" 
                                   onblur="this.style.borderColor='#ddd'">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label for="nearest_station" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                🚃 最寄り駅
                            </label>
                            <input type="text" name="nearest_station" id="nearest_station" 
                                   style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease;"
                                   onfocus="this.style.borderColor='#666'" 
                                   onblur="this.style.borderColor='#ddd'">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label for="company_email" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                📧 会社メールアドレス
                            </label>
                            <input type="email" name="company_email" id="company_email" 
                                   style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease;"
                                   onfocus="this.style.borderColor='#666'" 
                                   onblur="this.style.borderColor='#ddd'">
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div>
                        <div style="margin-bottom: 15px;">
                            <label for="address" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                📍 住所 <span style="color: #dc3232;">*</span>
                            </label>
                            <textarea name="address" id="address" required rows="3"
                                      style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; resize: vertical; transition: border-color 0.3s ease;"
                                      onfocus="this.style.borderColor='#28a745'" 
                                      onblur="this.style.borderColor='#ddd'"></textarea>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label for="business_hours" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                🕐 営業時間
                            </label>
                            <input type="text" name="business_hours" id="business_hours" 
                                   placeholder="例: 9:00-18:00"
                                   style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease;"
                                   onfocus="this.style.borderColor='#666'" 
                                   onblur="this.style.borderColor='#ddd'">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label for="holidays" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                🗓️ 定休日
                            </label>
                            <input type="text" name="holidays" id="holidays" 
                                   placeholder="例: 日曜日、祝日"
                                   style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease;"
                                   onfocus="this.style.borderColor='#666'" 
                                   onblur="this.style.borderColor='#ddd'">
                        </div>
                    </div>
                </div>
                
                <!-- Map Embed Code (Full Width) -->
                <div style="margin-bottom: 15px;">
                    <label for="map_url" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                        🗺️ 地図埋め込みコード
                    </label>
                    <textarea name="map_url" id="map_url" rows="4" 
                              placeholder="Paste your map embed code (e.g., Google Maps iframe)"
                              style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; resize: vertical; transition: border-color 0.3s ease;"
                              onfocus="this.style.borderColor='#28a745'" 
                              onblur="this.style.borderColor='#ddd'"></textarea>
                    <small style="display: block; margin-top: 5px; color: #666;">
                        Google Mapsのiframeコードなどを貼り付けてください
                    </small>
                </div>
            </div>

            <!-- Main Gallery Section -->
            <div id="main-gallery-section" style="margin: 30px 0; padding: 25px; border: 2px solid #666; border-radius: 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 20px; font-weight: 600; display: flex; align-items: center;">
                    🖼️ メイン画像（1枚のみ）
                </h3>
                
                <!-- 現在のメイン画像表示エリア -->
                <div id="current-main-image-section" style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">📷 現在のメイン画像</h4>
                    <div id="current-main-image-container">
                        <p id="no-main-image-message" style="color: #666; font-style: italic; padding: 15px; background: #fafafa; border: 1px dashed #ddd; border-radius: 6px; text-align: center;">
                            メイン画像が設定されていません
                        </p>
                    </div>
                </div>
                
                <!-- ファイル選択エリア -->
                <div>
                    <label for="main-gallery-input" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        🔄 メイン画像を変更/設定：
                    </label>
                    <input type="file" name="gallery_images_flat[]" accept="image/*" id="main-gallery-input" 
                           style="padding: 8px; border: 2px dashed #666; border-radius: 6px; background: white; width: 100%; max-width: 400px;">
                    <small style="display: block; margin-top: 5px; color: #666;">
                        新しい画像を選択すると、現在の画像と置き換えられます
                    </small>
                    
                    <!-- 新規画像プレビュー -->
                    <div id="main-gallery-preview" style="margin-top: 15px; display: none;">
                        <!-- プレビューがここに表示される -->
                    </div>
                </div>
            </div>

            <!-- Gallery by Category Section -->
            <div style="margin: 30px 0; padding: 25px; border: 2px solid #666; border-radius: 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 20px; font-weight: 600; display: flex; align-items: center;">
                    📁 カテゴリー別ギャラリー
                </h3>
                
                <!-- 既存カテゴリー表示エリア -->
                <div id="existing-categories-section" style="margin-bottom: 25px;">
                    <h4 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">📂 既存のカテゴリー</h4>
                    <div id="existing-categories-container">
                        <!-- 既存カテゴリーがここに表示される -->
                        <p id="no-categories-message" style="color: #666; font-style: italic; padding: 15px; background: #fafafa; border: 1px dashed #ddd; border-radius: 6px; text-align: center;">
                            カテゴリーが登録されていません
                        </p>
                    </div>
                </div>
                
                <!-- 新規カテゴリー追加エリア -->
                <div id="new-category-section" style="border: 2px dashed #666; padding: 20px; border-radius: 8px; background: white;">
                    <h4 style="margin: 0 0 20px 0; color: #333; font-size: 16px;">➕ 新しいカテゴリーを追加</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label for="new-category-name" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                🏷️ カテゴリー名
                            </label>
                            <input type="text" id="new-category-name" placeholder="例: ポートレート、風景写真" 
                                   style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease;"
                                   onfocus="this.style.borderColor='#666'" 
                                   onblur="this.style.borderColor='#ddd'">
                        </div>
                        
                        <div>
                            <label for="new-category-images" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">
                                🖼️ 画像ファイル
                            </label>
                            <input type="file" id="new-category-images" multiple accept="image/*"
                                   style="width: 100%; padding: 8px; border: 2px dashed #666; border-radius: 6px; background: white;">
                            <small style="display: block; margin-top: 5px; color: #666;">複数の画像を同時に選択できます</small>
                        </div>
                    </div>
                    
                    <div id="new-category-preview" style="margin: 15px 0; min-height: 120px; border: 1px dashed #ddd; padding: 15px; border-radius: 6px; background: #fafafa;">
                        <p style="color: #999; text-align: center; margin: 40px 0;">選択した画像のプレビューがここに表示されます</p>
                    </div>
                    
                    <div style="text-align: right;">
                        <button type="button" id="clear-new-category-btn" class="button" style="margin-right: 10px; padding: 10px 20px; border-radius: 6px;">
                            🔄 クリア
                        </button>
                        <button type="button" id="add-new-category-btn" class="button button-primary" disabled
                                style="padding: 10px 20px; border-radius: 6px; background: #666; border-color: #666;">
                            ➕ このカテゴリーを追加
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div style="margin: 30px 0; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; text-align: center; border: 2px solid #666;">
                <input type="submit" name="submit_shop" id="submit_shop" 
                       value="<?php echo $is_update_mode ? '🔄 ショップを更新' : '✨ ショップを登録'; ?>"
                       style="background: linear-gradient(135deg, #666 0%, #555 100%); color: white; border: none; padding: 15px 40px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 6px rgba(102, 102, 102, 0.3); transition: all 0.3s ease; text-transform: none;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(102, 102, 102, 0.4)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(102, 102, 102, 0.3)'"
                       onmousedown="this.style.transform='translateY(0)'"
                       onmouseup="this.style.transform='translateY(-2px)'">
            </div>
        </form>

        <style>
            /* 既存カテゴリー表示スタイル */
            .category-item {
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 15px;
                background: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .category-item h5 {
                margin: 0 0 10px 0;
                padding: 8px 12px;
                background: #f0f6ff;
                border-left: 4px solid #0073aa;
                border-radius: 4px;
                font-size: 16px;
                color: #0073aa;
            }
            
            .category-images {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 15px;
                min-height: 120px;
                padding: 10px;
                background: #fafafa;
                border: 1px dashed #ddd;
                border-radius: 4px;
            }
            
            .category-image-item {
                position: relative;
                display: inline-block;
            }
            
            .category-image-item img {
                width: 100px;
                height: 100px;
                object-fit: cover;
                border: 2px solid #ddd;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .category-image-item img:hover {
                border-color: #0073aa;
                transform: scale(1.05);
            }
            
            .delete-image-btn {
                position: absolute;
                top: -8px;
                right: -8px;
                background: #dc3232;
                color: white;
                border: none;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                font-size: 14px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                transition: all 0.2s ease;
            }
            
            .delete-image-btn:hover {
                background: #a00;
                transform: scale(1.1);
            }
            
            .category-actions {
                text-align: right;
                border-top: 1px solid #eee;
                padding-top: 10px;
            }
            
            .delete-category-btn {
                background: #dc3232;
                color: white;
                border: none;
                padding: 8px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                transition: background-color 0.2s ease;
            }
            
            .delete-category-btn:hover {
                background: #a00;
            }
            
            /* 新規カテゴリー追加スタイル */
            .image-preview {
                display: inline-block;
                position: relative;
                margin: 5px;
            }
            
            .image-preview img {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border: 2px solid #0073aa;
                border-radius: 4px;
            }
            
            .remove-preview-btn {
                position: absolute;
                top: -8px;
                right: -8px;
                background: #dc3232;
                color: white;
                border: none;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                font-size: 12px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            /* メッセージスタイル */
            .success-message {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
                padding: 10px 15px;
                border-radius: 4px;
                margin: 10px 0;
            }
            
            .error-message {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
                padding: 10px 15px;
                border-radius: 4px;
                margin: 10px 0;
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Plugin initialized
            
            // Global variable to store all categories
            window.allCategories = new Set();

            // DOM elements initialized

            const updateCheckbox = document.getElementById('update-mode');
            const shopSelector = document.getElementById('shop-selector');
            const shopSelect = document.getElementById('shop-id-select');
            const form = document.getElementById('shop-form');
            const mainGalleryPreview = document.getElementById('main-gallery-preview');
            const existingCategoriesContainer = document.getElementById('existing-categories-container');
            const newCategoryNameInput = document.getElementById('new-category-name');
            const newCategoryImagesInput = document.getElementById('new-category-images');
            const newCategoryPreview = document.getElementById('new-category-preview');
            const addNewCategoryBtn = document.getElementById('add-new-category-btn');
            const clearNewCategoryBtn = document.getElementById('clear-new-category-btn');

            // Initialize shopsData to store shop details
            window.shopsData = [];

            // Function to reset the form completely
            function resetForm(preserveFiles = false) {
                if (!preserveFiles) {
                    form.reset();
                }
                document.getElementById('shop_id').value = '';
                document.getElementById('name').value = '';
                document.getElementById('address').value = '';
                document.getElementById('phone').value = '';
                document.getElementById('nearest_station').value = '';
                document.getElementById('business_hours').value = '';
                document.getElementById('holidays').value = '';
                document.getElementById('map_url').value = '';
                document.getElementById('company_email').value = '';
                
                // Reset delete button state
                const deleteBtn = document.getElementById('delete-shop-btn');
                if (deleteBtn) {
                    deleteBtn.innerHTML = '🗑️ このショップを削除';
                    deleteBtn.disabled = false;
                    deleteBtn.style.display = 'none';
                }
                
                if (!preserveFiles) {
                    document.getElementById('main-gallery-input').value = '';
                    mainGalleryPreview.innerHTML = '<p>No images selected.</p>';
                    
                    // Reset new category section
                    if (newCategoryNameInput) newCategoryNameInput.value = '';
                    if (newCategoryImagesInput) newCategoryImagesInput.value = '';
                    if (newCategoryPreview) {
                        newCategoryPreview.innerHTML = '<p style="color: #999; text-align: center; margin: 40px 0;">選択した画像のプレビューがここに表示されます</p>';
                    }
                    if (addNewCategoryBtn) addNewCategoryBtn.disabled = true;
                    
                    // Clear existing categories display
                    if (existingCategoriesContainer) {
                        existingCategoriesContainer.innerHTML = '<p id="no-categories-message" style="color: #666; font-style: italic;">カテゴリーが登録されていません</p>';
                    }
                }
            }

            // Validate shop_id and process categories before form submission
            form.addEventListener('submit', (e) => {
                if (updateCheckbox.checked && !document.getElementById('shop_id').value) {
                    e.preventDefault();
                    alert('Please select a shop to update.');
                    return;
                }
                
                // New UI doesn't use category blocks anymore
            });

            // Get API base URL based on environment
            function getApiBaseUrl() {
                const host = window.location.hostname;
                const port = window.location.port;
                if (host === 'localhost' && (port === '8080' || port === '')) {
                    return 'http://localhost:8080/api/';
                } else {
                    return 'https://678photo.com/api/';
                }
            }
            
            const apiBaseUrl = getApiBaseUrl();
            // API Base URL set
            
            // Fetch shop list
            async function fetchShops() {
                try {
                    const response = await fetch(apiBaseUrl + 'get_all_studio_shop.php?t=' + new Date().getTime(), {
                        cache: 'no-store'
                    });
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    const data = await response.json();
                    if (data.success && data.shops) {
                        window.shopsData = data.shops;
                        populateDropdown(data.shops);
                        return true; // Return success
                    } else {
                        alert('Failed to load shop list');
                        return false;
                    }
                } catch (error) {
                    alert('Failed to load shop list');
                    return false;
                }
            }

            // Populate shop dropdown and collect categories
            function populateDropdown(shops) {
                if (!shopSelect) {
                    return;
                }
                shopSelect.innerHTML = '<option value="">Select a Shop</option>';
                
                // Collect all categories from all shops
                window.allCategories.clear();
                
                shops.forEach(shop => {
                    const option = document.createElement('option');
                    option.value = shop.id;
                    option.textContent = shop.name;
                    shopSelect.appendChild(option);
                    
                    
                    // Collect categories from this shop
                    if (shop.category_images && typeof shop.category_images === 'object' && !Array.isArray(shop.category_images)) {
                        Object.keys(shop.category_images).forEach(category => {
                            if (category && category.trim()) {
                                window.allCategories.add(category.trim());
                            }
                        });
                    }
                });
                
                // Wait a bit for DOM to be ready before updating selectors
                setTimeout(() => {
                    updateCategorySelectors();
                }, 200);
            }
            
            // Update category selector UI
            function updateCategorySelectors() {
                
                // Update existing categories display
                const existingCategoriesList = document.getElementById('existing-categories-list');
                if (existingCategoriesList) {
                    existingCategoriesList.innerHTML = '';
                    
                    if (window.allCategories.size === 0) {
                        existingCategoriesList.innerHTML = '<p><em>No existing categories found</em></p>';
                    } else {
                        Array.from(window.allCategories).sort().forEach(category => {
                            const categoryTag = document.createElement('span');
                            categoryTag.style.cssText = 'background: #e7f3ff; padding: 5px 10px; border-radius: 15px; font-size: 12px; border: 1px solid #b3d9ff;';
                            categoryTag.textContent = category;
                            existingCategoriesList.appendChild(categoryTag);
                        });
                    }
                }
                
                // Update all category select dropdowns
                document.querySelectorAll('.category-select').forEach((select, index) => {
                    const currentValue = select.value;
                    select.innerHTML = '<option value="">Choose existing or type new...</option>';
                    
                    Array.from(window.allCategories).sort().forEach(category => {
                        const option = document.createElement('option');
                        option.value = category;
                        option.textContent = category;
                        if (currentValue === category) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                });
            }

            // Populate form with shop details
            function updateShopDetails(shopId) {
                resetForm(true); // Reset form but preserve files
                document.getElementById('shop_id').value = shopId;

                if (!shopId) {
                    return;
                }

                const shop = window.shopsData.find(s => s.id == shopId);
                if (!shop) {
                    alert('Shop data not found');
                    return;
                }
                document.getElementById('name').value = shop.name || '';
                document.getElementById('address').value = shop.address || '';
                document.getElementById('phone').value = shop.phone || '';
                document.getElementById('nearest_station').value = shop.nearest_station || '';
                document.getElementById('business_hours').value = shop.business_hours || '';
                document.getElementById('holidays').value = shop.holidays || '';
                document.getElementById('map_url').value = shop.map_url || '';
                document.getElementById('company_email').value = shop.company_email || '';

                // Populate current main image
                renderCurrentMainImage(shop.main_gallery_images || shop.image_urls || [], shop.id);

                // 既存カテゴリーの表示処理（シンプル版）
                renderExistingCategories(shop.category_images || {});
            }

            // Toggle update mode
            updateCheckbox.addEventListener('change', () => {
                shopSelector.style.display = updateCheckbox.checked ? 'block' : 'none';
                document.getElementById('update_mode').value = updateCheckbox.checked ? 'on' : 'off';
                document.getElementById('submit_shop').value = updateCheckbox.checked ? 'Update Shop' : 'Add Shop';
                shopSelect.value = ''; // Reset shop selection
                
                // Reset delete button state
                const deleteBtn = document.getElementById('delete-shop-btn');
                deleteBtn.style.display = 'none';
                deleteBtn.innerHTML = '🗑️ このショップを削除';
                deleteBtn.disabled = false;
                
                updateShopDetails(''); // Reset form
            });

            // Populate form on shop selection
            shopSelect.addEventListener('change', (event) => {
                updateShopDetails(event.target.value);
                
                // Show/hide delete button based on shop selection
                const deleteBtn = document.getElementById('delete-shop-btn');
                if (event.target.value && event.target.value !== '') {
                    // Reset delete button state and show it
                    deleteBtn.innerHTML = '🗑️ このショップを削除';
                    deleteBtn.disabled = false;
                    deleteBtn.style.display = 'inline-block';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });

            // Shop deletion functionality
            document.getElementById('delete-shop-btn').addEventListener('click', function() {
                const shopId = document.getElementById('shop-id-select').value;
                
                if (!shopId) {
                    alert('削除するショップを選択してください');
                    return;
                }
                
                // Get shop name for confirmation
                const shopSelect = document.getElementById('shop-id-select');
                const shopName = shopSelect.options[shopSelect.selectedIndex].text;
                
                // Double confirmation
                if (!confirm(`ショップ「${shopName}」を完全に削除しますか？\n\n⚠️ この操作は取り消せません。\n・ショップの基本情報\n・すべてのメイン画像\n・すべてのカテゴリー画像\n・関連するすべてのデータ\n\nが永久に削除されます。`)) {
                    return;
                }
                
                if (!confirm(`本当に削除しますか？\n最終確認：ショップ「${shopName}」を削除します。`)) {
                    return;
                }
                
                // Show loading state
                this.innerHTML = '⏳ 削除中...';
                this.disabled = true;
                
                // API call to delete shop
                const data = new FormData();
                data.append('action', 'studio_shop_internal_api');
                data.append('endpoint', 'delete_shop.php');
                data.append('shop_id', shopId);
                
                
                fetch(ajaxurl, {
                    method: 'POST',
                    body: data
                })
                .then(response => {
                    return response.text();
                })
                .then(text => {
                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON response: ' + text);
                    }
                    return result;
                })
                .then(result => {
                    if (result.success) {
                        showMessage(`ショップ「${result.shop_name}」を削除しました`, 'success');
                        
                        // Reset form and UI
                        document.getElementById('update-mode').checked = false;
                        document.getElementById('shop-selector').style.display = 'none';
                        document.getElementById('update_mode').value = 'off';
                        document.getElementById('submit_shop').value = 'Add Shop';
                        
                        // Reset delete button state
                        this.innerHTML = '🗑️ このショップを削除';
                        this.disabled = false;
                        this.style.display = 'none';
                        
                        // Reload shop list
                        fetchShops();
                        
                        // Reset form
                        updateShopDetails('');
                    } else {
                        this.innerHTML = '🗑️ このショップを削除';
                        this.disabled = false;
                        showMessage('削除に失敗しました: ' + result.error, 'error');
                    }
                })
                .catch(error => {
                    this.innerHTML = '🗑️ このショップを削除';
                    this.disabled = false;
                    showMessage('削除に失敗しました', 'error');
                });
            });

            // Old add-category-block functionality removed - using new UI structure
            
            // Old category block listeners removed - using new UI structure

            // Main gallery preview for new uploads
            const mainGalleryInput = document.getElementById('main-gallery-input');
            if (mainGalleryInput) {
                mainGalleryInput.addEventListener('change', function () {
                    const files = Array.from(this.files);
                    const previewContainer = document.getElementById('main-gallery-preview');
                    
                    if (files.length === 0) {
                        previewContainer.style.display = 'none';
                        previewContainer.innerHTML = '';
                        return;
                    }
                    
                    // 1つの画像のみ処理
                    const file = files[0]; // 最初の画像のみ使用
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewContainer.style.display = 'block';
                        previewContainer.innerHTML = `
                            <div style="padding: 15px; background: #fff8e1; border: 2px dashed #ffc107; border-radius: 6px; text-align: center;">
                                <p style="margin: 0 0 10px 0; color: #e65100; font-weight: 500;">📋 プレビュー: 新しいメイン画像</p>
                                <div style="position: relative; display: inline-block;">
                                    <img src="${e.target.result}" alt="新しいメイン画像のプレビュー" 
                                         style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #ffc107; border-radius: 8px;">
                                    <button type="button" class="remove-main-preview-btn" data-index="0"
                                            style="position: absolute; top: -8px; right: -8px; background: #dc3232; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;"
                                            onmouseover="this.style.background='#a00'; this.style.transform='scale(1.1)'"
                                            onmouseout="this.style.background='#dc3232'; this.style.transform='scale(1)'">
                                        ×
                                    </button>
                                </div>
                                <p style="margin: 10px 0 0 0; color: #666; font-size: 12px;">保存すると現在の画像と置き換えられます</p>
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                });
                
                // 新規メイン画像プレビューの削除機能（1つの画像のみ）
                document.getElementById('main-gallery-preview').addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-main-preview-btn')) {
                        // 画像を完全にクリア
                        mainGalleryInput.value = '';
                        
                        // プレビューを隠す
                        document.getElementById('main-gallery-preview').style.display = 'none';
                        document.getElementById('main-gallery-preview').innerHTML = '';
                    }
                });
            }

            // Category gallery preview for new uploads
            document.addEventListener('change', (e) => {
                if (e.target.classList.contains('category-image-input')) {
                    const input = e.target;
                    const block = input.closest('.category-gallery-block');
                    const previewContainer = block.querySelector('.category-preview');
                    handleFilePreview(input, previewContainer, 'category');
                }
            });

            // Delete category
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('delete-category')) {
                    const block = e.target.closest('.category-gallery-block');
                    const categoryId = block.dataset.categoryId;
                    
                    if (categoryId) {
                        if (confirm('Are you sure you want to delete this category?')) {
                            fetch(apiBaseUrl + 'delete_category.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ category_id: categoryId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    block.remove();
                                    alert('Category deleted successfully');
                                } else {
                                    alert('Failed to delete category: ' + (data.error || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                alert('Failed to delete category');
                            });
                        }
                    } else {
                        block.remove();
                    }
                }
            });

            // Delete main gallery image
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-main-image')) {
                    const shopId = e.target.getAttribute('data-shop-id');
                    const imageUrl = e.target.getAttribute('data-image-url');
                    const imageIndex = e.target.getAttribute('data-index');
                    
                    if (confirm('Are you sure you want to delete this main gallery image?')) {
                        // Call API to delete main gallery image
                        fetch(apiBaseUrl + 'delete_shop_main_image.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ 
                                shop_id: shopId, 
                                image_url: imageUrl,
                                image_index: imageIndex 
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                e.target.closest('.image-preview').remove();
                                alert('Main gallery image deleted successfully');
                                
                                // Update the shop data in memory
                                const shop = window.shopsData.find(s => s.id == shopId);
                                if (shop && shop.image_urls) {
                                    shop.image_urls.splice(imageIndex, 1);
                                }
                            } else {
                                alert('Failed to delete main gallery image: ' + (data.error || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            alert('Failed to delete main gallery image');
                        });
                    }
                }
            });

            // Delete category image
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-image')) {
                    const imageId = e.target.dataset.imageId;
                    const type = e.target.dataset.type;
                    const block = type === 'category' ? e.target.closest('.category-gallery-block') : null;
                    const previewContainer = type === 'main' ? mainGalleryPreview : block.querySelector('.category-preview');

                    if (imageId) {
                        if (confirm('Are you sure you want to delete this image?')) {
                            // Use internal API for local environment
                            const isLocal = window.location.hostname === 'localhost';
                            const apiUrl = isLocal ? 
                                '<?php echo admin_url('admin-ajax.php'); ?>?action=studio_shop_internal_api' : 
                                apiBaseUrl + 'delete_category_image.php';
                            
                            const requestBody = isLocal ? {
                                action: 'studio_shop_internal_api',
                                endpoint: 'delete_category_image.php',
                                data: { image_id: imageId }
                            } : { image_id: imageId };
                            
                            fetch(apiUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(requestBody)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    e.target.closest('.image-preview').remove();
                                    alert('Image deleted successfully');
                                } else {
                                    alert('Failed to delete image: ' + (data.error || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                alert('Failed to delete image');
                            });
                        }
                        return;
                    }

                    // Handle local removal for uploaded but not yet saved images
                    const input = type === 'main' ? document.getElementById('main-gallery-input') : block.querySelector('.category-image-input');
                    const indexToRemove = e.target.dataset.index;
                    const dt = new DataTransfer();
                    Array.from(input.files).forEach((file, i) => {
                        if (i !== parseInt(indexToRemove)) {
                            dt.items.add(file);
                        }
                    });

                    const newInput = input.cloneNode(true);
                    newInput.files = dt.files;
                    input.replaceWith(newInput);
                    handleFilePreview(newInput, previewContainer, type);
                }
            });

            // Preview handler for new uploads
            function handleFilePreview(input, previewContainer, type) {
                previewContainer.innerHTML = '';
                if (input.files.length === 0) {
                    previewContainer.innerHTML = '<p>No images selected.</p>';
                    return;
                }
                Array.from(input.files).forEach((file, i) => {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const div = document.createElement('div');
                        div.classList.add('image-preview');
                        div.innerHTML = `
                            <img src="${event.target.result}" alt="New Image">
                            <button type="button" class="remove-image" data-type="${type}" data-index="${i}">×</button>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }

            // 現在のメイン画像を表示する関数（1枚のみ対応）
            function renderCurrentMainImage(imageData, shopId) {
                const container = document.getElementById('current-main-image-container');
                const noMessage = document.getElementById('no-main-image-message');
                
                if (!container) {
                    return;
                }
                
                // 画像データをチェック（1枚のみ）
                const hasImage = imageData && Array.isArray(imageData) && imageData.length > 0;
                
                if (!hasImage) {
                    // 画像が無い場合
                    container.innerHTML = '<p id="no-main-image-message" style="color: #666; font-style: italic; padding: 15px; background: #fafafa; border: 1px dashed #ddd; border-radius: 6px; text-align: center;">メイン画像が設定されていません</p>';
                    return;
                }
                
                // 最初（メイン）の画像のみ使用
                const imageItem = imageData[0];
                const imageUrl = typeof imageItem === 'object' ? imageItem.url : imageItem;
                const imageId = typeof imageItem === 'object' ? imageItem.id : null;
                
                // 1枚の画像を表示
                container.innerHTML = `
                    <div style="display: flex; justify-content: center; padding: 15px; background: #fafafa; border: 1px dashed #ddd; border-radius: 6px;">
                        <div class="main-image-item" style="position: relative; display: inline-block;" data-image-id="${imageId || ''}">
                            <img src="${imageUrl}" alt="現在のメイン画像" 
                                 onclick="showMainImagePreview('${imageUrl}', 1)"
                                 style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #666; border-radius: 8px; cursor: pointer; transition: all 0.3s ease;"
                                 onmouseover="this.style.borderColor='#333'; this.style.transform='scale(1.05)'"
                                 onmouseout="this.style.borderColor='#666'; this.style.transform='scale(1)'">
                            <button type="button" class="delete-main-image-btn" 
                                    onclick="deleteMainImage('${imageUrl}', 0, ${shopId}, this)"
                                    title="この画像を削除"
                                    style="position: absolute; top: -8px; right: -8px; background: #dc3232; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: all 0.2s ease;"
                                    onmouseover="this.style.background='#a00'; this.style.transform='scale(1.1)'"
                                    onmouseout="this.style.background='#dc3232'; this.style.transform='scale(1)'">
                                ×
                            </button>
                        </div>
                    </div>
                `;
            }
            
            // メインギャラリー画像プレビュー表示（グローバル関数）
            window.showMainImagePreview = function(imageUrl, imageNumber) {
                const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
                previewWindow.document.write(`
                    <html>
                        <head><title>メインギャラリー - 画像 ${imageNumber}</title></head>
                        <body style="margin:0; background:#f0f0f0; display:flex; justify-content:center; align-items:center; min-height:100vh;">
                            <div style="text-align:center; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                                <h3 style="margin-top:0;">メインギャラリー - 画像 ${imageNumber}</h3>
                                <img src="${imageUrl}" style="max-width:100%; max-height:80vh; border:1px solid #ddd; border-radius:4px;">
                                <p><button onclick="window.close()" style="margin-top:15px; padding:8px 20px; background:#0073aa; color:white; border:none; border-radius:4px; cursor:pointer;">閉じる</button></p>
                            </div>
                        </body>
                    </html>
                `);
            }
            
            // メインギャラリー画像削除（グローバル関数）
            window.deleteMainImage = function(imageUrl, imageIndex, shopId, buttonElement) {
                if (!confirm(`メインギャラリーから画像を削除しますか？`)) {
                    return;
                }
                
                // Loading状態を表示
                buttonElement.innerHTML = '⏳';
                buttonElement.disabled = true;
                
                // Find image ID from data attribute
                const imageItem = buttonElement.closest('.main-image-item');
                const imageId = imageItem.dataset.imageId;
                
                if (!imageId) {
                    // Fallback to DOM-only deletion if no ID available
                    imageItem.remove();
                    updateMainGalleryEmptyState();
                    showMessage('画像を削除しました', 'success');
                    return;
                }
                
                // API call to delete main gallery image
                const data = new FormData();
                data.append('action', 'studio_shop_internal_api');
                data.append('endpoint', 'delete_main_gallery_image.php');
                data.append('image_id', imageId);
                
                fetch(ajaxurl, {
                    method: 'POST',
                    body: data
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // Remove from DOM on successful deletion
                        imageItem.remove();
                        updateMainGalleryEmptyState();
                        showMessage('画像を削除しました', 'success');
                    } else {
                        // Reset button on failure
                        buttonElement.innerHTML = '×';
                        buttonElement.disabled = false;
                        showMessage('削除に失敗しました: ' + result.error, 'error');
                    }
                })
                .catch(error => {
                    buttonElement.innerHTML = '×';
                    buttonElement.disabled = false;
                    showMessage('削除に失敗しました', 'error');
                });
            }
            
            function updateMainGalleryEmptyState() {
                const container = document.getElementById('current-main-image-container');
                const remainingImages = container.querySelectorAll('.main-image-item');
                if (remainingImages.length === 0) {
                    container.innerHTML = '<p id="no-main-image-message" style="color: #666; font-style: italic; padding: 15px; background: #fafafa; border: 1px dashed #ddd; border-radius: 6px; text-align: center;">メイン画像が設定されていません</p>';
                }
            }

            // 既存カテゴリーを表示する関数（シンプル版）
            function renderExistingCategories(categoryImages) {
                // renderExistingCategories called
                
                const container = document.getElementById('existing-categories-container');
                const noMessage = document.getElementById('no-categories-message');
                
                if (!container) {
                    return;
                }
                
                // カテゴリーデータをチェック
                const hasCategories = categoryImages && 
                    typeof categoryImages === 'object' && 
                    Object.keys(categoryImages).length > 0;
                
                if (!hasCategories) {
                    // カテゴリーが無い場合
                    container.innerHTML = '<p id="no-categories-message" style="color: #666; font-style: italic;">カテゴリーが登録されていません</p>';
                    return;
                }
                
                // メッセージを隠す
                if (noMessage) {
                    noMessage.style.display = 'none';
                }
                
                // カテゴリー表示を構築
                let html = '';
                // Building category display
                
                Object.keys(categoryImages).forEach(categoryName => {
                    const images = categoryImages[categoryName];
                    if (!images || !Array.isArray(images) || images.length === 0) {
                        return; // 画像が無いカテゴリーはスキップ
                    }
                    
                    html += `
                        <div class="category-item" data-category="${categoryName}">
                            <h5>📁 ${categoryName}</h5>
                            <div class="category-images">
                    `;
                    
                    // 画像を表示
                    images.forEach((imageData, index) => {
                        const imageUrl = typeof imageData === 'string' ? imageData : imageData.url;
                        const imageId = typeof imageData === 'object' ? imageData.id : null;
                        
                        if (imageUrl) {
                            html += `
                                <div class="category-image-item" data-image-id="${imageId}" data-image-url="${imageUrl}">
                                    <img src="${imageUrl}" alt="${categoryName} 画像 ${index + 1}" 
                                         onclick="showImagePreview('${imageUrl}', '${categoryName}')">
                                    <button type="button" class="delete-image-btn" 
                                            onclick="deleteImage('${imageId}', '${categoryName}', this)"
                                            title="この画像を削除">
                                        ×
                                    </button>
                                </div>
                            `;
                        }
                    });
                    
                    html += `
                            </div>
                            <div class="category-actions">
                                <button type="button" class="delete-category-btn" 
                                        onclick="deleteCategory('${categoryName}', this)"
                                        title="このカテゴリーと全ての画像を削除">
                                    🗑️ カテゴリーを削除
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                
                if (html) {
                    container.innerHTML = html;
                }
            }
            
            // 画像プレビュー表示（グローバル関数として定義）
            window.showImagePreview = function(imageUrl, categoryName) {
                // 簡単なプレビュー表示（モーダルまたは新規タブ）
                const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
                previewWindow.document.write(`
                    <html>
                        <head><title>${categoryName} - 画像プレビュー</title></head>
                        <body style="margin:0; background:#f0f0f0; display:flex; justify-content:center; align-items:center; min-height:100vh;">
                            <div style="text-align:center; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                                <h3 style="margin-top:0;">${categoryName}</h3>
                                <img src="${imageUrl}" style="max-width:100%; max-height:80vh; border:1px solid #ddd; border-radius:4px;">
                                <p><button onclick="window.close()" style="margin-top:15px; padding:8px 20px; background:#0073aa; color:white; border:none; border-radius:4px; cursor:pointer;">閉じる</button></p>
                            </div>
                        </body>
                    </html>
                `);
            }
            
            // 個別画像削除（グローバル関数として定義）
            window.deleteImage = function(imageId, categoryName, buttonElement) {
                if (!imageId) {
                    alert('画像IDが取得できませんでした');
                    return;
                }
                
                if (!confirm(`「${categoryName}」から画像を削除しますか？`)) {
                    return;
                }
                
                // 画像削除開始
                
                // Loading状態を表示
                buttonElement.innerHTML = '⏳';
                buttonElement.disabled = true;
                
                // APIを呼び出して削除
                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'delete_category_image',
                        image_id: imageId,
                        _ajax_nonce: '<?php echo wp_create_nonce("studio_shop_nonce"); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // 削除結果を処理
                    if (data.success) {
                        // DOM要素を削除
                        const imageItem = buttonElement.closest('.category-image-item');
                        imageItem.remove();
                        
                        // 成功メッセージを表示
                        showMessage('画像を削除しました', 'success');
                        
                        // カテゴリーに画像が無くなった場合の処理
                        const categoryItem = buttonElement.closest('.category-item');
                        const remainingImages = categoryItem.querySelectorAll('.category-image-item');
                        if (remainingImages.length === 0) {
                            // カテゴリー全体を削除するか確認
                            if (confirm(`「${categoryName}」に画像が無くなりました。カテゴリー自体も削除しますか？`)) {
                                categoryItem.remove();
                                
                                // カテゴリーが全て無くなった場合
                                const container = document.getElementById('existing-categories-container');
                                if (container.children.length === 0) {
                                    container.innerHTML = '<p id="no-categories-message" style="color: #666; font-style: italic;">カテゴリーが登録されていません</p>';
                                }
                            }
                        }
                    } else {
                        alert('画像の削除に失敗しました: ' + (data.error || 'Unknown error'));
                        buttonElement.innerHTML = '×';
                        buttonElement.disabled = false;
                    }
                })
                .catch(error => {
                    alert('画像の削除中にエラーが発生しました');
                    buttonElement.innerHTML = '×';
                    buttonElement.disabled = false;
                });
            }
            
            // カテゴリー全体削除（グローバル関数として定義）
            window.deleteCategory = function(categoryName, buttonElement) {
                if (!confirm(`カテゴリー「${categoryName}」とその中の全ての画像を削除しますか？\n\nこの操作は取り消せません。`)) {
                    return;
                }
                
                // カテゴリー削除開始
                
                // Loading状態を表示
                const originalText = buttonElement.innerHTML;
                buttonElement.innerHTML = '⏳ 削除中...';
                buttonElement.disabled = true;
                
                // 現在選択されているショップIDを取得
                const shopId = document.getElementById('shop_id').value;
                if (!shopId) {
                    alert('ショップが選択されていません');
                    buttonElement.innerHTML = originalText;
                    buttonElement.disabled = false;
                    return;
                }
                
                // APIを呼び出してカテゴリー削除
                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'delete_category',
                        shop_id: shopId,
                        category_name: categoryName,
                        _ajax_nonce: '<?php echo wp_create_nonce("studio_shop_nonce"); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // カテゴリー削除結果を処理
                    if (data.success) {
                        // DOM要素を削除
                        const categoryItem = buttonElement.closest('.category-item');
                        categoryItem.remove();
                        
                        // 成功メッセージを表示
                        showMessage(`カテゴリー「${categoryName}」を削除しました`, 'success');
                        
                        // カテゴリーが全て無くなった場合
                        const container = document.getElementById('existing-categories-container');
                        if (container.children.length === 0) {
                            container.innerHTML = '<p id="no-categories-message" style="color: #666; font-style: italic;">カテゴリーが登録されていません</p>';
                        }
                    } else {
                        alert('カテゴリーの削除に失敗しました: ' + (data.error || 'Unknown error'));
                        buttonElement.innerHTML = originalText;
                        buttonElement.disabled = false;
                    }
                })
                .catch(error => {
                    alert('カテゴリーの削除中にエラーが発生しました');
                    buttonElement.innerHTML = originalText;
                    buttonElement.disabled = false;
                });
            }
            
            // メッセージ表示関数
            function showMessage(message, type = 'info') {
                const existingMessages = document.querySelectorAll('.temp-message');
                existingMessages.forEach(msg => msg.remove());
                
                const messageDiv = document.createElement('div');
                messageDiv.className = `temp-message ${type === 'success' ? 'success-message' : 'error-message'}`;
                messageDiv.textContent = message;
                messageDiv.style.position = 'fixed';
                messageDiv.style.top = '20px';
                messageDiv.style.right = '20px';
                messageDiv.style.zIndex = '9999';
                messageDiv.style.maxWidth = '400px';
                
                document.body.appendChild(messageDiv);
                
                // 3秒後に自動削除
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 3000);
            }

            // New category image preview handler
            if (newCategoryImagesInput) {
                newCategoryImagesInput.addEventListener('change', function() {
                    newCategoryPreview.innerHTML = '';
                    const files = Array.from(this.files);
                    
                    if (files.length === 0) {
                        newCategoryPreview.innerHTML = '<p style="color: #999; text-align: center; margin: 40px 0;">選択した画像のプレビューがここに表示されます</p>';
                        addNewCategoryBtn.disabled = true;
                        return;
                    }
                    
                    // Enable add button if we have name and images
                    if (newCategoryNameInput.value.trim()) {
                        addNewCategoryBtn.disabled = false;
                    }
                    
                    files.forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const preview = document.createElement('div');
                            preview.className = 'image-preview';
                            preview.innerHTML = `
                                <img src="${e.target.result}" alt="Preview ${index + 1}">
                                <button type="button" class="remove-preview-btn" data-index="${index}">×</button>
                            `;
                            newCategoryPreview.appendChild(preview);
                        };
                        reader.readAsDataURL(file);
                    });
                });
            }
            
            // New category name input handler
            if (newCategoryNameInput) {
                newCategoryNameInput.addEventListener('input', function() {
                    // Enable add button if we have name and images
                    if (this.value.trim() && newCategoryImagesInput.files.length > 0) {
                        addNewCategoryBtn.disabled = false;
                    } else {
                        addNewCategoryBtn.disabled = true;
                    }
                });
            }
            
            // Clear new category button handler
            if (clearNewCategoryBtn) {
                clearNewCategoryBtn.addEventListener('click', function() {
                    newCategoryNameInput.value = '';
                    newCategoryImagesInput.value = '';
                    newCategoryPreview.innerHTML = '<p style="color: #999; text-align: center; margin: 40px 0;">選択した画像のプレビューがここに表示されます</p>';
                    addNewCategoryBtn.disabled = true;
                });
            }
            
            // Add new category button handler
            if (addNewCategoryBtn) {
                addNewCategoryBtn.addEventListener('click', function() {
                    const categoryName = newCategoryNameInput.value.trim();
                    const files = Array.from(newCategoryImagesInput.files);
                    
                    if (!categoryName || files.length === 0) {
                        alert('カテゴリー名と画像を入力してください');
                        return;
                    }
                    
                    // Get current shop ID
                    const shopId = document.getElementById('shop_id').value;
                    if (!shopId) {
                        alert('ショップが選択されていません');
                        return;
                    }
                    
                    // Adding new category
                    
                    // Disable button during processing
                    addNewCategoryBtn.disabled = true;
                    addNewCategoryBtn.innerHTML = '⏳ 処理中...';
                    
                    // Convert files to base64
                    const promises = files.map(file => {
                        return new Promise((resolve, reject) => {
                            const reader = new FileReader();
                            reader.onload = (e) => resolve(e.target.result);
                            reader.onerror = reject;
                            reader.readAsDataURL(file);
                        });
                    });
                    
                    Promise.all(promises).then(base64Images => {
                        // Prepare data for API
                        const categoryData = {
                            shop_id: shopId,
                            gallery: [{
                                category_name: categoryName,
                                images: base64Images
                            }]
                        };
                        
                        // Sending to API
                        
                        // Call internal API via AJAX
                        const formData = new FormData();
                        formData.append('action', 'studio_shop_internal_api');
                        formData.append('endpoint', 'category_image_uploader.php');
                        formData.append('data', JSON.stringify(categoryData));
                        
                        fetch('/wp-admin/admin-ajax.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            // Response received
                            if (!response.ok) {
                                throw new Error('HTTP error! status: ' + response.status);
                            }
                            return response.text(); // Get text first to debug
                        })
                        .then(text => {
                            // Processing response
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                throw new Error('Invalid JSON response');
                            }
                        })
                        .then(result => {
                            
                            if (result && result.success) {
                                // Show success message
                                showMessage('カテゴリー「' + categoryName + '」を追加しました', 'success');
                                
                                // Clear the form
                                clearNewCategoryBtn.click();
                                
                                // First refresh the shops list to get updated data
                                fetchShops().then(() => {
                                    // Then update the shop details with fresh data
                                    setTimeout(() => {
                                        updateShopDetails(shopId);
                                    }, 500);
                                });
                            } else {
                                alert('カテゴリーの追加に失敗しました: ' + (result.error || 'Unknown error'));
                            }
                            
                            // Re-enable button
                            addNewCategoryBtn.innerHTML = '➕ このカテゴリーを追加';
                            addNewCategoryBtn.disabled = newCategoryNameInput.value.trim() && newCategoryImagesInput.files.length > 0 ? false : true;
                        })
                        .catch(error => {
                            alert('カテゴリーのアップロード中にエラーが発生しました');
                            addNewCategoryBtn.innerHTML = '➕ このカテゴリーを追加';
                            addNewCategoryBtn.disabled = false;
                        });
                    }).catch(error => {
                        alert('画像の読み込みに失敗しました');
                        addNewCategoryBtn.innerHTML = '➕ このカテゴリーを追加';
                        addNewCategoryBtn.disabled = false;
                    });
                });
            }
            
            // Handle preview image removal for new category
            newCategoryPreview.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-preview-btn')) {
                    const index = parseInt(e.target.dataset.index);
                    const dt = new DataTransfer();
                    const files = Array.from(newCategoryImagesInput.files);
                    
                    files.forEach((file, i) => {
                        if (i !== index) {
                            dt.items.add(file);
                        }
                    });
                    
                    newCategoryImagesInput.files = dt.files;
                    
                    // Refresh preview
                    if (newCategoryImagesInput.files.length > 0) {
                        newCategoryImagesInput.dispatchEvent(new Event('change'));
                    } else {
                        newCategoryPreview.innerHTML = '<p style="color: #999; text-align: center; margin: 40px 0;">選択した画像のプレビューがここに表示されます</p>';
                        addNewCategoryBtn.disabled = true;
                    }
                }
            });

            // Initialize - fetch shops on page load
            setTimeout(() => {
                if (shopSelect && typeof fetchShops === 'function') {
                    fetchShops();
                } else {
                }
            }, 100);
        });
        </script>
    </div>
    <?php
}

// Load plugin text domain for translations
add_action('init', 'studio_shops_load_textdomain');
function studio_shops_load_textdomain() {
    load_plugin_textdomain('studio-shops', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
?>