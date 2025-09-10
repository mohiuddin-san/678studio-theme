<?php
/**
 * Studio Shops Compatibility Layer
 * 既存の関数をACFベースのデータに対応させる互換性レイヤー
 * 
 * @package 678studio
 */

/**
 * ACFベースのスタジオデータを既存フォーマットに変換
 * 既存のテンプレートとの互換性を保つ
 */
function get_cached_studio_data_acf() {
    // キャッシュ確認
    $cache_key = 'studio_shops_data_acf';
    $cache_duration = 60; // 1分キャッシュ（管理画面での変更を素早く反映）
    
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // ACFから店舗データを取得
    $args = array(
        'post_type' => 'studio_shops',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    );
    
    $posts = get_posts($args);
    $shops = array();
    
    foreach ($posts as $post) {
        $shop_id = get_field('shop_id', $post->ID) ?: $post->ID;
        
        // メイン画像を取得
        $main_image = get_field('main_image', $post->ID);
        $main_image_url = '';
        if ($main_image) {
            $main_image_url = is_array($main_image) ? $main_image['url'] : wp_get_attachment_url($main_image);
        } elseif (has_post_thumbnail($post->ID)) {
            $main_image_url = get_the_post_thumbnail_url($post->ID, 'full');
        }
        
        // ギャラリー画像を取得（WordPress標準メタフィールド対応）
        $gallery_image_ids = get_post_meta($post->ID, '_gallery_image_ids', true);
        $gallery_urls = array();
        
        if (!empty($gallery_image_ids)) {
            $image_ids = explode(',', $gallery_image_ids);
            foreach ($image_ids as $image_id) {
                $image_id = trim($image_id);
                if ($image_id && is_numeric($image_id)) {
                    $image_url = wp_get_attachment_url($image_id);
                    if ($image_url) {
                        $gallery_urls[] = array('url' => $image_url);
                    }
                }
            }
        }
        
        // ACFのギャラリーフィールドとの後方互換性も保持
        if (empty($gallery_urls)) {
            $gallery_images = get_field('gallery_images', $post->ID);
            if ($gallery_images && is_array($gallery_images)) {
                foreach ($gallery_images as $image) {
                    if (is_array($image)) {
                        $gallery_urls[] = array('url' => $image['url']);
                    } else {
                        $gallery_urls[] = array('url' => wp_get_attachment_url($image));
                    }
                }
            }
        }
        
        // スタッフ情報を取得（ACF無料版対応 - 個別フィールド）
        $staff_data = array();
        for ($i = 1; $i <= 2; $i++) {
            $staff_name = get_field("staff{$i}_name", $post->ID);
            $staff_position = get_field("staff{$i}_position", $post->ID);
            $staff_message = get_field("staff{$i}_message", $post->ID);
            $staff_image = get_field("staff{$i}_image", $post->ID);
            
            if (!empty($staff_name)) {
                $staff_entry = array(
                    'name' => $staff_name,
                    'position' => $staff_position ?: '',
                    'message' => $staff_message ?: '',
                );
                
                if (!empty($staff_image)) {
                    $staff_image_url = is_array($staff_image) 
                        ? $staff_image['url'] 
                        : wp_get_attachment_url($staff_image);
                    $staff_entry['image'] = $staff_image_url;
                }
                
                $staff_data[] = $staff_entry;
            }
        }
        
        // 既存フォーマットに変換
        $address = get_field('address', $post->ID) ?: '';
        // BRタグを改行に変換してから空白に変換
        $address = str_replace(array('<br>', '<br/>', '<br />'), ' ', $address);
        // 余分な空白を削除
        $address = trim(preg_replace('/\s+/', ' ', $address));
        
        // 認定店情報を取得
        $is_certified = get_field('is_certified_store', $post->ID) ?: false;
        $photo_plans = array();
        
        // 店舗紹介文を取得（全店舗共通）
        $store_introduction = get_field('store_introduction', $post->ID) ?: '';
        
        if ($is_certified) {
            // 撮影プランを取得（ACF無料版対応 - 個別フィールド）
            for ($i = 1; $i <= 3; $i++) {
                $plan_name = get_field("plan{$i}_name", $post->ID);
                $plan_price = get_field("plan{$i}_price", $post->ID);
                $plan_duration = get_field("plan{$i}_duration", $post->ID);
                $plan_description = get_field("plan{$i}_description", $post->ID);
                
                if (!empty($plan_name) || !empty($plan_price)) {
                    // 目安時間をフォーマット
                    $formatted_duration = '';
                    if (!empty($plan_duration) && is_numeric($plan_duration)) {
                        $formatted_duration = $plan_duration . '分';
                    }
                    
                    $photo_plans[] = array(
                        'plan_name' => $plan_name ?: '',
                        'plan_price' => $plan_price ?: 0,
                        'plan_duration' => $plan_duration ?: '',
                        'plan_description' => $plan_description ?: '',
                        'formatted_price' => '¥' . number_format($plan_price ?: 0) . '円',
                        'formatted_duration' => $formatted_duration,
                    );
                }
            }
        }
        
        // 認定店でない場合はギャラリー画像を表示しない
        $final_gallery_urls = $is_certified ? $gallery_urls : array();
        
        $shop_data = array(
            'id' => $shop_id,
            'name' => get_field('store_name', $post->ID) ?: $post->post_title,
            'address' => $address,
            'phone' => get_field('phone', $post->ID) ?: '',
            'nearest_station' => get_field('nearest_station', $post->ID) ?: '',
            'business_hours' => get_field('business_hours', $post->ID) ?: '',
            'holidays' => get_field('holidays', $post->ID) ?: '',
            'map_url' => get_field('map_url', $post->ID) ?: '',
            'company_email' => get_field('company_email', $post->ID) ?: '',
            'prefecture' => get_field('prefecture', $post->ID) ?: '',
            'main_image' => $main_image_url,
            'main_gallery_images' => $final_gallery_urls,
            'staff' => $staff_data,
            'image_urls' => array($main_image_url), // 後方互換性
            // 認定店情報
            'is_certified_store' => $is_certified,
            'photo_plans' => $photo_plans,
            'store_introduction' => $store_introduction,
        );
        
        $shops[] = $shop_data;
    }
    
    $data = array(
        'shops' => $shops,
        'total' => count($shops),
    );
    
    // キャッシュに保存
    set_transient($cache_key, $data, $cache_duration);
    
    return $data;
}

