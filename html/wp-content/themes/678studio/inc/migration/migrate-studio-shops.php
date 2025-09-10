<?php
/**
 * Studio Shops Migration Script
 * 既存のプラグインデータをACFベースのカスタム投稿タイプに移行
 * 
 * Usage: WP-CLI経由で実行
 * wp eval-file wp-content/themes/678studio/inc/migration/migrate-studio-shops.php
 */

// 移行処理のメインクラス
class Studio_Shops_Migration {
    
    private $dry_run = false;
    private $migrated_count = 0;
    private $failed_count = 0;
    
    public function __construct($dry_run = false) {
        $this->dry_run = $dry_run;
    }
    
    /**
     * 移行処理を実行
     */
    public function migrate() {
        echo "Starting Studio Shops migration...\n";
        
        if ($this->dry_run) {
            echo "DRY RUN MODE - No actual changes will be made\n";
        }
        
        // 既存データを取得
        $existing_data = $this->get_existing_data();
        
        if (!$existing_data || !isset($existing_data['shops'])) {
            echo "Error: No existing shop data found\n";
            return false;
        }
        
        $total_shops = count($existing_data['shops']);
        echo "Found {$total_shops} shops to migrate\n\n";
        
        foreach ($existing_data['shops'] as $index => $shop) {
            $shop_number = $index + 1;
            echo "Processing shop {$shop_number}/{$total_shops}: {$shop['name']}...";
            
            if ($this->migrate_single_shop($shop)) {
                $this->migrated_count++;
                echo " ✓\n";
            } else {
                $this->failed_count++;
                echo " ✗\n";
            }
        }
        
        echo "\n======================\n";
        echo "Migration Complete!\n";
        echo "Migrated: {$this->migrated_count} shops\n";
        echo "Failed: {$this->failed_count} shops\n";
        
        if (!$this->dry_run && $this->migrated_count > 0) {
            // パーマリンクをフラッシュ
            flush_rewrite_rules();
            echo "Permalinks flushed\n";
            
            // キャッシュをクリア
            if (function_exists('clear_studio_data_cache')) {
                clear_studio_data_cache();
                echo "Cache cleared\n";
            }
        }
        
        return true;
    }
    
    /**
     * 既存データを取得
     */
    private function get_existing_data() {
        // functions.phpの関数を使用
        if (function_exists('get_cached_studio_data')) {
            return get_cached_studio_data();
        }
        
        // 直接APIを呼び出し
        if (function_exists('make_internal_api_call')) {
            return make_internal_api_call('getAllShops', []);
        }
        
        return false;
    }
    
    /**
     * 単一ショップを移行
     */
    private function migrate_single_shop($shop) {
        // 既存の投稿をチェック（shop_idで検索）
        $existing_post = $this->find_existing_post($shop['id']);
        
        if ($existing_post) {
            echo " (updating existing)";
            $post_id = $existing_post->ID;
        } else {
            // 新規投稿を作成
            $post_data = array(
                'post_title'    => $shop['name'],
                'post_status'   => 'publish',
                'post_type'     => 'studio_shops',
                'post_author'   => 1,
            );
            
            if (!$this->dry_run) {
                $post_id = wp_insert_post($post_data);
                
                if (is_wp_error($post_id)) {
                    echo " Error: " . $post_id->get_error_message();
                    return false;
                }
            } else {
                $post_id = 'DRY_RUN_ID';
            }
        }
        
        if (!$this->dry_run && $post_id) {
            // ACFフィールドを更新
            update_field('shop_id', $shop['id'], $post_id);
            update_field('address', $shop['address'] ?? '', $post_id);
            update_field('phone', $shop['phone'] ?? '', $post_id);
            update_field('nearest_station', $shop['nearest_station'] ?? '', $post_id);
            update_field('business_hours', $shop['business_hours'] ?? '', $post_id);
            update_field('holidays', $shop['holidays'] ?? '', $post_id);
            update_field('company_email', $shop['company_email'] ?? '', $post_id);
            update_field('map_url', $shop['map_url'] ?? '', $post_id);
            
            // メイン画像を処理
            if (!empty($shop['main_image'])) {
                $image_id = $this->process_image($shop['main_image'], $shop['name'] . ' - メイン画像');
                if ($image_id) {
                    update_field('main_image', $image_id, $post_id);
                    set_post_thumbnail($post_id, $image_id);
                }
            }
            
            // ギャラリー画像を処理
            if (!empty($shop['main_gallery_images']) && is_array($shop['main_gallery_images'])) {
                $gallery_ids = array();
                foreach ($shop['main_gallery_images'] as $index => $gallery_image) {
                    $image_url = is_array($gallery_image) && isset($gallery_image['url']) 
                        ? $gallery_image['url'] 
                        : $gallery_image;
                    
                    $image_id = $this->process_image($image_url, $shop['name'] . ' - ギャラリー画像 ' . ($index + 1));
                    if ($image_id) {
                        $gallery_ids[] = $image_id;
                    }
                }
                
                if (!empty($gallery_ids)) {
                    update_field('gallery_images', $gallery_ids, $post_id);
                }
            }
            
            // スタッフ情報を処理
            if (!empty($shop['staff']) && is_array($shop['staff'])) {
                $staff_data = array();
                foreach ($shop['staff'] as $staff) {
                    $staff_entry = array(
                        'name' => $staff['name'] ?? '',
                        'position' => $staff['position'] ?? '',
                        'message' => $staff['message'] ?? '',
                    );
                    
                    if (!empty($staff['image'])) {
                        $staff_image_id = $this->process_image($staff['image'], $staff['name'] . ' - スタッフ画像');
                        if ($staff_image_id) {
                            $staff_entry['image'] = $staff_image_id;
                        }
                    }
                    
                    $staff_data[] = $staff_entry;
                }
                
                if (!empty($staff_data)) {
                    update_field('staff_members', $staff_data, $post_id);
                }
            }
            
            // 都道府県タクソノミーを設定
            if (!empty($shop['address'])) {
                $prefecture = $this->extract_prefecture($shop['address']);
                if ($prefecture) {
                    wp_set_object_terms($post_id, $prefecture, 'studio_prefecture');
                }
            }
        }
        
        return true;
    }
    
