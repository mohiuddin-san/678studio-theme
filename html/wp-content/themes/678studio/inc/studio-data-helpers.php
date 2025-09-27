<?php
/**
 * Studio Data Helpers - Simplified Data Access Layer
 *
 * このファイルは複雑なデータフローを単純化し、
 * 直接的なACFアクセスを提供します。
 *
 * 設計方針:
 * - 直接ACFフィールドにアクセス
 * - シンプルな静的キャッシュのみ
 * - 既存システムと完全に独立
 * - 段階的移行をサポート
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 静的キャッシュ（リクエスト内でのみ有効）
 */
class StudioDataCache {
    private static $cache = array();

    public static function get($key) {
        return isset(self::$cache[$key]) ? self::$cache[$key] : null;
    }

    public static function set($key, $value) {
        self::$cache[$key] = $value;
    }

    public static function has($key) {
        return isset(self::$cache[$key]);
    }

    public static function clear() {
        self::$cache = array();
    }
}

/**
 * 店舗IDからWordPress Post IDを取得
 *
 * @param int $shop_id 店舗ID
 * @return int|false WordPress Post ID、見つからない場合はfalse
 */
function get_studio_post_id_by_shop_id($shop_id) {
    $cache_key = "post_id_for_shop_{$shop_id}";

    if (StudioDataCache::has($cache_key)) {
        return StudioDataCache::get($cache_key);
    }

    // shop_idフィールドで検索
    $posts = get_posts(array(
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
        'fields' => 'ids'
    ));

    if (!empty($posts)) {
        StudioDataCache::set($cache_key, $posts[0]);
        return $posts[0];
    }

    // Post IDで直接取得を試みる（フォールバック）
    $post = get_post($shop_id);
    if ($post && $post->post_type === 'studio_shops') {
        StudioDataCache::set($cache_key, $shop_id);
        return $shop_id;
    }

    StudioDataCache::set($cache_key, false);
    return false;
}

/**
 * 店舗の特定フィールドを取得
 *
 * @param int $shop_id 店舗ID
 * @param string $field_name ACFフィールド名
 * @return mixed フィールドの値（取得できない場合は空文字）
 */
function get_studio_shop_field($shop_id, $field_name) {
    $cache_key = "shop_{$shop_id}_field_{$field_name}";

    if (StudioDataCache::has($cache_key)) {
        return StudioDataCache::get($cache_key);
    }

    $post_id = get_studio_post_id_by_shop_id($shop_id);
    if (!$post_id) {
        StudioDataCache::set($cache_key, '');
        return '';
    }

    $value = get_field($field_name, $post_id);
    $result = $value ? $value : '';

    StudioDataCache::set($cache_key, $result);
    return $result;
}

/**
 * 店舗基本情報の一括取得
 *
 * @param int $shop_id 店舗ID
 * @return array 店舗基本情報
 */
function get_studio_shop_basic_info($shop_id) {
    $cache_key = "shop_{$shop_id}_basic_info";

    if (StudioDataCache::has($cache_key)) {
        return StudioDataCache::get($cache_key);
    }

    $post_id = get_studio_post_id_by_shop_id($shop_id);
    if (!$post_id) {
        $empty_result = array(
            'id' => $shop_id,
            'name' => '',
            'address' => '',
            'phone' => '',
            'nearest_station' => '',
            'business_hours' => '',
            'holidays' => '',
            'website_url' => '',
            'store_introduction' => '',
            'is_certified_store' => false,
        );
        StudioDataCache::set($cache_key, $empty_result);
        return $empty_result;
    }

    // 基本フィールドを一括取得
    $fields = get_fields($post_id);

    // 住所の整形（BRタグを空白に変換）
    $address = isset($fields['address']) ? $fields['address'] : '';
    $address = str_replace(array('<br>', '<br/>', '<br />'), ' ', $address);
    $address = trim(preg_replace('/\s+/', ' ', $address));

    // IDフィールドの確定
    $final_id = isset($fields['shop_id']) && !empty($fields['shop_id']) ? $fields['shop_id'] : $shop_id;

    $basic_info = array(
        'id' => $final_id,
        'name' => isset($fields['store_name']) ? $fields['store_name'] : get_the_title($post_id),
        'address' => $address,
        'phone' => isset($fields['phone']) ? $fields['phone'] : '',
        'nearest_station' => isset($fields['nearest_station']) ? $fields['nearest_station'] : '',
        'business_hours' => isset($fields['business_hours']) ? $fields['business_hours'] : '',
        'holidays' => isset($fields['holidays']) ? $fields['holidays'] : '',
        'website_url' => isset($fields['website_url']) ? $fields['website_url'] : '',
        'store_introduction' => isset($fields['store_introduction']) ? $fields['store_introduction'] : '',
        'is_certified_store' => isset($fields['is_certified_store']) ? (bool)$fields['is_certified_store'] : false,
    );

    StudioDataCache::set($cache_key, $basic_info);
    return $basic_info;
}