/**
 * 既存のget_cached_studio_data関数を上書き
 * ACFベースのデータを優先的に使用
 */
if (!function_exists('get_cached_studio_data_original')) {
    // 元の関数をバックアップ
    if (function_exists('get_cached_studio_data')) {
        function get_cached_studio_data_original() {
            // 元の実装を呼び出す
            $cache_key = 'studio_shops_data';
            $cache_duration = 300;
            
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
            
            if (is_local_environment()) {
                $data = get_studio_data_from_local_api();
            } else {
                $data = get_studio_data_from_server_api();
            }
            
            if (isset($data['error'])) {
                set_transient($cache_key, $data, 60);
            } else {
                set_transient($cache_key, $data, $cache_duration);
            }
            
            return $data;
        }
    }
}

/**
 * get_cached_studio_data関数のラッパー
 * ACFデータが存在する場合はそちらを使用
 */
function get_cached_studio_data_wrapper() {
    // ACFベースの投稿が存在するかチェック
    $acf_posts = get_posts(array(
        'post_type' => 'studio_shops',
        'posts_per_page' => 1,
        'post_status' => 'publish',
    ));
    
    if (!empty($acf_posts)) {
        // ACFデータを使用
        return get_cached_studio_data_acf();
    } else {
        // 既存のプラグインデータを使用
        if (function_exists('get_cached_studio_data_original')) {
            return get_cached_studio_data_original();
        } else {
            // フォールバック
            return array('shops' => array(), 'error' => 'No data source available');
        }
    }
}

/**
 * 個別店舗データ取得（ACF対応版）
 */
