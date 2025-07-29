<?php
/**
 * カテゴリー別ギャラリーから単純ギャラリーへの移行スクリプト
 * 
 * このスクリプトは以下を実行します：
 * 1. カテゴリー画像をメインギャラリーに統合
 * 2. 画像ファイルをリネーム（category_* → shop_*）
 * 3. データベースの更新
 */

// WordPress環境の読み込み
require_once __DIR__ . '/../html/wp-config.php';

class GalleryMigration {
    private $wpdb;
    private $upload_dir;
    private $log = [];
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->upload_dir = __DIR__ . '/../html/studio_shop_galary/';
        
        echo "=== カテゴリー別ギャラリー → 単純ギャラリー移行開始 ===\n";
        echo "実行時間: " . date('Y-m-d H:i:s') . "\n\n";
    }
    
    /**
     * メイン実行関数
     */
    public function run() {
        try {
            // 1. 現在のデータ状況を確認
            $this->analyzeCurrentData();
            
            // 2. 統合API経由でデータを取得
            $shops_data = $this->getShopsData();
            
            if (empty($shops_data)) {
                throw new Exception("ショップデータが取得できませんでした");
            }
            
            // 3. 各店舗のデータを移行
            foreach ($shops_data as $shop) {
                $this->migrateShopData($shop);
            }
            
            // 4. 移行結果を表示
            $this->showResults();
            
        } catch (Exception $e) {
            echo "エラーが発生しました: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    /**
     * 現在のデータ状況を分析
     */
    private function analyzeCurrentData() {
        echo "--- 現在のデータ状況分析 ---\n";
        
        // カテゴリーテーブルの確認
        $category_count = $this->wpdb->get_var("SELECT COUNT(*) FROM studio_shop_categories");
        echo "カテゴリー数: {$category_count}\n";
        
        // 画像ファイルの確認
        $category_files = glob($this->upload_dir . 'category_*');
        $shop_files = glob($this->upload_dir . 'shop_*');
        
        echo "カテゴリー画像ファイル数: " . count($category_files) . "\n";
        echo "ショップ画像ファイル数: " . count($shop_files) . "\n\n";
        
        $this->log['initial'] = [
            'category_count' => $category_count,
            'category_files' => count($category_files),
            'shop_files' => count($shop_files)
        ];
    }
    
    /**
     * 統合API経由でショップデータを取得
     */
    private function getShopsData() {
        // 統一キャッシュシステムを使用
        if (function_exists('get_cached_studio_data')) {
            $result = get_cached_studio_data();
            if (isset($result['shops'])) {
                return $result['shops'];
            }
        }
        
        // フォールバック: 直接データベースから取得
        return $this->getShopsFromDatabase();
    }
    
    /**
     * データベースから直接ショップデータを取得
     */
    private function getShopsFromDatabase() {
        $shops = $this->wpdb->get_results("SELECT * FROM studio_shops", ARRAY_A);
        
        foreach ($shops as &$shop) {
            // カテゴリー画像を取得
            $category_images = $this->wpdb->get_results($this->wpdb->prepare("
                SELECT category_name, image_url, id 
                FROM studio_shop_categories 
                WHERE shop_id = %d
            ", $shop['id']), ARRAY_A);
            
            $shop['category_images'] = [];
            foreach ($category_images as $cat_img) {
                $shop['category_images'][$cat_img['category_name']][] = [
                    'url' => $cat_img['image_url'],
                    'id' => $cat_img['id']
                ];
            }
            
            // メイン画像を取得
            $main_images = $this->wpdb->get_results($this->wpdb->prepare("
                SELECT image_url, id 
                FROM studio_shop_main_gallery 
                WHERE shop_id = %d
            ", $shop['id']), ARRAY_A);
            
            $shop['main_gallery_images'] = array_map(function($img) {
                return ['url' => $img['image_url'], 'id' => $img['id']];
            }, $main_images);
        }
        
        return $shops;
    }
    
    /**
     * 個別店舗のデータを移行
     */
    private function migrateShopData($shop) {
        $shop_id = $shop['id'];
        echo "--- 店舗ID: {$shop_id} ({$shop['name']}) の移行開始 ---\n";
        
        $migrated_count = 0;
        $main_images = isset($shop['main_gallery_images']) ? $shop['main_gallery_images'] : [];
        $category_images = isset($shop['category_images']) ? $shop['category_images'] : [];
        
        echo "既存メイン画像: " . count($main_images) . "枚\n";
        echo "カテゴリー画像: " . $this->countCategoryImages($category_images) . "枚\n";
        
        // カテゴリー画像をメインギャラリーに移行
        foreach ($category_images as $category_name => $images) {
            foreach ($images as $image) {
                $old_url = $image['url'];
                $old_filename = basename($old_url);
                
                // 新しいファイル名を生成
                $new_filename = $this->generateNewFilename($shop_id, $old_filename);
                $new_url = str_replace($old_filename, $new_filename, $old_url);
                
                // ファイルをリネーム
                if ($this->renameImageFile($old_filename, $new_filename)) {
                    // データベースに新しいメイン画像として追加
                    $this->addToMainGallery($shop_id, $new_url);
                    $migrated_count++;
                    
                    echo "  移行: {$old_filename} → {$new_filename}\n";
                } else {
                    echo "  エラー: {$old_filename} のリネームに失敗\n";
                }
            }
        }
        
        // カテゴリーデータを削除
        $this->deleteCategoryData($shop_id);
        
        echo "移行完了: {$migrated_count}枚の画像を統合\n\n";
        
        $this->log['shops'][$shop_id] = [
            'name' => $shop['name'],
            'migrated_count' => $migrated_count,
            'original_main' => count($main_images),
            'original_category' => $this->countCategoryImages($category_images)
        ];
    }
    
    /**
     * カテゴリー画像の総数をカウント
     */
    private function countCategoryImages($category_images) {
        $count = 0;
        foreach ($category_images as $images) {
            $count += count($images);
        }
        return $count;
    }
    
    /**
     * 新しいファイル名を生成
     */
    private function generateNewFilename($shop_id, $old_filename) {
        $timestamp = time();
        $extension = pathinfo($old_filename, PATHINFO_EXTENSION);
        
        // 既存のshop_*ファイルの数を確認
        $existing_count = count(glob($this->upload_dir . "shop_{$shop_id}_*"));
        
        return "shop_{$shop_id}_{$timestamp}_{$existing_count}.{$extension}";
    }
    
    /**
     * 画像ファイルをリネーム
     */
    private function renameImageFile($old_filename, $new_filename) {
        $old_path = $this->upload_dir . $old_filename;
        $new_path = $this->upload_dir . $new_filename;
        
        if (file_exists($old_path)) {
            return rename($old_path, $new_path);
        }
        
        return false;
    }
    
    /**
     * メインギャラリーにエントリを追加
     */
    private function addToMainGallery($shop_id, $image_url) {
        return $this->wpdb->insert(
            'studio_shop_main_gallery',
            [
                'shop_id' => $shop_id,
                'image_url' => $image_url,
                'uploaded_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s']
        );
    }
    
    /**
     * カテゴリーデータを削除
     */
    private function deleteCategoryData($shop_id) {
        $deleted = $this->wpdb->delete(
            'studio_shop_categories',
            ['shop_id' => $shop_id],
            ['%d']
        );
        
        if ($deleted > 0) {
            echo "  カテゴリーデータ削除: {$deleted}件\n";
        }
    }
    
    /**
     * 移行結果を表示
     */
    private function showResults() {
        echo "=== 移行結果 ===\n";
        
        $total_migrated = 0;
        $total_shops = count($this->log['shops']);
        
        foreach ($this->log['shops'] as $shop_id => $data) {
            echo "店舗ID {$shop_id} ({$data['name']}): ";
            echo "メイン{$data['original_main']}枚 + カテゴリー{$data['original_category']}枚 → ";
            echo "統合後" . ($data['original_main'] + $data['migrated_count']) . "枚\n";
            
            $total_migrated += $data['migrated_count'];
        }
        
        echo "\n総計:\n";
        echo "- 処理済み店舗数: {$total_shops}\n";
        echo "- 移行画像数: {$total_migrated}枚\n";
        echo "- 実行時間: " . date('Y-m-d H:i:s') . "\n";
        
        echo "\n=== 移行完了 ===\n";
    }
}

// スクリプト実行
if (php_sapi_name() === 'cli') {
    $migration = new GalleryMigration();
    $migration->run();
} else {
    echo "このスクリプトはコマンドラインから実行してください。\n";
}