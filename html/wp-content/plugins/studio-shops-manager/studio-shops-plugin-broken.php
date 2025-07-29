<?php
/*
Plugin Name: Studio Shops Manager - COMPLETE VERSION
Description: Simplified Studio Shops management with simple gallery upload
Version: 2.0.0
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

// AJAX handler for form submission

add_action('wp_ajax_studio_shop_form_submit', 'handle_studio_shop_form_submit');
function handle_studio_shop_form_submit() {
    // フォーム送信処理をそのまま実行
    ob_start();
    studio_shops_page();
    $output = ob_get_clean();
    
    echo $output;
    wp_die();
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
        }
    }
    
    // 成功メッセージを先に処理
    $show_success_message = false;
    $success_shop_id = null;
    $success_is_update = false;
    
    if (isset($_POST['submit_shop']) && check_admin_referer('studio_shops_save', 'studio_shops_nonce')) {
        // フォーム処理はこの後で行うが、フラグだけ先に設定
        $show_success_message = true;
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
                $shop_id = isset($_POST['shop_id']) ? sanitize_text_field($_POST['shop_id']) : '';

                
                // Validate shop_id for update mode
                if ($is_update_mode && empty($shop_id)) {
                    echo '<div class="error"><p>' . esc_html__('Error: Shop ID is missing during update. Please select a shop from the dropdown.', 'studio-shops') . '</p></div>';
                } else {
                    // Handle main image
                    $main_image = null;
                    if (!empty($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['main_image']['tmp_name'];
                        if (file_exists($tmp_name)) {
                            $image_data = file_get_contents($tmp_name);
                            $image_type = $_FILES['main_image']['type'];
                            $main_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                            
                        }
                    } else {
                        if (isset($_FILES['main_image'])) {
                            $error_code = $_FILES['main_image']['error'];
                            $error_messages = [
                                UPLOAD_ERR_INI_SIZE => 'ファイルサイズが大きすぎます（最大10MB）',
                                UPLOAD_ERR_FORM_SIZE => 'ファイルサイズが大きすぎます',
                                UPLOAD_ERR_PARTIAL => 'ファイルが部分的にしかアップロードされませんでした',
                                UPLOAD_ERR_NO_FILE => 'ファイルが選択されていません',
                                UPLOAD_ERR_NO_TMP_DIR => 'テンポラリディレクトリがありません',
                                UPLOAD_ERR_CANT_WRITE => 'ファイルの書き込みに失敗しました',
                                UPLOAD_ERR_EXTENSION => 'PHPの拡張機能によってアップロードが停止されました'
                            ];
                            
                            $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown error';
                            
                            echo '<div class="notice notice-error" style="padding: 15px; background: #f8d7da; border-left: 4px solid #dc3545;"><p><strong>🚫 メイン画像のアップロードエラー:</strong> ' . esc_html($error_message) . ' (エラーコード: ' . $error_code . ')</p></div>';
                            
                            if ($error_code === UPLOAD_ERR_INI_SIZE) {
                            }
                        }
                    }

                    // Handle gallery images (ギャラリー画像)
                    $main_gallery_images = [];
                    
                    // Check for actual gallery files (handled below)
                    if (!empty($_FILES['gallery_images_flat'])) {
                        $gallery_flat_files = $_FILES['gallery_images_flat'];
                        
                        // Check if it's a single file or multiple files
                        if (is_array($gallery_flat_files['name'])) {
                            // Multiple files - check for actual files (not empty file names)
                            $has_actual_files = false;
                            for ($i = 0; $i < count($gallery_flat_files['name']); $i++) {
                                
                                // Skip empty file slots (UPLOAD_ERR_NO_FILE = 4)
                                if ($gallery_flat_files['error'][$i] === UPLOAD_ERR_NO_FILE || empty($gallery_flat_files['name'][$i])) {
                                    continue;
                                }
                                
                                $has_actual_files = true;
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
                            // Single file - check if it's actually selected
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
                    
                    // User-friendly message about gallery images
                    if (count($main_gallery_images) === 0 && isset($_FILES['gallery_images_flat'])) {
                        echo '<div class="notice notice-warning" style="padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;"><p><strong>📸 ギャラリー画像について:</strong> ギャラリー画像が選択されていません。複数の画像を表示したい場合は、ファイル選択ボタンをクリックして画像を選択してください。</p></div>';
                    }

                    // Create the API data
                    $api_data = [
                        'name' => $name,
                        'address' => $address,
                        'phone' => $phone,
                        'nearest_station' => $nearest_station,
                        'business_hours' => $business_hours,
                        'holidays' => $holidays,
                        'map_url' => $map_url,
                        'company_email' => $company_email,
                        'gallery_images' => $main_gallery_images
                    ];

                    // Add main image to API data if provided
                    if ($main_image) {
                        $api_data['main_image'] = $main_image;
                    }

                    if ($is_update_mode) {
                        $api_data['shop_id'] = $shop_id;
                    }

                    $api_endpoint = $is_update_mode ? 'update_shop_details.php' : 'studio_shop.php';
                    
                    // Debug API data
                    
                    // Make internal API call
                    $response_body = make_internal_api_call($api_endpoint, $api_data);
                    $response = array('body' => json_encode($response_body));
                    
                    // Debug API response (cleaned)
                    
                    if (is_wp_error($response)) {
                        $error_message = $response->get_error_message();
                        echo '<div class="error"><p>' . esc_html__('API request failed: ' . $error_message, 'studio-shops') . '</p></div>';
                    } else {
                        $response_body = json_decode(wp_remote_retrieve_body($response), true);
                        if (isset($response_body['success']) && $response_body['success']) {
                            $shop_id = $is_update_mode ? $shop_id : ($response_body['shop_id'] ?? '');
                            
                            if ($shop_id) {
                                echo '<div class="updated"><p>' . esc_html__($is_update_mode ? 'Shop updated successfully!' : 'Shop created successfully!', 'studio-shops') . '</p></div>';
                                
                                // 更新モードの場合、現在の状態を維持
                                if ($is_update_mode) {
                                    // Script removed to prevent JavaScript conflicts
                                
                                // Add JavaScript to refresh shop list after successful creation/update
                                }
                                
                                // Add JavaScript to refresh shop list after successful creation/update
                                if (!$is_update_mode && $shop_id) {
                                    echo '<div class="notice notice-info" style="padding: 15px; background: #e8f4f8; border-left: 4px solid #0073aa; margin: 20px 0;">
                                        <p style="font-size: 16px; margin: 0;">
                                            <strong>✅ 店舗が正常に登録されました！ (ID: ' . $shop_id . ')</strong><br>
                                            📸 ギャラリー画像の管理ができるようになりました。
                                        </p>
                                    </div>';
                                    
                                    // フォームを更新モードに自動的に切り替え
                                    // Script removed to prevent JavaScript conflicts
                                    
                                    // 登録されたデータを確認表示
                                    echo '<div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border: 1px solid #0073aa; border-radius: 5px;">
                                        <h3 style="margin-top: 0;">📋 登録確認</h3>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="font-weight: bold; width: 150px; padding: 5px;">店舗名:</td><td style="padding: 5px;">' . esc_html($name) . '</td></tr>
                                            <tr><td style="font-weight: bold; padding: 5px;">住所:</td><td style="padding: 5px;">' . esc_html($address) . '</td></tr>
                                            <tr><td style="font-weight: bold; padding: 5px;">電話番号:</td><td style="padding: 5px;">' . esc_html($phone) . '</td></tr>
                                            <tr><td style="font-weight: bold; padding: 5px;">最寄り駅:</td><td style="padding: 5px;">' . esc_html($nearest_station) . '</td></tr>
                                            <tr><td style="font-weight: bold; padding: 5px;">メールアドレス:</td><td style="padding: 5px;">' . esc_html($company_email) . '</td></tr>
                                            <tr><td style="font-weight: bold; padding: 5px;">営業時間:</td><td style="padding: 5px;">' . esc_html($business_hours) . '</td></tr>
                                            <tr><td style="font-weight: bold; padding: 5px;">定休日:</td><td style="padding: 5px;">' . esc_html($holidays) . '</td></tr>
                                            <tr><td style="font-weight: bold; padding: 5px;">メイン画像:</td><td style="padding: 5px;">' . ($main_image ? '✅ アップロード済み (' . number_format(strlen($main_image)) . ' 文字)' : '❌ なし') . '</td></tr>
                                            <tr><td style="font-weight: bold; padding: 5px;">ギャラリー画像:</td><td style="padding: 5px;">' . count($main_gallery_images) . ' 枚</td></tr>
                                        </table>
                                    </div>';
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

            <!-- Main Image Section -->
            <div style="margin: 30px 0; padding: 25px; border: 2px solid #666; border-radius: 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 20px; font-weight: 600;">🏪 メイン画像（店舗外観・代表画像）</h3>
                <p style="color: #666; margin-bottom: 20px;">店舗のメイン画像をアップロードできます。search-formのカードやstudio-detailのヒーロー画像に使用されます。</p>
                
                <div>
                    <label for="main_image" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">メイン画像</label>
                    <input type="file" id="main_image" name="main_image" accept="image/*"
                           style="width: 100%; padding: 10px; border: 2px solid #0073aa; border-radius: 6px; background: white;">
                    <small style="display: block; margin-top: 5px; color: #666;">JPG、PNG、GIF形式の画像ファイルを選択してください（1枚のみ、最大10MB）</small>
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
                    <small style="display: block; margin-top: 5px; color: #666;">JPG、PNG、GIF形式の画像ファイルを選択してください（複数選択可能、各ファイル最大10MB）</small>
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
        // Global functions
        function deleteGalleryImage(imageId) {
            const shopId = document.getElementById('shop-id-select').value;
            if (!shopId) {
                alert('ショップが選択されていません。');
                return;
            }
            
            if (!confirm('この画像を削除しますか？')) {
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
                    const imageElement = document.querySelector('[data-image-id="' + imageId + '"]');
                    if (imageElement) {
                        imageElement.remove();
                    }
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
        
        function loadShopList() {
            const currentShopSelect = document.getElementById('shop-id-select');
            if (!currentShopSelect) {
                return Promise.resolve([]);
            }
            
            return fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=studio_shop_internal_api&endpoint=get_all_studio_shop.php'
            })
            .then(response => response.text())
            .then(responseText => {
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    return [];
                }
                
                if (data.success && data.shops) {
                    currentShopSelect.innerHTML = '<option value="">Select a Shop</option>';
                    data.shops.forEach(shop => {
                        const option = document.createElement('option');
                        option.value = shop.id;
                        option.textContent = shop.name;
                        currentShopSelect.appendChild(option);
                    });
                    return data.shops;
                } else {
                    return [];
                }
            });
        }

        function loadShopData(shopId) {
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=studio_shop_internal_api&endpoint=get_all_studio_shop.php'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.shops) {
                    const shop = data.shops.find(s => s.id == shopId);
                    if (shop) {
                        // 基本情報をフォームに設定
                        document.getElementById('name').value = shop.name || '';
                        document.getElementById('address').value = shop.address || '';
                        document.getElementById('phone').value = shop.phone || '';
                        document.getElementById('nearest_station').value = shop.nearest_station || '';
                        document.getElementById('business_hours').value = shop.business_hours || '';
                        document.getElementById('holidays').value = shop.holidays || '';
                        document.getElementById('map_url').value = shop.map_url || '';
                        document.getElementById('company_email').value = shop.company_email || '';
                        
                        // メイン画像のプレビューを表示
                        const mainImagePreview = document.getElementById('main-image-preview');
                        if (shop.main_image) {
                            mainImagePreview.innerHTML = '<div style="margin-top: 10px; padding: 10px; background: #f0f8ff; border: 2px solid #0073aa; border-radius: 8px;"><p style="margin: 0 0 10px 0; font-weight: bold; color: #0073aa;">📸 現在のメイン画像:</p><img src="' + shop.main_image + '" alt="現在のメイン画像" style="width: 200px; height: 150px; object-fit: cover; border-radius: 6px; border: 3px solid #0073aa;"><p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">新しい画像をアップロードすると、この画像は置き換えられます。</p></div>';
                        } else {
                            mainImagePreview.innerHTML = '<p style="color: #666; font-style: italic;">メイン画像は設定されていません。</p>';
                        }
                        
                        // ギャラリー画像のプレビューを表示
                        const galleryPreview = document.getElementById('gallery-preview');
                        if (shop.main_gallery_images && shop.main_gallery_images.length > 0) {
                            let galleryHtml = '<div style="margin-bottom: 15px; padding: 15px; background: #f8f8f8; border: 2px solid #666; border-radius: 8px;"><p style="margin: 0 0 15px 0; font-weight: bold; color: #333; font-size: 16px;">📸 現在のギャラリー画像 (' + shop.main_gallery_images.length + '枚):</p></div>';
                            
                            shop.main_gallery_images.forEach((image, index) => {
                                // image might be a base64 string or object with different property names
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

        document.addEventListener('DOMContentLoaded', function() {
            // Update mode functionality
            document.addEventListener('change', function(e) {
                if (e.target && e.target.id === 'update-mode') {
                    const shopSelector = document.getElementById('shop-selector');
                    const submitBtn = document.getElementById('submit_shop');
                    
                    if (e.target.checked) {
                        if (shopSelector) shopSelector.style.display = 'block';
                        if (submitBtn) submitBtn.value = '🔄 ショップを更新';
                        loadShopList();
                    } else {
                        if (shopSelector) shopSelector.style.display = 'none';
                        if (submitBtn) submitBtn.value = '✨ ショップを登録';
                    }
                }
                
                if (e.target && e.target.id === 'shop-id-select') {
                    const deleteShopBtn = document.getElementById('delete-shop-btn');
                    
                    if (e.target.value) {
                        if (deleteShopBtn) deleteShopBtn.style.display = 'inline-block';
                        loadShopData(e.target.value);
                    } else {
                        if (deleteShopBtn) deleteShopBtn.style.display = 'none';
                        // Clear form
                        document.getElementById('name').value = '';
                        document.getElementById('address').value = '';
                        document.getElementById('phone').value = '';
                        document.getElementById('nearest_station').value = '';
                        document.getElementById('business_hours').value = '';
                        document.getElementById('holidays').value = '';
                        document.getElementById('map_url').value = '';
                        document.getElementById('company_email').value = '';
                    }
                }
            });
        });
        </script>
        
    </div>
    <?php
}
?>