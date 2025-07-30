<?php
/**
 * Get all studio shops API endpoint
 */

if (!defined('ABSPATH')) {
    exit;
}

// 文字エンコーディングを明示的に設定
header('Content-Type: application/json; charset=utf-8');

// データベース接続の設定（環境に応じて動的に設定）
if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === 'localhost:8080' || $_SERVER['HTTP_HOST'] === 'localhost') {
    // Local environment (Docker)
    $servername = "mysql-678studio";
    $username = "wp_user";
    $password = "password";
    $dbname = "wordpress_678";
} else {
    // Server environment
    $servername = "localhost";
    $username = "xb592942_hwnzr";
    $password = "bplyipjee2";
    $dbname = "xb592942_1qqor";
}

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // 文字セット設定を強化
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error loading character set utf8mb4: " . $conn->error);
    }
    
    // 追加の文字エンコーディング設定
    $conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->query("SET CHARACTER SET utf8mb4");
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

try {
    // ショップ一覧を取得（作成日時の降順）
    $stmt = $conn->prepare("SELECT id, name, address, phone, nearest_station, business_hours, holidays, map_url, company_email, main_image, created_at FROM studio_shops ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $shops = $result->fetch_all(MYSQLI_ASSOC);
    
    // 各ショップの画像データを取得
    foreach ($shops as &$shop) {
        $shop_id = $shop['id'];
        
        // メインギャラリー画像を取得
        $imageStmt = $conn->prepare("SELECT id, image_url FROM studio_shop_images WHERE shop_id = ? ORDER BY created_at ASC");
        $imageStmt->bind_param("i", $shop_id);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $images = $imageResult->fetch_all(MYSQLI_ASSOC);
        
        $image_data = [];
        foreach ($images as $img) {
            $image_data[] = [
                'id' => $img['id'],
                'url' => $img['image_url']
            ];
        }
        
        $shop['main_gallery_images'] = $image_data;
        $shop['image_urls'] = array_column($image_data, 'url');
        
        $imageStmt->close();
    }
    
    // 日本語を正しく出力するためのオプション設定
    echo json_encode([
        'success' => true,
        'message' => 'Shops retrieved successfully',
        'shops' => $shops
    ], JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>