function get_studio_shop_data_acf($shop_id) {
    // shop_idで検索
    $args = array(
        'post_type' => 'studio_shops',
        'meta_query' => array(
            array(
                'key' => 'shop_id',
                'value' => $shop_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish',
    );
    
    $posts = get_posts($args);
    
    if (empty($posts)) {
        // Post IDで直接取得を試みる
        $post = get_post($shop_id);
        if (!$post || $post->post_type !== 'studio_shops') {
            return array('shop' => null, 'error' => 'Shop not found');
        }
        $posts = array($post);
    }
    
    $post = $posts[0];
    
    // データを既存フォーマットに変換
    $main_image = get_field('main_image', $post->ID);
    $main_image_url = '';
    if ($main_image) {
        $main_image_url = is_array($main_image) ? $main_image['url'] : wp_get_attachment_url($main_image);
    } elseif (has_post_thumbnail($post->ID)) {
        $main_image_url = get_the_post_thumbnail_url($post->ID, 'full');
    }
    
    // ギャラリー画像（WordPressメタフィールドから取得）
    $gallery_image_ids = get_post_meta($post->ID, '_gallery_image_ids', true);
    $gallery_urls = array();
    
    if (!empty($gallery_image_ids)) {
        $image_ids = explode(',', $gallery_image_ids);
        foreach ($image_ids as $image_id) {
            $image_id = trim($image_id);
            if ($image_id && is_numeric($image_id)) {
                $image_url = wp_get_attachment_url($image_id);
                if ($image_url) {
                    $gallery_urls[] = array('url' => $image_url);
                }
            }
        }
    }
    
    // ACFのギャラリーフィールドとの後方互換性も保持
    if (empty($gallery_urls)) {
        $gallery_images = get_field('gallery_images', $post->ID);
        if ($gallery_images && is_array($gallery_images)) {
            foreach ($gallery_images as $image) {
                if (is_array($image)) {
                    $gallery_urls[] = array('url' => $image['url']);
                } else {
                    $gallery_urls[] = array('url' => wp_get_attachment_url($image));
                }
            }
        }
    }
    
    // スタッフ情報（ACF無料版対応 - 個別フィールド）
    $staff_data = array();
    for ($i = 1; $i <= 2; $i++) {
        $staff_name = get_field("staff{$i}_name", $post->ID);
        $staff_position = get_field("staff{$i}_position", $post->ID);
        $staff_message = get_field("staff{$i}_message", $post->ID);
        $staff_image = get_field("staff{$i}_image", $post->ID);
        
        if (!empty($staff_name)) {
            $staff_entry = array(
                'name' => $staff_name,
                'position' => $staff_position ?: '',
                'message' => $staff_message ?: '',
            );
            
            if (!empty($staff_image)) {
                $staff_image_url = is_array($staff_image) 
                    ? $staff_image['url'] 
                    : wp_get_attachment_url($staff_image);
                $staff_entry['image'] = $staff_image_url;
            }
            
            $staff_data[] = $staff_entry;
        }
    }
    
    $address = get_field('address', $post->ID) ?: '';
    // BRタグを改行に変換してから空白に変換
    $address = str_replace(array('<br>', '<br/>', '<br />'), ' ', $address);
    // 余分な空白を削除
    $address = trim(preg_replace('/\s+/', ' ', $address));
    
    // 認定店情報を取得
    $is_certified = get_field('is_certified_store', $post->ID) ?: false;
    $photo_plans = array();
    
    // 店舗紹介文を取得（全店舗共通）
    $store_introduction = get_field('store_introduction', $post->ID) ?: '';
    
    if ($is_certified) {
        // 撮影プランを取得（ACF無料版対応 - 個別フィールド）
        for ($i = 1; $i <= 3; $i++) {
            $plan_name = get_field("plan{$i}_name", $post->ID);
            $plan_price = get_field("plan{$i}_price", $post->ID);
            $plan_duration = get_field("plan{$i}_duration", $post->ID);
            $plan_description = get_field("plan{$i}_description", $post->ID);
            
            if (!empty($plan_name) || !empty($plan_price)) {
                // 目安時間をフォーマット
                $formatted_duration = '';
                if (!empty($plan_duration) && is_numeric($plan_duration)) {
                    $formatted_duration = $plan_duration . '分';
                }
                
                $photo_plans[] = array(
                    'plan_name' => $plan_name ?: '',
                    'plan_price' => $plan_price ?: 0,
                    'plan_duration' => $plan_duration ?: '',
                    'plan_description' => $plan_description ?: '',
                    'formatted_price' => '¥' . number_format($plan_price ?: 0) . '円',
                    'formatted_duration' => $formatted_duration,
                );
            }
        }
    }
    
    // 認定店でない場合はギャラリー画像を表示しない
    $final_gallery_urls = $is_certified ? $gallery_urls : array();
    
    $shop_data = array(
        'id' => get_field('shop_id', $post->ID) ?: $post->ID,
        'name' => get_field('store_name', $post->ID) ?: $post->post_title,
        'address' => $address,
        'phone' => get_field('phone', $post->ID) ?: '',
        'nearest_station' => get_field('nearest_station', $post->ID) ?: '',
        'business_hours' => get_field('business_hours', $post->ID) ?: '',
        'holidays' => get_field('holidays', $post->ID) ?: '',
        'map_url' => get_field('map_url', $post->ID) ?: '',
        'company_email' => get_field('company_email', $post->ID) ?: '',
        'prefecture' => get_field('prefecture', $post->ID) ?: '',
        'main_image' => $main_image_url,
        'main_gallery_images' => $final_gallery_urls,
        'staff' => $staff_data,
        'image_urls' => array($main_image_url),
        // 認定店情報
        'is_certified_store' => $is_certified,
        'photo_plans' => $photo_plans,
        'store_introduction' => $store_introduction,
    );
    
    return array('shop' => $shop_data, 'error' => null);
}

/**
 * キャッシュクリア関数（ACF対応版）
 */
function clear_studio_data_cache_acf() {
    // ACFベースのキャッシュをクリア
    delete_transient('studio_shops_data_acf');
    
    // 既存のキャッシュもクリア
    delete_transient('studio_shops_data');
    
    // 個別ショップキャッシュもクリア
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_studio_shop_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_studio_shop_%'");
    
    // オブジェクトキャッシュもクリア
    wp_cache_flush();
}

/**
 * Studio Shopsの投稿保存時にキャッシュをクリア
 */
add_action('save_post_studio_shops', function($post_id) {
    // 自動保存時はスキップ
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // 権限チェック
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // キャッシュをクリア
    clear_studio_data_cache_acf();
    
    // デバッグログ（管理者用）
    if (current_user_can('administrator')) {
        error_log('Studio Shops cache cleared after saving post ID: ' . $post_id);
    }
}, 10, 1);

/**
 * Studio Shopsの投稿削除時にもキャッシュをクリア
 */
add_action('delete_post', function($post_id) {
    $post_type = get_post_type($post_id);
    if ($post_type === 'studio_shops') {
        clear_studio_data_cache_acf();
        
        if (current_user_can('administrator')) {
            error_log('Studio Shops cache cleared after deleting post ID: ' . $post_id);
        }
    }
});

/**
 * ACFフィールド更新時にもキャッシュをクリア
 */
add_action('acf/save_post', function($post_id) {
    if (get_post_type($post_id) === 'studio_shops') {
        clear_studio_data_cache_acf();
        
        if (current_user_can('administrator')) {
            error_log('Studio Shops cache cleared after ACF field update for post ID: ' . $post_id);
        }
    }
}, 20);

// 既存の関数を上書き（ACF対応版を使用）
add_action('init', function() {
    // ACFベースの投稿が存在する場合のみ関数を上書き
    $acf_posts = get_posts(array(
        'post_type' => 'studio_shops',
        'posts_per_page' => 1,
        'post_status' => 'publish',
    ));
    
    if (!empty($acf_posts)) {
        // get_cached_studio_data関数を上書き
        if (!function_exists('get_cached_studio_data_original_backup')) {
            function get_cached_studio_data_original_backup() {
                // Original function backup - in case we need it
                return array('shops' => array(), 'error' => 'Original function backed up');
            }
        }
        
        // 既存の関数を削除して新しい関数を追加
        remove_action('wp_ajax_get_gallery_studios', 'ajax_get_gallery_studios');
        remove_action('wp_ajax_nopriv_get_gallery_studios', 'ajax_get_gallery_studios');
        
        // ACF対応版のAJAXハンドラー
        add_action('wp_ajax_get_gallery_studios', function() {
            if (!wp_verify_nonce($_POST['nonce'], 'gallery_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce']);
                return;
            }
            
            $studio_data = get_cached_studio_data_acf();
            
            if (isset($studio_data['error'])) {
                wp_send_json_error(['message' => $studio_data['error']]);
            } else {
                wp_send_json_success($studio_data);
            }
        });
        
        add_action('wp_ajax_nopriv_get_gallery_studios', function() {
            if (!wp_verify_nonce($_POST['nonce'], 'gallery_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce']);
                return;
            }
            
            $studio_data = get_cached_studio_data_acf();
            
            if (isset($studio_data['error'])) {
                wp_send_json_error(['message' => $studio_data['error']]);
            } else {
                wp_send_json_success($studio_data);
            }
        });
    }
}, 20);