/**
 * 店舗データの簡単取得（軽量版）
 *
 * 既存のstudio-shops-compat.phpと同等の情報を提供しますが、
 * より直接的でシンプルなアプローチです。
 *
 * @param int $shop_id 店舗ID
 * @return array 店舗データ（errorキーを含む可能性あり）
 */
function get_studio_shop_data_simple($shop_id) {
    $cache_key = "shop_{$shop_id}_data_simple";

    if (StudioDataCache::has($cache_key)) {
        return StudioDataCache::get($cache_key);
    }

    $post_id = get_studio_post_id_by_shop_id($shop_id);
    if (!$post_id) {
        $error_result = array(
            'shop' => null,
            'error' => 'Shop not found'
        );
        StudioDataCache::set($cache_key, $error_result);
        return $error_result;
    }

    // 基本情報を取得
    $basic_info = get_studio_shop_basic_info($shop_id);

    // メイン画像の取得
    $main_image_url = '';
    $main_image = get_field('main_image', $post_id);
    if ($main_image) {
        $main_image_url = is_array($main_image) ? $main_image['url'] : wp_get_attachment_url($main_image);
    } elseif (has_post_thumbnail($post_id)) {
        $main_image_url = get_the_post_thumbnail_url($post_id, 'full');
    }

    // 認定店の場合のみギャラリー画像を取得
    $gallery_urls = array();
    if ($basic_info['is_certified_store']) {
        // WordPressメタフィールドから取得
        $gallery_image_ids = get_post_meta($post_id, '_gallery_image_ids', true);

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

        // ACFギャラリーフィールドとの後方互換性
        if (empty($gallery_urls)) {
            $gallery_images = get_field('gallery_images', $post_id);
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
    }

    // 撮影プランの取得（認定店のみ）
    $photo_plans = array();
    if ($basic_info['is_certified_store']) {
        for ($i = 1; $i <= 3; $i++) {
            $plan_name = get_field("plan{$i}_name", $post_id);
            $plan_price = get_field("plan{$i}_price", $post_id);
            $plan_duration = get_field("plan{$i}_duration", $post_id);
            $plan_description = get_field("plan{$i}_description", $post_id);

            if (!empty($plan_name) || !empty($plan_price)) {
                $formatted_duration = '';
                if (!empty($plan_duration) && is_numeric($plan_duration)) {
                    $formatted_duration = $plan_duration . '分';
                }

                // プラン画像の取得
                $plan_image = get_field("plan{$i}_image", $post_id);
                $plan_image_url = '';
                if ($plan_image) {
                    $plan_image_url = is_array($plan_image) ? $plan_image['url'] : wp_get_attachment_url($plan_image);
                }

                $photo_plans[] = array(
                    'plan_name' => $plan_name ?: '',
                    'plan_price' => $plan_price ?: 0,
                    'plan_duration' => $plan_duration ?: '',
                    'plan_description' => $plan_description ?: '',
                    'plan_image' => $plan_image_url,
                    'formatted_price' => '¥' . number_format($plan_price ?: 0),
                    'formatted_duration' => $formatted_duration,
                );
            }
        }
    }

    // スタッフ情報の取得
    $staff_data = array();
    for ($i = 1; $i <= 2; $i++) {
        $staff_name = get_field("staff{$i}_name", $post_id);
        $staff_position = get_field("staff{$i}_position", $post_id);
        $staff_message = get_field("staff{$i}_message", $post_id);
        $staff_image = get_field("staff{$i}_image", $post_id);

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

    // 最終的な店舗データを構築
    $shop_data = array_merge($basic_info, array(
        'map_url' => get_field('map_url', $post_id) ?: '',
        'company_email' => get_field('company_email', $post_id) ?: '',
        'prefecture' => get_field('prefecture', $post_id) ?: '',
        'access_details' => get_field('access_details', $post_id) ?: '',
        'main_image' => $main_image_url,
        'main_gallery_images' => $gallery_urls,
        'staff' => $staff_data,
        'image_urls' => array($main_image_url),
        'photo_plans' => $photo_plans,
    ));

    $result = array(
        'shop' => $shop_data,
        'error' => null
    );

    StudioDataCache::set($cache_key, $result);
    return $result;
}

/**
 * 全店舗データの取得（検索・一覧表示用）
 *
 * 既存のget_cached_studio_data()の代替機能
 * 全店舗の基本情報を配列で返します
 *
 * @return array 全店舗データ（errorキーを含む可能性あり）
 */
function get_all_studio_shops_data() {
    $cache_key = 'all_studio_shops_data';

    if (StudioDataCache::has($cache_key)) {
        return StudioDataCache::get($cache_key);
    }

    // studio_shopsポストタイプの全投稿を取得
    $posts = get_posts(array(
        'post_type' => 'studio_shops',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ));

    if (empty($posts)) {
        $error_result = array(
            'shops' => array(),
            'error' => 'No studio shops found'
        );
        StudioDataCache::set($cache_key, $error_result);
        return $error_result;
    }

    $shops_data = array();
    foreach ($posts as $post_id) {
        $shop_id_field = get_field('shop_id', $post_id);
        // shop_idフィールドが設定されていない場合はPost IDを使用
        $shop_id = (!empty($shop_id_field)) ? $shop_id_field : $post_id;

        // 基本情報を取得（Post IDを引数として渡す）
        $basic_info = get_studio_shop_basic_info($post_id);

        // IDを正しく設定
        $basic_info['id'] = $shop_id;

        if (!empty($basic_info['name'])) {
            $shops_data[] = $basic_info;
        }
    }

    $result = array(
        'shops' => $shops_data,
        'error' => null
    );

    StudioDataCache::set($cache_key, $result);
    return $result;
}

/**
 * キャッシュクリア関数
 *
 * 開発時やデバッグ時にキャッシュをクリアします。
 */
function clear_studio_data_helpers_cache() {
    StudioDataCache::clear();
}

/**
 * デバッグ情報の取得
 *
 * 現在のキャッシュ状況やデータの整合性をチェックします。
 *
 * @return array デバッグ情報
 */
function get_studio_data_helpers_debug_info() {
    return array(
        'cache_count' => count(StudioDataCache::$cache),
        'cache_keys' => array_keys(StudioDataCache::$cache),
        'memory_usage' => memory_get_usage(true),
        'version' => '1.0.0',
        'status' => 'active'
    );
}

/**
 * 管理者向けデバッグ機能
 *
 * URLパラメータでデバッグ情報を表示
 */
if (isset($_GET['studio_debug']) && current_user_can('administrator')) {
    add_action('wp_footer', function() {
        $debug_info = get_studio_data_helpers_debug_info();
        echo '<div style="position:fixed; bottom:10px; right:10px; background:#000; color:#0f0; padding:10px; font-family:monospace; font-size:12px; z-index:9999;">';
        echo '<strong>Studio Data Helpers Debug</strong><br>';
        echo 'Cache Count: ' . $debug_info['cache_count'] . '<br>';
        echo 'Memory: ' . round($debug_info['memory_usage'] / 1024 / 1024, 2) . 'MB<br>';
        echo 'Version: ' . $debug_info['version'] . '<br>';
        echo '</div>';
    });
}

/**
 * テスト用ショートコード
 *
 * 使用方法: [studio_helpers_test]
 */
add_shortcode('studio_helpers_test', function($atts) {
    if (!current_user_can('administrator')) {
        return '<p style="color:red;">管理者権限が必要です。</p>';
    }

    ob_start();

    $shop_id = isset($atts['shop_id']) ? intval($atts['shop_id']) : 122;

    echo '<div style="font-family:monospace; background:#f9f9f9; padding:20px; border:1px solid #ddd;">';
    echo '<h3>Studio Data Helpers テスト結果</h3>';
    echo '<style>.success{color:green;} .error{color:red;} .info{color:blue;}</style>';

    echo "<p><strong>テスト対象店舗ID:</strong> {$shop_id}</p>";

    // 1. Post ID取得テスト
    $post_id = get_studio_post_id_by_shop_id($shop_id);
    if ($post_id) {
        echo "<div class='success'>✅ Post ID取得成功: {$post_id}</div>";
    } else {
        echo "<div class='error'>❌ Post ID取得失敗</div>";
        echo '</div>';
        return ob_get_clean();
    }

    // 2. Website URL フィールドテスト
    $website_url = get_studio_shop_field($shop_id, 'website_url');
    echo "<div class='info'><strong>Website URL:</strong> " . ($website_url ? $website_url : '(未設定)') . "</div>";

    // 3. 基本情報テスト
    $basic_info = get_studio_shop_basic_info($shop_id);
    echo "<div class='info'><strong>店舗名:</strong> " . $basic_info['name'] . "</div>";
    echo "<div class='info'><strong>認定店:</strong> " . ($basic_info['is_certified_store'] ? 'はい' : 'いいえ') . "</div>";

    // 4. 既存システムとの比較
    if (function_exists('get_studio_shop_data_acf')) {
        $old_data = get_studio_shop_data_acf($shop_id);
        $new_data = get_studio_shop_data_simple($shop_id);

        $old_website = $old_data['shop']['website_url'] ?? '';
        $new_website = $new_data['shop']['website_url'] ?? '';

        echo "<hr>";
        echo "<p><strong>既存システム vs 新システム比較:</strong></p>";
        echo "<div class='info'>既存 Website URL: " . ($old_website ?: '(未設定)') . "</div>";
        echo "<div class='info'>新システム Website URL: " . ($new_website ?: '(未設定)') . "</div>";

        if ($old_website === $new_website) {
            echo "<div class='success'>✅ Website URLデータ一致</div>";
        } else {
            echo "<div class='error'>❌ Website URLデータ不一致</div>";
        }
    }

    // 5. デバッグ情報
    $debug_info = get_studio_data_helpers_debug_info();
    echo "<hr>";
    echo "<div class='info'><strong>キャッシュ数:</strong> " . $debug_info['cache_count'] . "</div>";
    echo "<div class='info'><strong>メモリ使用量:</strong> " . round($debug_info['memory_usage'] / 1024 / 1024, 2) . 'MB</div>';

    echo "<hr>";
    echo "<div class='success'>✅ Phase 1 テスト完了</div>";
    echo '</div>';

    return ob_get_clean();
});