<?php
/*
Plugin Name: Studio Shops Manager (Simple Gallery) - SIMPLE VERSION
Description: Simplified Studio Shops management with simple gallery upload
Version: 2.0.0
Author: Your Name
*/

defined('ABSPATH') or die('No direct access allowed.');

// Debug function fallbacks
if (!function_exists('wp_debug_log_info')) {
    function wp_debug_log_info($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('INFO: ' . $message . ' ' . json_encode($context));
        }
    }
}

if (!function_exists('wp_debug_log_error')) {
    function wp_debug_log_error($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ERROR: ' . $message . ' ' . json_encode($context));
        }
    }
}

// Include API helper
require_once plugin_dir_path(__FILE__) . 'includes/api-helper.php';

// AJAX handler for internal API calls
add_action('wp_ajax_studio_shop_internal_api', 'handle_studio_shop_internal_api');
add_action('wp_ajax_nopriv_studio_shop_internal_api', 'handle_studio_shop_internal_api');

function handle_studio_shop_internal_api() {
    try {
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
        
        // Add error logging for debugging
        wp_debug_log_info('API request received', ['endpoint' => $endpoint, 'data_keys' => array_keys($data)]);
        
        $result = make_internal_api_call($endpoint, $data);
        
        wp_debug_log_info('API response generated', ['success' => $result['success'] ?? false]);
        
        wp_die(json_encode($result));
        
    } catch (Exception $e) {
        wp_debug_log_error('API handler exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        wp_die(json_encode(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()]));
    } catch (Error $e) {
        wp_debug_log_error('API handler fatal error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        wp_die(json_encode(['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()]));
    }
}

// Add admin menu for Studio Shops
add_action('admin_menu', 'studio_shops_menu');
function studio_shops_menu() {
    add_menu_page(
        'Studio Shops', 
        'Studio Shops',
        'manage_options',
        'studio-shops',
        'studio_shops_page',
        'dashicons-camera-alt',
        6
    );
}