// メイン関数をフィルターで上書き
add_filter('pre_transient_studio_shops_data', function($pre_transient, $transient) {
    // ACF投稿が存在する場合、ACFデータを返す
    $acf_posts = get_posts(array(
        'post_type' => 'studio_shops',
        'posts_per_page' => 1,
        'post_status' => 'publish',
    ));
    
    if (!empty($acf_posts)) {
        // ACFキャッシュを確認（短めのキャッシュ時間）
        $acf_cache = get_transient('studio_shops_data_acf');
        if ($acf_cache !== false) {
            return $acf_cache;
        }
        
        // ACFデータを生成してキャッシュに保存
        $acf_data = get_cached_studio_data_acf();
        if (!isset($acf_data['error'])) {
            // 正常なデータの場合は短いキャッシュ時間（1分）を設定
            set_transient('studio_shops_data_acf', $acf_data, 60);
        }
        
        return $acf_data;
    }
    
    return $pre_transient;
}, 10, 2);

// 管理者向けのキャッシュクリア用URLパラメータ
add_action('wp', function() {
    if (isset($_GET['clear_studio_cache']) && current_user_can('administrator')) {
        clear_studio_data_cache_acf();
        
        // リダイレクトしてURLをクリーンに
        $clean_url = remove_query_arg('clear_studio_cache');
        wp_redirect($clean_url);
        exit;
    }
});

