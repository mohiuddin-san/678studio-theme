<?php
/**
 * Studio Shops API - Get all studio shops for reservation dropdown
 */

// WordPress環境を読み込み
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// OPTIONSリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // ACFからstudio_shopsを取得
    $studios = get_posts(array(
        'post_type' => 'studio_shops',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));
    
    $shops_data = array();

    foreach ($studios as $studio) {
        $fields = get_fields($studio->ID);
        
        // main_image フィールドの処理
        $main_image_url = '';
        if (isset($fields['main_image'])) {
            $main_image = $fields['main_image'];
            if (is_array($main_image) && isset($main_image['url'])) {
                // ACF画像フィールドが配列で返される場合
                $main_image_url = $main_image['url'];
            } elseif (is_string($main_image) && filter_var($main_image, FILTER_VALIDATE_URL)) {
                // 文字列でURLが返される場合
                $main_image_url = $main_image;
            } elseif (is_numeric($main_image)) {
                // 画像IDが返される場合
                $main_image_url = wp_get_attachment_image_url($main_image, 'full');
            }
        }

        // 店舗データを構築
        $shop = array(
            'id' => $studio->ID,
            'name' => $fields['name'] ?? $studio->post_title,
            'address' => $fields['address'] ?? '',
            'phone' => $fields['phone'] ?? '',
            'nearest_station' => $fields['nearest_station'] ?? '',
            'business_hours' => $fields['business_hours'] ?? '',
            'holidays' => $fields['holidays'] ?? '',
            'main_image' => $main_image_url,
            'prefecture' => $fields['prefecture'] ?? '',
            'is_certified_store' => isset($fields['is_certified_store']) ? $fields['is_certified_store'] : false
        );
        
        $shops_data[] = $shop;
    }

    // レスポンスを返す
    wp_send_json_success(array(
        'shops' => $shops_data,
        'total' => count($shops_data)
    ));

} catch (Exception $e) {
    error_log('Studio Shops API Error: ' . $e->getMessage());
    wp_send_json_error(array(
        'message' => 'Failed to fetch studio shops: ' . $e->getMessage()
    ));
}