function studio_shops_page() {
    // Clear cache if requested
    if (isset($_GET['clear_studio_cache']) && current_user_can('administrator')) {
        // Clear unified cache system
        if (function_exists('clear_studio_data_cache')) {
            clear_studio_data_cache();
            echo '<div class="notice notice-success"><p>スタジオデータキャッシュをクリアしました。</p></div>';
        }
    }
    
    ?>
    <div class="wrap">
        <h1>🏢 Studio Shops Management (Simple Gallery)</h1>
        <p>店舗情報とシンプルなギャラリー画像を管理します。</p>
        
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('studio_shops_save', 'studio_shops_nonce'); ?>
            
            <!-- キャッシュクリアボタン -->
            <div style="margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                <p><strong>キャッシュ管理:</strong> ショップ情報がフロントエンドに反映されない場合は、キャッシュをクリアしてください。</p>
                <a href="?page=studio-shops&clear_studio_cache=1" class="button button-secondary">🗑️ スタジオデータキャッシュをクリア</a>
            </div>
            
            <div>
                <label><input type="checkbox" id="update-mode" name="update_mode"> Update Existing Shop</label>
                <div id="shop-selector" style="display:none; margin-top:10px;">
                    <select name="shop_id" id="shop-id-select" style="border: 2px solid #ddd; padding: 8px; border-radius: 4px;">
                        <option value="">ショップを選択してください（必須）</option>
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
                $shop_id = isset($_POST['shop_id']) ? sanitize_text_field($_POST['shop_id']) : '';

                // Validate shop_id for update mode
                if ($is_update_mode && empty($shop_id)) {
                    echo '<div class="error"><p>更新モードではショップを選択してください。</p></div>';
                    // Stop processing to prevent further errors
                    return;
                } else {
                    // Handle main image - optimized direct file processing
                    $main_image_processed = null;
                    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                        // Process main image directly to file system
                        $main_image_files = process_and_save_uploaded_files([$_FILES['main_image']], $shop_id ?: 0);
                        if (!empty($main_image_files)) {
                            $main_image_processed = $main_image_files[0]['url'];
                        }
                    }
                    
                    // Handle main gallery images - optimized direct file processing
                    $gallery_images_processed = [];
                    
                    if (!empty($_FILES['gallery_images_flat'])) {
                        // Prepare files array for processing
                        $files_to_process = [];
                        $gallery_flat_files = $_FILES['gallery_images_flat'];
                        
                        // Check if it's a single file or multiple files
                        if (is_array($gallery_flat_files['name'])) {
                            // Multiple files - convert to individual file format
                            for ($i = 0; $i < count($gallery_flat_files['name']); $i++) {
                                if ($gallery_flat_files['error'][$i] === UPLOAD_ERR_OK) {
                                    $files_to_process[] = [
                                        'name' => $gallery_flat_files['name'][$i],
                                        'type' => $gallery_flat_files['type'][$i],
                                        'tmp_name' => $gallery_flat_files['tmp_name'][$i],
                                        'error' => $gallery_flat_files['error'][$i],
                                        'size' => $gallery_flat_files['size'][$i]
                                    ];
                                }
                            }
                        } else {
                            // Single file
                            if ($gallery_flat_files['error'] === UPLOAD_ERR_OK) {
                                $files_to_process[] = $gallery_flat_files;
                            }
                        }
                        
                        // Process all gallery files at once
                        if (!empty($files_to_process)) {
                            $processed_gallery = process_and_save_uploaded_files($files_to_process, $shop_id ?: 0);
                            foreach ($processed_gallery as $processed_file) {
                                $gallery_images_processed[] = $processed_file['url'];
                            }
                        }
                    }

                    // Create the API data - using processed URLs
                    $api_data = [
                        'name' => $name,
                        'address' => $address,
                        'phone' => $phone,
                        'nearest_station' => $nearest_station,
                        'business_hours' => $business_hours,
                        'holidays' => $holidays,
                        'map_url' => $map_url,
                        'company_email' => $company_email,
                        'gallery_images' => $gallery_images_processed
                    ];
                    
                    // Add main image if provided
                    if ($main_image_processed) {
                        $api_data['main_image'] = $main_image_processed;
                    }

                    if ($is_update_mode) {
                        $api_data['shop_id'] = $shop_id;
                    }

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
                                echo '<div class="updated"><p>' . esc_html__($is_update_mode ? 'Shop updated successfully!' : 'Shop created successfully!', 'studio-shops') . '</p></div>';
                                
                                // Add JavaScript to refresh shop list after successful creation/update
                                if (!$is_update_mode && $shop_id) {
                                    echo '<div class="notice notice-info" style="padding: 15px; background: #e8f4f8; border-left: 4px solid #0073aa; margin: 20px 0;">
                                        <p style="font-size: 16px; margin: 0;">
                                            <strong>✅ 店舗が正常に登録されました！</strong><br>
                                            📸 ギャラリー画像の管理ができるようになりました。
                                        </p>
                                    </div>';
                                    
                                    // Auto-switch to update mode and refresh shop list
                                    echo '<script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            setTimeout(() => {
                                                const updateModeCheckbox = document.getElementById("update-mode");
                                                const shopSelector = document.getElementById("shop-selector");
                                                const submitBtn = document.getElementById("submit_shop");
                                                
                                                if (updateModeCheckbox) updateModeCheckbox.checked = true;
                                                if (shopSelector) shopSelector.style.display = "block";
                                                if (submitBtn) submitBtn.value = "🔄 ショップを更新";
                                                
                                                // Refresh shop list after a delay to ensure database commit
                                                setTimeout(() => {
                                                    if (typeof loadShopList === "function") {
                                                        loadShopList();
                                                    }
                                                }, 1000);
                                            }, 100);
                                        });
                                    </script>';
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

            <!-- Basic Information Section -->
            <div style="margin: 30px 0; padding: 25px; border: 2px solid #666; border-radius: 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 20px; font-weight: 600;">🏢 基本情報</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label for="name" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">店舗名 <span style="color: #d63638;">*</span></label>
                        <input type="text" id="name" name="name" required 
                               style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px;" 
                               placeholder="例: えがお写真館 本店">
                    </div>
                    
                    <div>
                        <label for="phone" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">電話番号</label>
                        <input type="text" id="phone" name="phone" 
                               style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px;" 
                               placeholder="例: 03-1234-5678">
                    </div>
                </div>

                <div>
                    <label for="address" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">住所 <span style="color: #d63638;">*</span></label>
                    <textarea id="address" name="address" required rows="3" 
                              style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; resize: vertical;" 
                              placeholder="例: 〒170-0002 東京都豊島区巣鴨３丁目２０−１４ 山下ビル ２F"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div>
                        <label for="nearest_station" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">最寄り駅</label>
                        <input type="text" id="nearest_station" name="nearest_station" 
                               style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px;" 
                               placeholder="例: 巣鴨駅">
                    </div>
                    
                    <div>
                        <label for="company_email" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">メールアドレス</label>
                        <input type="email" id="company_email" name="company_email" 
                               style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px;" 
                               placeholder="例: info@example.com">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div>
                        <label for="business_hours" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">営業時間</label>
                        <input type="text" id="business_hours" name="business_hours" 
                               style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px;" 
                               placeholder="例: 10:00-19:00">
                    </div>
                    
                    <div>
                        <label for="holidays" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">定休日</label>
                        <input type="text" id="holidays" name="holidays" 
                               style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px;" 
                               placeholder="例: 不定休">
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <label for="map_url" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">Google Maps埋め込みコード</label>
                    <textarea id="map_url" name="map_url" rows="4" 
                              style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; resize: vertical;" 
                              placeholder="Google Mapsの埋め込みiframeコードを貼り付けてください"></textarea>
                    <small style="color: #666; display: block; margin-top: 5px;">Google Mapsで「共有」→「地図を埋め込む」からiframeコードをコピーして貼り付けてください</small>
                </div>
            </div>

            <!-- メイン画像セクション -->
            <div style="margin: 30px 0; padding: 25px; border: 2px solid #0073aa; border-radius: 12px; background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);">
                <h3 style="margin: 0 0 20px 0; color: #0073aa; font-size: 20px; font-weight: 600;">🖼️ メイン画像</h3>
                <p style="color: #666; margin-bottom: 20px;">店舗の代表的な画像をアップロードしてください。</p>
                
                <div>
                    <label for="main_image" style="display: block; margin-bottom: 5px; font-weight: 500; color: #0073aa;">メイン画像</label>
                    <input type="file" id="main_image" name="main_image" accept="image/*"
                           style="width: 100%; padding: 10px; border: 2px dashed #0073aa; border-radius: 6px; background: white;">
                    <small style="display: block; margin-top: 5px; color: #666;">JPG、PNG、GIF形式の画像ファイルを選択してください</small>
                </div>
                
                <div id="main-image-preview" style="margin-top: 20px;">
                    <!-- プレビュー画像がここに表示されます -->
                </div>
            </div>

            <!-- Gallery Section -->
            <div style="margin: 30px 0; padding: 25px; border: 2px solid #666; border-radius: 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 20px; font-weight: 600;">📸 ギャラリー画像</h3>
                <p style="color: #666; margin-bottom: 20px;">店舗のギャラリー画像をアップロードできます。複数の画像を同時に選択可能です。</p>
                
                <div>
                    <label for="gallery_images_flat" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">ギャラリー画像</label>
                    <input type="file" id="gallery_images_flat" name="gallery_images_flat[]" multiple accept="image/*"
                           style="width: 100%; padding: 10px; border: 2px dashed #666; border-radius: 6px; background: white;">
                    <small style="display: block; margin-top: 5px; color: #666;">JPG、PNG、GIF形式の画像ファイルを選択してください（複数選択可能）</small>
                </div>
                
                <div id="gallery-preview" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;">
                    <!-- プレビュー画像がここに表示されます -->
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

        <script>
        // WPDebugLogger fallback
        window.WPDebugLogger = window.WPDebugLogger || {
            log: function(message, data) {
                if (typeof wp_debug_log_info === 'function') {
                    wp_debug_log_info(message, data || {});
                } else {
                    // Silent fallback - no console.log as per .claude/docs
                }
            },
            error: function(message, data) {
                if (typeof wp_debug_log_error === 'function') {
                    wp_debug_log_error(message, data || {});
                }
            }
        };
        
        // Global function for loadShopData
        function loadShopData(shopId) {
            jQuery.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'studio_shop_internal_api',
                    endpoint: 'get_all_studio_shop.php'
                },
                timeout: 10000,
                success: function(data) {
                    if (data.success && data.shops) {
                        const shop = data.shops.find(s => s.id == shopId);
                        if (shop) {
                            // Set form fields
                            document.getElementById('name').value = shop.name || '';
                            document.getElementById('address').value = shop.address || '';
                            document.getElementById('phone').value = shop.phone || '';
                            document.getElementById('nearest_station').value = shop.nearest_station || '';
                            document.getElementById('business_hours').value = shop.business_hours || '';
                            document.getElementById('holidays').value = shop.holidays || '';
                            document.getElementById('map_url').value = shop.map_url || '';
                            document.getElementById('company_email').value = shop.company_email || '';
                            document.getElementById('shop_id').value = shop.id;
                            
                            // Gallery images preview
                            const galleryPreview = document.getElementById('gallery-preview');
                            
                            if (shop.main_gallery_images && shop.main_gallery_images.length > 0) {
                                let galleryHtml = '<div style="margin-bottom: 15px; padding: 15px; background: #f8f8f8; border: 2px solid #666; border-radius: 8px;"><p style="margin: 0 0 15px 0; font-weight: bold; color: #333; font-size: 16px;">📸 現在のギャラリー画像 (' + shop.main_gallery_images.length + '枚):</p></div>';
                                
                                shop.main_gallery_images.forEach((image, index) => {
                                    const imageUrl = image.url || image.image_url || image;
                                    const imageId = image.id || index;
                                    
                                    galleryHtml += '<div style="position: relative; background: white; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s;" data-image-id="' + imageId + '"><img src="' + imageUrl + '" alt="ギャラリー画像 ' + (index + 1) + '" style="width: 100%; height: 100px; object-fit: cover; display: block; cursor: pointer;" loading="lazy" onclick="window.open(this.src, \'_blank\')"><div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; padding: 2px; text-align: center; font-size: 11px;">' + (index + 1) + '</div><button onclick="deleteMainGalleryImage(' + imageId + ')" style="position: absolute; top: 4px; right: 4px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" title="この画像を削除">×</button></div>';
                                });
                                
                                galleryPreview.innerHTML = galleryHtml;
                            } else {
                                galleryPreview.innerHTML = '<p style="color: #666; font-style: italic; margin-top: 10px;">ギャラリー画像は設定されていません。</p>';
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    alert('ショップデータの読み込みに失敗しました: ' + error);
                }
            });
        }

        // Global function for deleteMainGalleryImage
        function deleteMainGalleryImage(imageId) {
            if (!confirm('この画像を削除しますか？\\n\\nこの操作は取り消すことができません。')) {
                return;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'studio_shop_internal_api',
                    endpoint: 'delete_main_gallery_image.php',
                    image_id: imageId
                },
                timeout: 10000,
                success: function(data) {
                    if (data.success) {
                        alert(data.message);
                        const shopId = document.getElementById('shop-id-select').value;
                        if (shopId) {
                            loadShopData(shopId);
                        }
                    } else {
                        alert('削除に失敗しました: ' + (data.error || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('削除リクエストでエラーが発生しました: ' + error);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // 自動テスト: ギャラリー画像問題の検出と修正
            setTimeout(function() {
                const updateCheckbox = document.getElementById('update-mode');
                const shopSelect = document.getElementById('shop-id-select');
                
                if (updateCheckbox && shopSelect) {
                    // 自動診断実行
                    WPDebugLogger.log('Auto-diagnostics started');
                    
                    // 既存ショップがある場合、最初のショップで自動テスト
                    fetch('/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=studio_shop_internal_api&endpoint=get_all_studio_shop.php'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                        }
                        return response.text();
                    })
                    .then(responseText => {
                        WPDebugLogger.log('Auto-diagnostics raw response', {preview: responseText.substring(0, 100)});
                        
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (e) {
                            WPDebugLogger.error('Auto-diagnostics JSON parse failed', {error: e.message});
                            throw new Error('Invalid JSON in auto-diagnostics');
                        }
                        
                        if (data.success && data.shops && data.shops.length > 0) {
                            const testShop = data.shops[0];
                            WPDebugLogger.log('Auto-test with shop', {id: testShop.id, name: testShop.name});
                            
                            // ギャラリー画像があるかチェック
                            if (testShop.main_gallery_images && testShop.main_gallery_images.length > 0) {
                                WPDebugLogger.log('Gallery images found, testing display');
                                // ここで表示テストを実行
                            } else {
                                WPDebugLogger.log('No gallery images found for auto-test');
                            }
                        }
                    })
                    .catch(error => {
                        WPDebugLogger.error('Auto-diagnostics failed', {error: error.message});
                    });
                }
            }, 2000);
            
            // Gallery image preview
            const galleryInput = document.getElementById('gallery_images_flat');
            const galleryPreview = document.getElementById('gallery-preview');
            
            galleryInput.addEventListener('change', function(e) {
                galleryPreview.innerHTML = '';
                
                for (let i = 0; i < e.target.files.length; i++) {
                    const file = e.target.files[i];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.width = '100px';
                            img.style.height = '100px';
                            img.style.objectFit = 'cover';
                            img.style.borderRadius = '6px';
                            img.style.border = '2px solid #ddd';
                            galleryPreview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });

            // Update mode functionality
            const updateModeCheckbox = document.getElementById('update-mode');
            const shopSelector = document.getElementById('shop-selector');
            const shopSelect = document.getElementById('shop-id-select');
            const deleteShopBtn = document.getElementById('delete-shop-btn');
            const submitBtn = document.getElementById('submit_shop');
            
            // Form submission validation
            const form = document.querySelector('form[method="post"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Check if update mode is enabled but no shop is selected
                    if (updateModeCheckbox.checked && !shopSelect.value) {
                        e.preventDefault();
                        alert('更新モードではショップを選択してください。');
                        shopSelect.focus();
                        return false;
                    }
                });
            }

            updateModeCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    shopSelector.style.display = 'block';
                    submitBtn.value = '🔄 ショップを更新';
                    loadShopList();
                } else {
                    shopSelector.style.display = 'none';
                    submitBtn.value = '✨ ショップを登録';
                    clearForm();
                }
            });

            function loadShopList() {
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=studio_shop_internal_api&endpoint=get_all_studio_shop.php'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                    }
                    return response.text();
                })
                .then(responseText => {
                    WPDebugLogger.log('loadShopList raw response', {preview: responseText.substring(0, 200)});
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        WPDebugLogger.error('JSON parse failed in loadShopList', {error: e.message, response: responseText});
                        throw new Error('Invalid JSON response');
                    }
                    
                    if (data.success && data.shops) {
                        shopSelect.innerHTML = '<option value="">Select a Shop</option>';
                        data.shops.forEach(shop => {
                            const option = document.createElement('option');
                            option.value = shop.id;
                            option.textContent = shop.name;
                            shopSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    WPDebugLogger.error('Error loading shops', {error: error.message});
                });
            }

            shopSelect.addEventListener('change', function() {
                if (this.value) {
                    deleteShopBtn.style.display = 'inline-block';
                    loadShopData(this.value);
                    // Visual feedback for selected shop
                    this.style.borderColor = '#0073aa';
                    this.style.backgroundColor = '#f0f8ff';
                } else {
                    deleteShopBtn.style.display = 'none';
                    clearForm();
                    // Reset visual styling
                    this.style.borderColor = '#ddd';
                    this.style.backgroundColor = '#fff';
                }
            });

            // Delete shop button event handler
            deleteShopBtn.addEventListener('click', function() {
                const shopId = shopSelect.value;
                if (!shopId) {
                    alert('削除するショップが選択されていません。');
                    return;
                }
                
                const shopName = shopSelect.options[shopSelect.selectedIndex].text;
                if (!confirm('ショップ「' + shopName + '」を完全に削除しますか？\\n\\nこの操作は取り消すことができません。\\n・ショップ情報\\n・ギャラリー画像\\n・カテゴリー画像\\nすべてのデータが削除されます。')) {
                    return;
                }
                
                jQuery.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: {
                        action: 'studio_shop_internal_api',
                        endpoint: 'delete_shop.php',
                        shop_id: shopId
                    },
                    timeout: 15000,
                    dataType: 'json',
                    success: function(data) {
                        console.log('Delete shop response:', data);
                        if (data && data.success) {
                            alert('ショップ「' + shopName + '」を削除しました。\\n\\n削除されたデータ:\\n・メイン画像: ' + (data.deleted_main_images || 0) + '枚\\n・カテゴリー画像: ' + (data.deleted_category_images || 0) + '枚');
                            
                            // Reset form and reload shop list
                            clearForm();
                            shopSelect.value = '';
                            deleteShopBtn.style.display = 'none';
                            loadShopList();
                        } else {
                            const errorMsg = data && data.error ? data.error : (data && data.message ? data.message : 'Unknown error');
                            alert('ショップの削除に失敗しました: ' + errorMsg);
                            console.error('Delete shop failed:', data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete shop AJAX error:', xhr.responseText);
                        alert('削除リクエストでエラーが発生しました: ' + error + '\\n\\nレスポンス: ' + xhr.responseText);
                    }
                });
            });

            function loadShopData(shopId) {
                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=studio_shop_internal_api&endpoint=get_all_studio_shop.php'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                    }
                    return response.text();
                })
                .then(responseText => {
                    WPDebugLogger.log('loadShopData raw response', {preview: responseText.substring(0, 200)});
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        WPDebugLogger.error('loadShopData JSON parse failed', {error: e.message, response: responseText});
                        throw new Error('Invalid JSON response');
                    }
                    
                    WPDebugLogger.log('loadShopData parsed data', data);
                    console.log(data);
                    if (data.success && data.shops) {
                        const shop = data.shops.find(s => s.id == shopId);
                        if (shop) {
                            WPDebugLogger.log('Found shop', {id: shop.id, name: shop.name, gallery_count: shop.main_gallery_images ? shop.main_gallery_images.length : 0});
                            WPDebugLogger.log('Shop gallery images structure', shop.main_gallery_images);
                            // Set form fields
                            document.getElementById('name').value = shop.name || '';
                            document.getElementById('address').value = shop.address || '';
                            document.getElementById('phone').value = shop.phone || '';
                            document.getElementById('nearest_station').value = shop.nearest_station || '';
                            document.getElementById('business_hours').value = shop.business_hours || '';
                            document.getElementById('holidays').value = shop.holidays || '';
                            document.getElementById('map_url').value = shop.map_url || '';
                            document.getElementById('company_email').value = shop.company_email || '';
                            
                            // Main image preview
                            const mainImagePreview = document.getElementById('main-image-preview');
                            if (shop.main_image) {
                                mainImagePreview.innerHTML = '<div style="margin-top: 10px; padding: 10px; background: #f0f8ff; border: 2px solid #0073aa; border-radius: 8px;"><p style="margin: 0 0 10px 0; font-weight: bold; color: #0073aa;">📸 現在のメイン画像:</p><img src="' + shop.main_image + '" alt="現在のメイン画像" style="width: 200px; height: 150px; object-fit: cover; border-radius: 6px; border: 3px solid #0073aa;"><p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">新しい画像をアップロードすると、この画像は置き換えられます。</p></div>';
                            } else {
                                mainImagePreview.innerHTML = '<p style="color: #666; font-style: italic;">メイン画像は設定されていません。</p>';
                            }
                            
                            // Gallery images preview
                            const galleryPreview = document.getElementById('gallery-preview');
                            WPDebugLogger.log('Gallery preview check', {
                                has_main_gallery_images: !!shop.main_gallery_images,
                                gallery_length: shop.main_gallery_images ? shop.main_gallery_images.length : 0,
                                gallery_element_exists: !!galleryPreview
                            });
                            
                            if (shop.main_gallery_images && shop.main_gallery_images.length > 0) {
                                let galleryHtml = '<div style="margin-bottom: 15px; padding: 15px; background: #f8f8f8; border: 2px solid #666; border-radius: 8px;"><p style="margin: 0 0 15px 0; font-weight: bold; color: #333; font-size: 16px;">📸 現在のギャラリー画像 (' + shop.main_gallery_images.length + '枚):</p></div>';
                                
                                shop.main_gallery_images.forEach((image, index) => {
                                    const imageUrl = image.url || image.image_url || image.image_data || image.data || image;
                                    const imageId = image.id || index;
                                    
                                    galleryHtml += '<div style="position: relative; background: white; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform=\'scale(1.05)\'; this.style.boxShadow=\'0 3px 6px rgba(0,0,0,0.2)\'" onmouseout="this.style.transform=\'scale(1)\'; this.style.boxShadow=\'0 1px 3px rgba(0,0,0,0.1)\'" data-image-id="' + imageId + '"><img src="' + imageUrl + '" alt="ギャラリー画像 ' + (index + 1) + '" style="width: 100%; height: 100px; object-fit: cover; display: block; cursor: pointer;" loading="lazy" onclick="window.open(this.src, \'_blank\')"><div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; padding: 2px; text-align: center; font-size: 11px;">' + (index + 1) + '</div><button onclick="deleteGalleryImage(' + imageId + ')" style="position: absolute; top: 4px; right: 4px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background=\'rgba(255,0,0,1)\'" onmouseout="this.style.background=\'rgba(255,0,0,0.8)\'" title="この画像を削除">×</button></div>';
                                });
                                
                                galleryHtml += '<div style="grid-column: 1 / -1; margin-top: 15px;"><button onclick="deleteAllGalleryImages()" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; padding: 8px 16px; font-size: 12px; font-weight: 600; border-radius: 6px; cursor: pointer; margin-bottom: 10px; transition: all 0.3s ease;" onmouseover="this.style.transform=\'translateY(-1px)\'; this.style.boxShadow=\'0 4px 8px rgba(220,53,69,0.3)\'" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'none\'">🗑️ ギャラリー画像を全て削除</button><div style="font-size: 13px; color: #666; padding: 10px; background: #fff; border-radius: 4px; border: 1px solid #ddd;">💡 新しい画像をアップロードすると、既存の画像に追加されます。<br>⚠️ 削除した画像は復元できません。</div></div>';
                                
                                galleryPreview.innerHTML = galleryHtml;
                            } else {
                                galleryPreview.innerHTML = '<p style="color: #666; font-style: italic; margin-top: 10px;">ギャラリー画像は設定されていません。</p>';
                            }
                        }
                    }
                });
            }

            function clearForm() {
                document.getElementById('name').value = '';
                document.getElementById('address').value = '';
                document.getElementById('phone').value = '';
                document.getElementById('nearest_station').value = '';
                document.getElementById('business_hours').value = '';
                document.getElementById('holidays').value = '';
                document.getElementById('map_url').value = '';
                document.getElementById('company_email').value = '';
            }
        });
        
        function deleteGalleryImage(imageId) {
            if (!confirm('この画像を削除しますか？\\n\\nこの操作は取り消すことができません。')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'studio_shop_internal_api');
            formData.append('endpoint', 'delete_main_gallery_image.php');
            formData.append('image_id', imageId);
            
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    const shopId = document.getElementById('shop-id-select').value;
                    loadShopData(shopId);
                } else {
                    alert('削除に失敗しました: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('削除リクエストでエラーが発生しました: ' + error.message);
            });
        }
        
        function deleteAllGalleryImages() {
            const shopId = document.getElementById('shop-id-select').value;
            if (!shopId) {
                alert('ショップが選択されていません。');
                return;
            }
            
            if (!confirm('ギャラリー画像を全て削除しますか？\\n\\nこの操作は取り消すことができません。')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'studio_shop_internal_api');
            formData.append('endpoint', 'delete_gallery_image.php');
            formData.append('shop_id', shopId);
            formData.append('delete_all', '1');
            
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadShopData(shopId);
                } else {
                    alert('削除に失敗しました: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('削除リクエストでエラーが発生しました: ' + error.message);
            });
        }
        </script>
    </div>
    <?php
}
?>