/**
 * 店舗名フィールドが更新されたときに投稿タイトルも自動更新
 */
add_action('acf/save_post', function($post_id) {
    // studio_shops投稿タイプのみ処理
    if (get_post_type($post_id) !== 'studio_shops') {
        return;
    }
    
    // 無限ループを防ぐため、一度だけ実行
    if (did_action('acf/save_post') > 1) {
        return;
    }
    
    // 自動保存時はスキップ
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // 権限チェック
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // 店舗名フィールドを取得
    $store_name = get_field('store_name', $post_id);
    
    if (!empty($store_name)) {
        // 現在の投稿タイトルと比較
        $current_post = get_post($post_id);
        
        if ($current_post && $current_post->post_title !== $store_name) {
            // フックを一時的に除去して無限ループを防ぐ
            remove_action('acf/save_post', __FUNCTION__);
            
            // 投稿タイトルを更新
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $store_name,
            ));
            
            // フックを再度追加
            add_action('acf/save_post', __FUNCTION__, 15);
            
            // デバッグログ（管理者用）
            if (current_user_can('administrator')) {
                error_log("Studio Shop post title updated: {$post_id} -> {$store_name}");
            }
        }
    }
}, 15);

/**
 * 既存の投稿の投稿タイトルをstore_nameと同期させる管理関数
 * WordPress管理画面でのみ実行される
 */
add_action('admin_init', function() {
    // URLパラメータで同期処理を実行
    if (isset($_GET['sync_studio_titles']) && current_user_can('administrator')) {
        $posts = get_posts(array(
            'post_type' => 'studio_shops',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        $updated_count = 0;
        
        foreach ($posts as $post) {
            $store_name = get_field('store_name', $post->ID);
            
            if (!empty($store_name) && $post->post_title !== $store_name) {
                wp_update_post(array(
                    'ID' => $post->ID,
                    'post_title' => $store_name,
                ));
                $updated_count++;
            } elseif (empty($store_name) && !empty($post->post_title)) {
                // 逆方向の同期：投稿タイトルがあってstore_nameが空の場合
                update_field('store_name', $post->post_title, $post->ID);
                $updated_count++;
            }
        }
        
        // 結果をセッションに保存してリダイレクト後に表示
        set_transient('studio_sync_result_' . get_current_user_id(), $updated_count, 30);
        
        wp_redirect(admin_url('edit.php?post_type=studio_shops&synced=1'));
        exit;
    }
    
    // 同期結果の表示
    if (isset($_GET['synced']) && current_user_can('administrator')) {
        $result = get_transient('studio_sync_result_' . get_current_user_id());
        if ($result !== false) {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>店舗タイトルの同期が完了しました。更新件数: ' . $result . '件</p>';
                echo '</div>';
            });
            delete_transient('studio_sync_result_' . get_current_user_id());
        }
    }
});

/**
 * 管理画面に同期ボタンを追加
 */
add_action('manage_studio_shops_posts_custom_column', function($column, $post_id) {
    if ($column === 'title') {
        $store_name = get_field('store_name', $post_id);
        $post_title = get_the_title($post_id);
        
        if (!empty($store_name) && $store_name !== $post_title) {
            echo '<br><small style="color: red;">⚠️ タイトル不一致: 店舗名「' . esc_html($store_name) . '」</small>';
        }
    }
}, 10, 2);

/**
 * 管理画面にバルク同期ボタンを追加
 */
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'studio_shops' && $screen->base === 'edit') {
        echo '<script>
        jQuery(document).ready(function($) {
            $(".tablenav.top .alignleft.actions.bulkactions").after(
                "<a href=\"" + window.location.href + "&sync_studio_titles=1\" " +
                "class=\"button\" onclick=\"return confirm(\'店舗名とタイトルを同期しますか？\')\">タイトル同期</a>"
            );
        });
        </script>';
    }
});