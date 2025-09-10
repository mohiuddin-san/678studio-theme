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
        
        // 店舗データを構築
        $shop = array(
            'id' => $studio->ID,
            'name' => $fields['name'] ?? $studio->post_title,
            'address' => $fields['address'] ?? '',
            'phone' => $fields['phone'] ?? '',
            'nearest_station' => $fields['nearest_station'] ?? '',
            'business_hours' => $fields['business_hours'] ?? '',
            'holidays' => $fields['holidays'] ?? '',
            'main_image' => $fields['main_image'] ?? '',
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