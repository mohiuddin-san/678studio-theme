<?php
/**
 * Test script to verify main image functionality
 */

// Sample base64 image data (small test image)
$sample_main_image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
$sample_gallery_image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAGA4bPtJAAAAABJRU5ErkJggg==';

$test_data = [
    'name' => 'テスト写真館（メイン画像テスト）',
    'address' => '東京都渋谷区テスト1-2-3',
    'phone' => '03-1234-5678',
    'nearest_station' => 'テスト駅',
    'business_hours' => '10:00-18:00',
    'holidays' => '水曜日',
    'company_email' => 'test@example.com',
    'map_url' => 'https://maps.google.com',
    'main_image' => $sample_main_image,
    'gallery_images' => [$sample_gallery_image]
];

echo "Test data prepared:\n";
echo "- Shop name: " . $test_data['name'] . "\n";
echo "- Has main_image: " . (!empty($test_data['main_image']) ? 'Yes' : 'No') . "\n";
echo "- Gallery images count: " . count($test_data['gallery_images']) . "\n";
echo "\nTo test the plugin, you can:\n";
echo "1. Access WordPress admin at http://localhost:8080/wp-admin\n";
echo "2. Go to Studio Shops menu\n";
echo "3. Create a new shop with both main image and gallery images\n";
echo "4. Verify that main image appears in search cards and hero sections\n";
echo "5. Verify that gallery images appear only in gallery sections\n";
?>