    /**
     * 既存の投稿を検索
     */
    private function find_existing_post($shop_id) {
        $args = array(
            'post_type' => 'studio_shops',
            'meta_query' => array(
                array(
                    'key' => 'shop_id',
                    'value' => $shop_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        );
        
        $posts = get_posts($args);
        return !empty($posts) ? $posts[0] : null;
    }
    
    /**
     * 画像を処理（Base64またはURLから）
     */
    private function process_image($image_data, $title = '') {
        if (empty($image_data)) {
            return false;
        }
        
        // Base64データの場合
        if (strpos($image_data, 'data:image') === 0) {
            return $this->upload_base64_image($image_data, $title);
        }
        
        // URLの場合
        if (filter_var($image_data, FILTER_VALIDATE_URL)) {
            return $this->sideload_image($image_data, $title);
        }
        
        return false;
    }
    
    /**
     * Base64画像をアップロード
     */
    private function upload_base64_image($base64_string, $title = '') {
        // Base64データを解析
        $data = explode(',', $base64_string);
        if (count($data) !== 2) {
            return false;
        }
        
        // MIMEタイプを取得
        preg_match('/data:image\/(\w+);base64/', $data[0], $matches);
        $extension = isset($matches[1]) ? $matches[1] : 'jpg';
        
        // デコード
        $image_data = base64_decode($data[1]);
        if (!$image_data) {
            return false;
        }
        
        // ファイル名を生成
        $filename = sanitize_file_name($title) . '_' . uniqid() . '.' . $extension;
        
        // WordPressのアップロードディレクトリに保存
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'] . '/' . $filename;
        $upload_url = $upload_dir['url'] . '/' . $filename;
        
        file_put_contents($upload_path, $image_data);
        
        // メディアライブラリに追加
        $attachment = array(
            'post_mime_type' => 'image/' . $extension,
            'post_title'     => $title,
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        $attach_id = wp_insert_attachment($attachment, $upload_path);
        
        // メタデータを生成
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        return $attach_id;
    }
    
    /**
     * URLから画像をサイドロード
     */
    private function sideload_image($url, $title = '') {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // 一時ファイルにダウンロード
        $tmp = download_url($url);
        
        if (is_wp_error($tmp)) {
            return false;
        }
        
        // ファイル配列を準備
        $file_array = array(
            'name' => basename($url),
            'tmp_name' => $tmp
        );
        
        // メディアライブラリに追加
        $id = media_handle_sideload($file_array, 0, $title);
        
        // 一時ファイルを削除
        @unlink($tmp);
        
        if (is_wp_error($id)) {
            return false;
        }
        
        return $id;
    }
    
    /**
     * 住所から都道府県を抽出
     */
    private function extract_prefecture($address) {
        $prefectures = array(
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
            '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
            '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
            '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
        );
        
        foreach ($prefectures as $prefecture) {
            if (strpos($address, $prefecture) !== false) {
                return $prefecture;
            }
        }
        
        return null;
    }
}

// 実行
if (defined('WP_CLI') && WP_CLI) {
    // 環境変数でdry-runモードを制御
    $dry_run = getenv('DRY_RUN') === 'true';
    $migration = new Studio_Shops_Migration($dry_run);
    $migration->migrate();
} else {
    echo "This script must be run via WP-CLI\n";
    echo "Usage: wp eval-file wp-content/themes/678studio/inc/migration/migrate-studio-shops.php\n";
    echo "       DRY_RUN=true wp eval-file wp-content/themes/678studio/inc/migration/migrate-studio-shops.php\n";
}