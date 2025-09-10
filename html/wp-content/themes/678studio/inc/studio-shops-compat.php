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
    $cache_duration = 300; // 5分キャッシュ
    
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
        
        // スタッフ情報を取得
        $staff_members = get_field('staff_members', $post->ID);
        $staff_data = array();
        if ($staff_members && is_array($staff_members)) {
            foreach ($staff_members as $staff) {
                $staff_entry = array(
                    'name' => $staff['name'] ?? '',
                    'position' => $staff['position'] ?? '',
                    'message' => $staff['message'] ?? '',
                );
                
                if (!empty($staff['image'])) {
                    $staff_image_url = is_array($staff['image']) 
                        ? $staff['image']['url'] 
                        : wp_get_attachment_url($staff['image']);
                    $staff_entry['image'] = $staff_image_url;
                }
                
                $staff_data[] = $staff_entry;
            }
        }
        
        // 既存フォーマットに変換
        $shop_data = array(
            'id' => $shop_id,
            'name' => $post->post_title,
            'address' => get_field('address', $post->ID) ?: '',
            'phone' => get_field('phone', $post->ID) ?: '',
            'nearest_station' => get_field('nearest_station', $post->ID) ?: '',
            'business_hours' => get_field('business_hours', $post->ID) ?: '',
            'holidays' => get_field('holidays', $post->ID) ?: '',
            'map_url' => get_field('map_url', $post->ID) ?: '',
            'company_email' => get_field('company_email', $post->ID) ?: '',
            'prefecture' => get_field('prefecture', $post->ID) ?: '',
            'main_image' => $main_image_url,
            'main_gallery_images' => $gallery_urls,
            'staff' => $staff_data,
            'image_urls' => array($main_image_url), // 後方互換性
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
    
    // ギャラリー画像
    $gallery_images = get_field('gallery_images', $post->ID);
    $gallery_urls = array();
    if ($gallery_images && is_array($gallery_images)) {
        foreach ($gallery_images as $image) {
            if (is_array($image)) {
                $gallery_urls[] = array('url' => $image['url']);
            } else {
                $gallery_urls[] = array('url' => wp_get_attachment_url($image));
            }
        }
    }
    
    // スタッフ情報
    $staff_members = get_field('staff_members', $post->ID);
    $staff_data = array();
    if ($staff_members && is_array($staff_members)) {
        foreach ($staff_members as $staff) {
            $staff_entry = array(
                'name' => $staff['name'] ?? '',
                'position' => $staff['position'] ?? '',
                'message' => $staff['message'] ?? '',
            );
            
            if (!empty($staff['image'])) {
                $staff_image_url = is_array($staff['image']) 
                    ? $staff['image']['url'] 
                    : wp_get_attachment_url($staff['image']);
                $staff_entry['image'] = $staff_image_url;
            }
            
            $staff_data[] = $staff_entry;
        }
    }
    
    $shop_data = array(
        'id' => get_field('shop_id', $post->ID) ?: $post->ID,
        'name' => $post->post_title,
        'address' => get_field('address', $post->ID) ?: '',
        'phone' => get_field('phone', $post->ID) ?: '',
        'nearest_station' => get_field('nearest_station', $post->ID) ?: '',
        'business_hours' => get_field('business_hours', $post->ID) ?: '',
        'holidays' => get_field('holidays', $post->ID) ?: '',
        'map_url' => get_field('map_url', $post->ID) ?: '',
        'company_email' => get_field('company_email', $post->ID) ?: '',
        'prefecture' => get_field('prefecture', $post->ID) ?: '',
        'main_image' => $main_image_url,
        'main_gallery_images' => $gallery_urls,
        'staff' => $staff_data,
        'image_urls' => array($main_image_url),
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
}

// 既存の関数を上書き（ACF対応版を使用）
add_action('init', function() {
    // ACFベースの投稿が存在する場合のみ関数を上書き
    $acf_posts = get_posts(array(
        'post_type' => 'studio_shops',
        'posts_per_page' => 1,
        'post_status' => 'publish',
    ));
    
    if (!empty($acf_posts)) {
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