<?php
/**
 * ギャラリー画像削除API
 * 個別削除と全削除をサポート
 */

if (!defined('ABSPATH')) {
    exit;
}

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
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// 入力値の取得と検証
$shop_id = filter_input(INPUT_POST, 'shop_id', FILTER_VALIDATE_INT);
$image_id = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);
$delete_all = filter_input(INPUT_POST, 'delete_all', FILTER_VALIDATE_BOOLEAN);

if (!$shop_id) {
    echo json_encode(['success' => false, 'error' => 'Shop ID is required']);
    exit;
}

try {
    if ($delete_all) {
        // 全ギャラリー画像を削除
        $stmt = $conn->prepare("DELETE FROM studio_shop_images WHERE shop_id = ?");
        $stmt->bind_param("i", $shop_id);
        $result = $stmt->execute();
        
        if ($result) {
            $deleted_count = $stmt->affected_rows;
            echo json_encode([
                'success' => true, 
                'message' => "ギャラリー画像を全て削除しました ({$deleted_count}枚)",
                'deleted_count' => $deleted_count
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete all gallery images']);
        }
        $stmt->close();
    } else if ($image_id) {
        // 個別画像を削除
        $stmt = $conn->prepare("DELETE FROM studio_shop_images WHERE id = ? AND shop_id = ?");
        $stmt->bind_param("ii", $image_id, $shop_id);
        $result = $stmt->execute();
        
        if ($result && $stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'ギャラリー画像を削除しました',
                'deleted_image_id' => $image_id
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Image not found or failed to delete']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Either image_id or delete_all parameter is required']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>