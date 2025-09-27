<?php
/**
 * Studio Data Helpers Test Page
 *
 * このファイルは新しいヘルパー関数のテスト用です。
 * URL: http://localhost:8080/wp-content/themes/678studio/test-studio-helpers.php
 */

// WordPressの読み込み
require_once('../../../../../wp-config.php');

// 管理者権限チェック
if (!current_user_can('administrator')) {
    wp_die('管理者権限が必要です。');
}

// テスト関数
function test_studio_helpers() {
    echo "<h1>Studio Data Helpers テスト</h1>";
    echo "<style>body{font-family:monospace;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

    $shop_id = 122; // テスト用店舗ID

    echo "<h2>テスト対象店舗ID: {$shop_id}</h2>";

    // 1. Post ID取得テスト
    echo "<h3>1. Post ID取得テスト</h3>";
    $post_id = get_studio_post_id_by_shop_id($shop_id);
    if ($post_id) {
        echo "<div class='success'>✅ Post ID取得成功: {$post_id}</div>";
    } else {
        echo "<div class='error'>❌ Post ID取得失敗</div>";
        return;
    }

    // 2. 個別フィールド取得テスト
    echo "<h3>2. 個別フィールド取得テスト</h3>";
    $fields_to_test = array('store_name', 'website_url', 'phone', 'address');
    foreach ($fields_to_test as $field) {
        $value = get_studio_shop_field($shop_id, $field);
        echo "<div class='info'>{$field}: " . ($value ? $value : '(空)') . "</div>";
    }

    // 3. 基本情報一括取得テスト
    echo "<h3>3. 基本情報一括取得テスト</h3>";
    $basic_info = get_studio_shop_basic_info($shop_id);
    echo "<pre>";
    print_r($basic_info);
    echo "</pre>";

    // 4. 完全データ取得テスト
    echo "<h3>4. 完全データ取得テスト</h3>";
    $full_data = get_studio_shop_data_simple($shop_id);
    if (isset($full_data['error'])) {
        echo "<div class='error'>❌ エラー: " . $full_data['error'] . "</div>";
    } else {
        echo "<div class='success'>✅ データ取得成功</div>";
        echo "<div class='info'>店舗名: " . $full_data['shop']['name'] . "</div>";
        echo "<div class='info'>ウェブサイトURL: " . ($full_data['shop']['website_url'] ?: '(未設定)') . "</div>";
        echo "<div class='info'>認定店: " . ($full_data['shop']['is_certified_store'] ? 'はい' : 'いいえ') . "</div>";
    }

    // 5. パフォーマンステスト
    echo "<h3>5. パフォーマンステスト</h3>";
    $start_time = microtime(true);
    for ($i = 0; $i < 10; $i++) {
        get_studio_shop_basic_info($shop_id);
    }
    $end_time = microtime(true);
    $duration = ($end_time - $start_time) * 1000;
    echo "<div class='info'>10回実行時間: " . round($duration, 2) . "ms (キャッシュ効果確認)</div>";

    // 6. デバッグ情報
    echo "<h3>6. デバッグ情報</h3>";
    $debug_info = get_studio_data_helpers_debug_info();
    echo "<pre>";
    print_r($debug_info);
    echo "</pre>";

    // 7. 既存システムとの比較
    echo "<h3>7. 既存システムとの比較</h3>";
    if (function_exists('get_studio_shop_data_acf')) {
        $old_data = get_studio_shop_data_acf($shop_id);
        $new_data = get_studio_shop_data_simple($shop_id);

        echo "<h4>既存システム:</h4>";
        echo "<div class='info'>店舗名: " . ($old_data['shop']['name'] ?? '取得失敗') . "</div>";
        echo "<div class='info'>ウェブサイトURL: " . ($old_data['shop']['website_url'] ?? '未設定') . "</div>";

        echo "<h4>新システム:</h4>";
        echo "<div class='info'>店舗名: " . ($new_data['shop']['name'] ?? '取得失敗') . "</div>";
        echo "<div class='info'>ウェブサイトURL: " . ($new_data['shop']['website_url'] ?? '未設定') . "</div>";

        // データ一致確認
        $old_name = $old_data['shop']['name'] ?? '';
        $new_name = $new_data['shop']['name'] ?? '';
        if ($old_name === $new_name) {
            echo "<div class='success'>✅ 店舗名データ一致</div>";
        } else {
            echo "<div class='error'>❌ 店舗名データ不一致</div>";
        }
    } else {
        echo "<div class='error'>既存システムが利用できません</div>";
    }

    echo "<h3>テスト完了</h3>";
    echo "<p>全ての機能が正常に動作している場合、Phase 1は成功です。</p>";
}

// テスト実行
test_studio_helpers();

// キャッシュクリア
echo "<hr>";
echo "<h3>キャッシュクリア</h3>";
clear_studio_data_helpers_cache();
echo "<div class='success'>✅ キャッシュクリア完了</div>";

echo "<hr>";
echo "<p><a href='http://localhost:8080/studio-detail/?shop_id=122'>→ 店舗詳細ページで実際の表示確認</a></p>";
echo "<p><a href='http://localhost:8080/studio-detail/?shop_id=122&studio_debug=1'>→ デバッグモードで表示確認</a></p>";
?>