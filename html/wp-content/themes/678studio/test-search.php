<?php
/**
 * Test Search Function
 * Access via: /test-search.php
 */

// WordPressの機能を読み込み
require_once('../../../wp-config.php');

echo "<h1>Search Function Test</h1>";

// テスト用のヘルパー関数を含める
if (file_exists('./template-parts/sections/stores/store-search-results.php')) {
    // store-search-results.phpから関数を読み込み（出力を抑制）
    ob_start();
    include './template-parts/sections/stores/store-search-results.php';
    ob_end_clean();
}

echo "<h2>Test 1: Data Retrieval</h2>";

// Phase 3-1: 新しいヘルパー関数を使用
if (function_exists('get_all_studio_shops_data')) {
    echo "✅ get_all_studio_shops_data() function exists<br>";
    $data = get_all_studio_shops_data();
    echo "Data structure: <pre>" . print_r($data, true) . "</pre>";
} else {
    echo "❌ get_all_studio_shops_data() function NOT found<br>";
    // フォールバック：既存システム
    if (function_exists('get_cached_studio_data')) {
        echo "✅ get_cached_studio_data() function exists<br>";
        $data = get_cached_studio_data();
        echo "Data structure: <pre>" . print_r($data, true) . "</pre>";
    } else {
        echo "❌ get_cached_studio_data() function NOT found<br>";
    }
}

echo "<h2>Test 2: Search Function Test</h2>";

if (function_exists('fetch_studio_shops')) {
    echo "✅ fetch_studio_shops() function exists<br>";

    // テスト1: すべての店舗を取得
    echo "<h3>All shops:</h3>";
    $result = fetch_studio_shops('', '', 1);
    echo "Total shops: " . $result['total'] . "<br>";
    echo "Certified shops: " . count($result['certified_shops']) . "<br>";
    echo "Regular shops: " . count($result['regular_shops']) . "<br>";

    // テスト2: 東京都で検索
    echo "<h3>Tokyo prefecture search:</h3>";
    $result = fetch_studio_shops('', '東京都', 1);
    echo "Total shops: " . $result['total'] . "<br>";
    echo "Certified shops: " . count($result['certified_shops']) . "<br>";
    echo "Regular shops: " . count($result['regular_shops']) . "<br>";

    // テスト3: フリーワード検索
    echo "<h3>Free text search ('写真館'):</h3>";
    $result = fetch_studio_shops('写真館', '', 1);
    echo "Total shops: " . $result['total'] . "<br>";
    echo "Certified shops: " . count($result['certified_shops']) . "<br>";
    echo "Regular shops: " . count($result['regular_shops']) . "<br>";

} else {
    echo "❌ fetch_studio_shops() function NOT found<br>";
}

echo "<h2>Test 3: GET Parameters</h2>";
echo "Current GET parameters: <pre>" . print_r($_GET, true) . "</pre>";

?>