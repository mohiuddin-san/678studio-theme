<?php
/*
Plugin Name: Studio Shops Manager
Description: Manage Studio Shops and their gallery images via API in WordPress admin dashboard.
Version: 1.7.4
Author: Your Name
*/

defined('ABSPATH') or die('No direct access allowed.');

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

// Admin page rendering and form handling
function studio_shops_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Studio Shops Manager', 'studio-shops'); ?></h1>
        
        <div>
            <label><input type="checkbox" id="update-mode" name="update_mode"> Update Existing Shop</label>
            <div id="shop-selector" style="display:none; margin-top:10px;">
                <select name="shop_id" id="shop-id-select">
                    <option value="">Select a Shop</option>
                </select>
            </div>
        </div>

        <?php
        // Check if form is submitted
        $is_update_mode = isset($_POST['update_mode']) && $_POST['update_mode'] === 'on';
        
        if (isset($_POST['submit_shop']) && check_admin_referer('studio_shops_save', 'studio_shops_nonce')) {
            // Debug: Log all POST data at the very start
            error_log('=== FORM SUBMISSION DEBUG START ===');
            error_log('POST data: ' . print_r($_POST, true));
            error_log('FILES data: ' . print_r($_FILES, true));
            error_log('Update mode: ' . ($is_update_mode ? 'YES' : 'NO'));
            error_log('=== FORM SUBMISSION DEBUG END ===');
            
            // Debug display for admin  
            $main_files_debug = 'NO FILES';
            if (!empty($_FILES['gallery_images_flat']['name'][0])) {
                $main_files_debug = count($_FILES['gallery_images_flat']['name']) . ' files';
            } elseif (isset($_FILES['gallery_images_flat'])) {
                $main_files_debug = 'FILES FIELD EXISTS BUT EMPTY: ' . print_r($_FILES['gallery_images_flat']['error'], true);
            }
            
            echo '<div class="notice notice-info"><p><strong>Debug Info:</strong><br>';
            echo 'Main gallery files: ' . $main_files_debug . '<br>';
            echo 'Category files: ' . (!empty($_FILES['gallery_images']) ? 'YES' : 'NO') . '<br>';
            echo 'Update mode: ' . ($is_update_mode ? 'YES' : 'NO') . '<br>';
            echo 'Shop ID: ' . (isset($_POST['shop_id']) ? $_POST['shop_id'] : 'Not set') . '<br>';
            echo 'Total POST data: ' . count($_POST) . ' fields<br>';
            echo 'Category names: ' . (isset($_POST['category_name']) ? implode(', ', array_unique($_POST['category_name'])) : 'None') . ' (unique: ' . (isset($_POST['category_name']) ? count(array_unique($_POST['category_name'])) : '0') . ')';
            echo '</p></div>';
            
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

            // Debug: Log the shop_id to verify it's being received
            error_log('Submitted shop_id: ' . $shop_id);

            // Validate shop_id for update mode
            if ($is_update_mode && empty($shop_id)) {
                echo '<div class="error"><p>' . esc_html__('Error: Shop ID is missing during update.', 'studio-shops') . '</p></div>';
            } else {
                // Debug: Log $_FILES data
                error_log('$_FILES data: ' . print_r($_FILES, true));

                // Handle main gallery images FIRST
                $main_gallery_images = [];
                error_log('DEBUG: $_FILES[gallery_images_flat] = ' . print_r($_FILES['gallery_images_flat'] ?? 'NOT SET', true));
                error_log('DEBUG: Full $_FILES dump: ' . print_r($_FILES, true));
                
                if (!empty($_FILES['gallery_images_flat'])) {
                    error_log('Processing main gallery images...');
                    $gallery_flat_files = $_FILES['gallery_images_flat'];
                    
                    // Check if it's a single file or multiple files
                    if (is_array($gallery_flat_files['name'])) {
                        // Multiple files
                        for ($i = 0; $i < count($gallery_flat_files['name']); $i++) {
                            error_log("Processing image $i: " . $gallery_flat_files['name'][$i] . ", error: " . $gallery_flat_files['error'][$i]);
                            if ($gallery_flat_files['error'][$i] === UPLOAD_ERR_OK) {
                                $tmp_name = $gallery_flat_files['tmp_name'][$i];
                                if (file_exists($tmp_name)) {
                                    $image_data = file_get_contents($tmp_name);
                                    $image_type = $gallery_flat_files['type'][$i];
                                    $base64_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                                    $main_gallery_images[] = $base64_image;
                                    error_log("Successfully processed image: " . $gallery_flat_files['name'][$i] . " (Size: " . strlen($base64_image) . " chars)");
                                } else {
                                    error_log("ERROR: Temp file does not exist: " . $tmp_name);
                                }
                            } else {
                                error_log("ERROR: Upload error for file " . $gallery_flat_files['name'][$i] . ": " . $gallery_flat_files['error'][$i]);
                            }
                        }
                    } else {
                        // Single file
                        error_log("Processing single image: " . $gallery_flat_files['name'] . ", error: " . $gallery_flat_files['error']);
                        if ($gallery_flat_files['error'] === UPLOAD_ERR_OK) {
                            $tmp_name = $gallery_flat_files['tmp_name'];
                            if (file_exists($tmp_name)) {
                                $image_data = file_get_contents($tmp_name);
                                $image_type = $gallery_flat_files['type'];
                                $base64_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                                $main_gallery_images[] = $base64_image;
                                error_log("Successfully processed single image: " . $gallery_flat_files['name'] . " (Size: " . strlen($base64_image) . " chars)");
                            } else {
                                error_log("ERROR: Single temp file does not exist: " . $tmp_name);
                            }
                        } else {
                            error_log("ERROR: Upload error for single file " . $gallery_flat_files['name'] . ": " . $gallery_flat_files['error']);
                        }
                    }
                    error_log("Total main gallery images processed: " . count($main_gallery_images));
                } else {
                    error_log('No main gallery images found in $_FILES[gallery_images_flat]');
                    if (isset($_FILES['gallery_images_flat'])) {
                        error_log('gallery_images_flat exists but no valid files: ' . print_r($_FILES['gallery_images_flat'], true));
                    } else {
                        error_log('gallery_images_flat field not found in $_FILES');
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

                // Debug: Log the API payload
                error_log('API Payload: ' . print_r($api_data, true));

                echo '<div id="loader" style="padding:10px; font-weight:bold; color:blue;">Processing shop data, please wait...</div>';

                // Send API request for shop creation or update
                $api_url = $is_update_mode ? 
                    'https://678photo.com/api/update_shop_details.php' : 
                    'https://678photo.com/api/studio_shop.php';

                error_log('Sending to API: ' . $api_url);
                error_log('API Data being sent: ' . json_encode($api_data, JSON_UNESCAPED_UNICODE));
                
                $response = wp_remote_post($api_url, [
                    'method' => 'POST',
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($api_data),
                    'timeout' => 60
                ]);

                error_log('Raw API Response: ' . print_r($response, true));
                
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo '<div class="error"><p>' . esc_html__('API request failed: ' . $error_message, 'studio-shops') . '</p></div>';
                } else {
                    $response_body = json_decode(wp_remote_retrieve_body($response), true);
                    error_log('API Response (Submit): ' . print_r($response_body, true)); // Debug API response
                    if (isset($response_body['success']) && $response_body['success']) {
                        $shop_id = $is_update_mode ? $shop_id : ($response_body['shop_id'] ?? '');
                        
                        if ($shop_id) {
                            // Prepare category gallery payload
                            $category_gallery = [];
                            $has_category_images = false;
                            
                            error_log('Processing categories: ' . print_r($category_names, true));
                            error_log('Gallery files structure: ' . print_r($gallery_files, true));
                            
                            // Remove duplicate category names but preserve their associated files
                            $unique_categories = [];
                            foreach ($category_names as $cat_index => $cat_name) {
                                $cat_name = sanitize_text_field($cat_name);
                                if (empty($cat_name)) {
                                    error_log("Skipping empty category at index {$cat_index}");
                                    continue;
                                }
                                
                                // Store all indices for each unique category name
                                if (!isset($unique_categories[$cat_name])) {
                                    $unique_categories[$cat_name] = [];
                                }
                                $unique_categories[$cat_name][] = $cat_index;
                            }
                            
                            error_log("Unique categories: " . print_r($unique_categories, true));
                            
                            foreach ($unique_categories as $cat_name => $indices) {
                                error_log("Processing category: {$cat_name} with indices: " . implode(',', $indices));
                                
                                // Initialize category array
                                $category_gallery[$cat_name] = [];

                                // Process all files for all indices of this category
                                foreach ($indices as $cat_index) {
                                    if (isset($gallery_files['name'][$cat_index]) && is_array($gallery_files['name'][$cat_index])) {
                                        error_log("Found files for category {$cat_name} at index {$cat_index}: " . print_r($gallery_files['name'][$cat_index], true));
                                        foreach ($gallery_files['name'][$cat_index] as $img_index => $img_name) {
                                            error_log("Processing file {$img_index}: {$img_name}, error: " . $gallery_files['error'][$cat_index][$img_index]);
                                            if (!empty($img_name) && $gallery_files['error'][$cat_index][$img_index] === UPLOAD_ERR_OK) {
                                                $tmp_name = $gallery_files['tmp_name'][$cat_index][$img_index];
                                                if (file_exists($tmp_name)) {
                                                    $image_data = file_get_contents($tmp_name);
                                                    $image_type = $gallery_files['type'][$cat_index][$img_index];
                                                    $base64_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                                                    $category_gallery[$cat_name][] = $base64_image;
                                                    $has_category_images = true;
                                                    error_log("SUCCESS: Category image processed: {$cat_name} - {$img_name} (Base64 length: " . strlen($base64_image) . ")");
                                                } else {
                                                    error_log("ERROR: Temp file not found: {$tmp_name}");
                                                }
                                            } else {
                                                error_log("SKIP: File {$img_name} - empty name or upload error: " . ($gallery_files['error'][$cat_index][$img_index] ?? 'unknown'));
                                            }
                                        }
                                    } else {
                                        error_log("No files found for category {$cat_name} at index {$cat_index}");
                                    }
                                }
                            }
                            
                            error_log('Category gallery data: ' . print_r($category_gallery, true));
                            error_log('Has category images: ' . ($has_category_images ? 'YES' : 'NO'));

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
                            
                            // Debug final payload
                            error_log('Final payload gallery: ' . print_r($final_payload['gallery'], true));

                            // Main gallery images are now included in the main API call above
                            $main_success = true;
                            if (!empty($main_gallery_images)) {
                                error_log('Main gallery images included in main API call: ' . count($main_gallery_images) . ' images');
                                error_log('First image sample: ' . substr($main_gallery_images[0], 0, 50) . '...');
                            } else {
                                error_log('No main gallery images to upload - array is empty');
                            }

                            // Send category images to the appropriate API
                            $category_api_url = $is_update_mode ? 
                                'https://678photo.com/api/update_shop_category_images.php' : 
                                'https://678photo.com/api/category_image_uploader.php';

                            if (!$has_category_images || empty($final_payload['gallery'])) {
                                // No category images to process, skip API call
                                error_log('Skipping category API call - has_category_images: ' . ($has_category_images ? 'YES' : 'NO') . ', gallery count: ' . count($final_payload['gallery']));
                                $category_success = true;
                            } else {
                                error_log('Sending category images to API: ' . $category_api_url);
                                error_log('Category payload: ' . print_r($final_payload, true));
                                
                                $image_response = wp_remote_post($category_api_url, [
                                    'method' => 'POST',
                                    'headers' => ['Content-Type' => 'application/json'],
                                    'body' => json_encode($final_payload),
                                    'timeout' => 60
                                ]);

                                if (is_wp_error($image_response)) {
                                    error_log('Category image API error: ' . $image_response->get_error_message());
                                    echo '<div class="error"><p>' . esc_html__('Failed to upload category images: ' . $image_response->get_error_message(), 'studio-shops') . '</p></div>';
                                    $category_success = false;
                                } else {
                                    $image_response_body = json_decode(wp_remote_retrieve_body($image_response), true);
                                    error_log('Category Image API Response: ' . print_r($image_response_body, true));
                                    if (!isset($image_response_body['success']) || !$image_response_body['success']) {
                                        $error_msg = $image_response_body['error'] ?? 'Unknown error';
                                        error_log('Category image upload failed: ' . $error_msg);
                                        
                                        // Check if it's a duplicate entry error for update mode
                                        if ($is_update_mode && strpos($error_msg, 'Duplicate entry') !== false) {
                                            error_log('Duplicate entry detected, attempting to delete existing categories first...');
                                            
                                            // Try to delete existing category images before inserting new ones
                                            $delete_payload = ['shop_id' => $shop_id];
                                            $delete_response = wp_remote_post('https://678photo.com/api/delete_shop_category_images.php', [
                                                'method' => 'POST',
                                                'headers' => ['Content-Type' => 'application/json'],
                                                'body' => json_encode($delete_payload),
                                                'timeout' => 30
                                            ]);
                                            
                                            if (!is_wp_error($delete_response)) {
                                                $delete_response_body = json_decode(wp_remote_retrieve_body($delete_response), true);
                                                error_log('Delete API Response: ' . print_r($delete_response_body, true));
                                                
                                                if (isset($delete_response_body['success']) && $delete_response_body['success']) {
                                                    error_log('Category images deleted successfully, attempting re-upload...');
                                                } else {
                                                    error_log('Delete API failed: ' . ($delete_response_body['error'] ?? 'Unknown error'));  
                                                }
                                                
                                                error_log('Attempting to re-upload category images after deletion...');
                                                // Retry the category image upload
                                                $retry_response = wp_remote_post($category_api_url, [
                                                    'method' => 'POST',
                                                    'headers' => ['Content-Type' => 'application/json'],
                                                    'body' => json_encode($final_payload),
                                                    'timeout' => 60
                                                ]);
                                                
                                                if (!is_wp_error($retry_response)) {
                                                    $retry_body = json_decode(wp_remote_retrieve_body($retry_response), true);
                                                    error_log('Retry API Response: ' . print_r($retry_body, true));
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
                                error_log('Cleared cache for shop ID: ' . $shop_id);
                            }
                            
                            // Show final success message
                            if ($main_success && $category_success) {
                                echo '<div class="updated"><p>' . esc_html__($is_update_mode ? 'Shop updated successfully!' : 'Shop created successfully!', 'studio-shops') . '</p></div>';
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
            <table class="form-table">
                <tr><th><label for="name">Name</label></th><td><input type="text" name="name" id="name" required></td></tr>
                <tr><th><label for="address">Address</label></th><td><textarea name="address" id="address" required></textarea></td></tr>
                <tr><th><label for="phone">Phone</label></th><td><input type="text" name="phone" id="phone"></td></tr>
                <tr><th><label for="nearest_station">Nearest Station</label></th><td><input type="text" name="nearest_station" id="nearest_station"></td></tr>
                <tr><th><label for="business_hours">Business Hours</label></th><td><input type="text" name="business_hours" id="business_hours"></td></tr>
                <tr><th><label for="holidays">Holidays</label></th><td><input type="text" name="holidays" id="holidays"></td></tr>
                <tr><th><label for="map_url">Map Embed Code</label></th><td><textarea name="map_url" id="map_url" rows="4" cols="50" placeholder="Paste your map embed code (e.g., Google Maps iframe)"></textarea></td></tr>     
                <tr><th><label for="company_email">Company Email</label></th><td><input type="email" name="company_email" id="company_email"></td></tr>
            </table>

            <h3>Main Gallery (no category)</h3>
            <p><input type="file" name="gallery_images_flat[]" multiple accept="image/*" id="main-gallery-input"></p>
            <div id="main-gallery-preview"></div>

            <h3>Gallery by Category</h3>
            <div id="existing-categories-section" style="margin-bottom: 20px;">
                <h4>Existing Categories</h4>
                <p><em>Select from existing categories or create new ones below:</em></p>
                <div id="existing-categories-list" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                    <!-- Categories will be populated here -->
                </div>
            </div>
            
            <div id="category-gallery-wrapper">
                <div class="category-gallery-block" data-category-id="">
                    <div class="category-input-section">
                        <label>Category:</label>
                        <select name="category_name[]" class="category-select" style="width: 200px; margin-right: 10px;">
                            <option value="">Choose existing or type new...</option>
                        </select>
                        <input type="text" class="new-category-input" placeholder="Or type new category name" style="width: 200px; margin-left: 10px;">
                    </div>
                    <input type="file" name="gallery_images[0][]" multiple accept="image/*" class="category-image-input">
                    <div class="category-preview" data-index="0"></div>
                    <button type="button" class="delete-category button">Delete Category</button>
                </div>
            </div>
            <p><button type="button" id="add-category-block" class="button">+ Add Category</button></p>

            <p class="submit">
                <input type="submit" name="submit_shop" id="submit_shop" class="button button-primary" value="<?php echo $is_update_mode ? 'Update Shop' : 'Add Shop'; ?>">
            </p>
        </form>

        <style>
            .image-preview {
                display: inline-block;
                position: relative;
                margin: 5px;
            }
            .image-preview img {
                width: 100px;
                height: 100px;
                object-fit: cover;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .remove-image, .delete-category {
                background: red;
                color: white;
                border: none;
                border-radius: 50%;
                font-size: 14px;
                width: 20px;
                height: 20px;
                cursor: pointer;
                position: absolute;
                top: -8px;
                right: -8px;
            }
            .delete-category {
                border-radius: 4px;
                width: auto;
                height: auto;
                padding: 5px 10px;
                position: static;
                margin-top: 5px;
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('studio-shops-manager.js loaded');
            
            // Global variable to store all categories
            window.allCategories = new Set();

            // Debug DOM structure
            console.log('update-mode:', document.getElementById('update-mode'));
            console.log('shop-selector:', document.getElementById('shop-selector'));
            console.log('shop-id-select:', document.getElementById('shop-id-select'));
            console.log('shop-form:', document.getElementById('shop-form'));
            console.log('main-gallery-preview:', document.getElementById('main-gallery-preview'));
            console.log('category-gallery-wrapper:', document.getElementById('category-gallery-wrapper'));

            const updateCheckbox = document.getElementById('update-mode');
            const shopSelector = document.getElementById('shop-selector');
            const shopSelect = document.getElementById('shop-id-select');
            const form = document.getElementById('shop-form');
            const mainGalleryPreview = document.getElementById('main-gallery-preview');
            const categoryGalleryWrapper = document.getElementById('category-gallery-wrapper');

            // Initialize shopsData to store shop details
            window.shopsData = [];

            // Function to reset the form completely
            function resetForm(preserveFiles = false) {
                console.log('Resetting form...', preserveFiles ? '(preserving files)' : '(clearing all)');
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
                
                if (!preserveFiles) {
                    document.getElementById('main-gallery-input').value = '';
                    mainGalleryPreview.innerHTML = '<p>No images selected.</p>';
                    categoryGalleryWrapper.innerHTML = `
                        <div class="category-gallery-block" data-category-id="">
                            <input type="text" name="category_name[]" placeholder="Category Name" required>
                            <input type="file" name="gallery_images[0][]" multiple accept="image/*" class="category-image-input">
                            <div class="category-preview" data-index="0"></div>
                            <button type="button" class="delete-category button">Delete Category</button>
                        </div>
                    `;
                }
            }

            // Validate shop_id and process categories before form submission
            form.addEventListener('submit', (e) => {
                if (updateCheckbox.checked && !document.getElementById('shop_id').value) {
                    e.preventDefault();
                    alert('Please select a shop to update.');
                    return;
                }
                
                // Process category names: merge select and text inputs
                const categoryBlocks = document.querySelectorAll('.category-gallery-block');
                const processedCategories = [];
                let hasError = false;
                
                categoryBlocks.forEach((block, index) => {
                    const select = block.querySelector('.category-select');
                    const textInput = block.querySelector('.new-category-input');
                    let categoryName = '';
                    
                    if (select && select.value.trim()) {
                        categoryName = select.value.trim();
                    } else if (textInput && textInput.value.trim()) {
                        categoryName = textInput.value.trim();
                    }
                    
                    if (categoryName) {
                        // Check for duplicates
                        if (processedCategories.includes(categoryName)) {
                            alert(`Error: Category "${categoryName}" is duplicated. Please use unique category names.`);
                            hasError = true;
                            return;
                        }
                        processedCategories.push(categoryName);
                        
                        // Update the hidden input for form submission
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'category_name[]';
                        hiddenInput.value = categoryName;
                        form.appendChild(hiddenInput);
                    }
                });
                
                if (hasError) {
                    e.preventDefault();
                    return;
                }
                
                console.log('Processed categories for submission:', processedCategories);
            });

            // Fetch shop list
            async function fetchShops() {
                try {
                    const response = await fetch('https://678photo.com/api/get_all_studio_shop.php?t=' + new Date().getTime(), {
                        cache: 'no-store'
                    });
                    console.log('Shop List API Response:', response);
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    const data = await response.json();
                    console.log('Shop List API Data:', data);
                    if (data.success && data.shops) {
                        window.shopsData = data.shops;
                        populateDropdown(data.shops);
                    } else {
                        console.error('Failed to fetch shops:', data.error || 'No shops found');
                        alert('Failed to load shop list');
                    }
                } catch (error) {
                    console.error('Error fetching shops:', error);
                    alert('Failed to load shop list');
                }
            }

            // Populate shop dropdown and collect categories
            function populateDropdown(shops) {
                console.log('Populating dropdown with shops:', shops);
                if (!shopSelect) {
                    console.error('Error: shop-id-select not found');
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
                    
                    // Debug category_images structure
                    console.log(`Shop ${shop.id} (${shop.name}) category_images:`, shop.category_images, typeof shop.category_images, Array.isArray(shop.category_images));
                    
                    // Collect categories from this shop
                    if (shop.category_images && typeof shop.category_images === 'object' && !Array.isArray(shop.category_images)) {
                        Object.keys(shop.category_images).forEach(category => {
                            if (category && category.trim()) {
                                console.log(`Adding category: "${category.trim()}"`);
                                window.allCategories.add(category.trim());
                            }
                        });
                    }
                });
                
                console.log('Dropdown populated:', shopSelect);
                console.log('All categories collected:', Array.from(window.allCategories));
                
                // Wait a bit for DOM to be ready before updating selectors
                setTimeout(() => {
                    updateCategorySelectors();
                }, 200);
            }
            
            // Update category selector UI
            function updateCategorySelectors() {
                console.log('Updating category selectors...');
                console.log('Available categories:', Array.from(window.allCategories));
                console.log('Category selectors found:', document.querySelectorAll('.category-select').length);
                
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
                    console.log(`Updating select ${index}:`, select);
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
                        console.log(`Added option: ${category}`);
                    });
                });
            }

            // Populate form with shop details
            function updateShopDetails(shopId) {
                console.log('Updating shop details for shopId:', shopId);
                resetForm(true); // Reset form but preserve files
                document.getElementById('shop_id').value = shopId;

                if (!shopId) {
                    console.log('No shop selected, form reset');
                    return;
                }

                const shop = window.shopsData.find(s => s.id == shopId);
                if (!shop) {
                    console.error('Shop not found for ID:', shopId);
                    alert('Shop data not found');
                    return;
                }

                console.log('Populating form with shop data:', shop);
                document.getElementById('name').value = shop.name || '';
                document.getElementById('address').value = shop.address || '';
                document.getElementById('phone').value = shop.phone || '';
                document.getElementById('nearest_station').value = shop.nearest_station || '';
                document.getElementById('business_hours').value = shop.business_hours || '';
                document.getElementById('holidays').value = shop.holidays || '';
                document.getElementById('map_url').value = shop.map_url || '';
                document.getElementById('company_email').value = shop.company_email || '';

                // Populate main gallery images from image_urls
                mainGalleryPreview.innerHTML = '';
                if (shop.image_urls && shop.image_urls.length > 0) {
                    shop.image_urls.forEach((imageUrl, index) => {
                        const div = document.createElement('div');
                        div.classList.add('image-preview');
                        div.innerHTML = `
                            <img src="${imageUrl}" alt="Main Gallery Image" style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;">
                            <button type="button" class="remove-main-image" data-type="main" data-shop-id="${shop.id}" data-image-url="${imageUrl}" data-index="${index}">×</button>
                        `;
                        mainGalleryPreview.appendChild(div);
                    });
                } else {
                    mainGalleryPreview.innerHTML = '<p>No main gallery images available.</p>';
                }

                // Populate category gallery from category_images
                categoryGalleryWrapper.innerHTML = '';
                if (shop.category_images && Object.keys(shop.category_images).length > 0) {
                    let index = 0;
                    for (const [categoryName, images] of Object.entries(shop.category_images)) {
                        const block = document.createElement('div');
                        block.classList.add('category-gallery-block');
                        block.dataset.categoryId = ''; // Fetch category IDs if available from API
                        block.innerHTML = `
                            <div class="category-input-section">
                                <label>Category:</label>
                                <select name="category_name[]" class="category-select" style="width: 200px; margin-right: 10px;">
                                    <option value="${categoryName}" selected>${categoryName}</option>
                                </select>
                                <input type="text" class="new-category-input" placeholder="Or type new category name" style="width: 200px; margin-left: 10px; display: none;">
                            </div>
                            <input type="file" name="gallery_images[${index}][]" multiple accept="image/*" class="category-image-input">
                            <div class="category-preview" data-index="${index}"></div>
                            <button type="button" class="delete-category button">Delete Category</button>
                        `;
                        categoryGalleryWrapper.appendChild(block);

                        const preview = block.querySelector('.category-preview');
                        if (images && images.length > 0) {
                            images.forEach((imageUrl, imgIndex) => {
                                const div = document.createElement('div');
                                div.classList.add('image-preview');
                                div.innerHTML = `
                                    <img src="${imageUrl}" alt="Category Image">
                                    <button type="button" class="remove-image" data-type="category" data-index="${imgIndex}" data-image-id="">×</button>
                                `;
                                preview.appendChild(div);
                            });
                        } else {
                            preview.innerHTML = '<p>No images available for this category.</p>';
                        }
                        
                        // Setup event listeners for this block
                        setupCategoryBlockListeners(block);
                        index++;
                    }
                    
                    // Update category selectors after populating existing categories
                    setTimeout(() => {
                        updateCategorySelectors();
                    }, 100);
                } else {
                    categoryGalleryWrapper.innerHTML = `
                        <div class="category-gallery-block" data-category-id="">
                            <div class="category-input-section">
                                <label>Category:</label>
                                <select name="category_name[]" class="category-select" style="width: 200px; margin-right: 10px;">
                                    <option value="">Choose existing or type new...</option>
                                </select>
                                <input type="text" class="new-category-input" placeholder="Or type new category name" style="width: 200px; margin-left: 10px;">
                            </div>
                            <input type="file" name="gallery_images[0][]" multiple accept="image/*" class="category-image-input">
                            <div class="category-preview" data-index="0"></div>
                            <button type="button" class="delete-category button">Delete Category</button>
                        </div>
                    `;
                    
                    // Setup listeners for the default block
                    setTimeout(() => {
                        const defaultBlock = categoryGalleryWrapper.querySelector('.category-gallery-block');
                        if (defaultBlock) {
                            setupCategoryBlockListeners(defaultBlock);
                            updateCategorySelectors();
                        }
                    }, 100);
                }
            }

            // Toggle update mode
            updateCheckbox.addEventListener('change', () => {
                console.log('Update mode changed:', updateCheckbox.checked);
                shopSelector.style.display = updateCheckbox.checked ? 'block' : 'none';
                document.getElementById('update_mode').value = updateCheckbox.checked ? 'on' : 'off';
                document.getElementById('submit_shop').value = updateCheckbox.checked ? 'Update Shop' : 'Add Shop';
                shopSelect.value = ''; // Reset shop selection
                updateShopDetails(''); // Reset form
            });

            // Populate form on shop selection
            shopSelect.addEventListener('change', (event) => {
                console.log('Shop selected:', event.target.value);
                updateShopDetails(event.target.value);
            });

            // Add new category block
            document.getElementById('add-category-block').addEventListener('click', () => {
                let index = categoryGalleryWrapper.children.length;
                let block = document.createElement('div');
                block.classList.add('category-gallery-block');
                block.dataset.categoryId = '';
                block.innerHTML = `
                    <div class="category-input-section">
                        <label>Category:</label>
                        <select name="category_name[]" class="category-select" style="width: 200px; margin-right: 10px;">
                            <option value="">Choose existing or type new...</option>
                        </select>
                        <input type="text" class="new-category-input" placeholder="Or type new category name" style="width: 200px; margin-left: 10px;">
                    </div>
                    <input type="file" name="gallery_images[${index}][]" multiple accept="image/*" class="category-image-input">
                    <div class="category-preview" data-index="${index}"></div>
                    <button type="button" class="delete-category button">Delete Category</button>
                `;
                categoryGalleryWrapper.appendChild(block);
                
                // Add event listeners for the new block first
                setupCategoryBlockListeners(block);
                
                // Then update the new category select with existing categories
                setTimeout(() => {
                    updateCategorySelectors();
                }, 100);
            });
            
            // Setup event listeners for category input interaction
            function setupCategoryBlockListeners(block) {
                const select = block.querySelector('.category-select');
                const textInput = block.querySelector('.new-category-input');
                
                // When select changes, clear text input
                select.addEventListener('change', () => {
                    if (select.value) {
                        textInput.value = '';
                        textInput.style.display = 'none';
                    } else {
                        textInput.style.display = 'inline-block';
                    }
                });
                
                // When text input gets focus, clear select
                textInput.addEventListener('focus', () => {
                    select.value = '';
                });
                
                // Show/hide text input based on select value
                if (select.value) {
                    textInput.style.display = 'none';
                } else {
                    textInput.style.display = 'inline-block';
                }
            }
            
            // Setup listeners for initial category blocks
            document.querySelectorAll('.category-gallery-block').forEach(block => {
                setupCategoryBlockListeners(block);
            });

            // Main gallery preview for new uploads
            document.getElementById('main-gallery-input').addEventListener('change', function () {
                handleFilePreview(this, mainGalleryPreview, 'main');
            });

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
                            fetch('https://678photo.com/api/delete_category.php', {
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
                                console.error('Error deleting category:', error);
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
                        fetch('https://678photo.com/api/delete_shop_main_image.php', {
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
                            console.error('Error deleting main gallery image:', error);
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
                            fetch('https://678photo.com/api/delete_category_image.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ image_id: imageId })
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
                                console.error('Error deleting image:', error);
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

            // Initialize category block listeners first
            setTimeout(() => {
                document.querySelectorAll('.category-gallery-block').forEach(block => {
                    setupCategoryBlockListeners(block);
                });
                
                // Then fetch shops and update selectors
                fetchShops();
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