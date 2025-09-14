<?php
/**
 * Plugin Name: Auto Sitemap Generator
 * Description: 投稿の保存・削除時に自動でサイトマップを更新
 * Version: 1.0
 * Author: Claude Code Security Team
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

class AutoSitemapPlugin {
    
    public function __construct() {
        // WordPressのフックを設定
        add_action('save_post', array($this, 'on_post_save'), 10, 2);
        add_action('before_delete_post', array($this, 'on_post_delete'));
        add_action('transition_post_status', array($this, 'on_post_status_change'), 10, 3);
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * 管理画面メニューを追加
     */
    public function add_admin_menu() {
        add_options_page(
            'Auto Sitemap', 
            'Auto Sitemap', 
            'manage_options', 
            'auto-sitemap', 
            array($this, 'admin_page')
        );
    }
    
    /**
     * 管理画面ページ
     */
    public function admin_page() {
        if (isset($_POST['regenerate'])) {
            $this->regenerate_all_sitemaps();
            echo '<div class="notice notice-success"><p>サイトマップを再生成しました！</p></div>';
        }
        
        ?>
        <div class="wrap">
            <h1>Auto Sitemap Generator</h1>
            <p>投稿の保存・削除時に自動でサイトマップが更新されます。</p>
            
            <form method="post">
                <p>
                    <input type="submit" name="regenerate" class="button-primary" value="全サイトマップを手動再生成" />
                </p>
            </form>
            
            <h2>ログ</h2>
            <pre style="background:#f9f9f9;padding:10px;max-height:300px;overflow:auto;"><?php 
                $log_file = dirname(dirname(dirname(__DIR__))) . '/sitemaps/sitemap.log';
                if (file_exists($log_file)) {
                    echo esc_html(file_get_contents($log_file));
                } else {
                    echo 'ログファイルが見つかりません。';
                }
            ?></pre>
        </div>
        <?php
    }
    
    /**
     * 投稿保存時
     */
    public function on_post_save($post_id, $post) {
        // 自動保存やリビジョンをスキップ
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        $this->regenerate_sitemap_for_post_type($post->post_type);
    }
    
    /**
     * 投稿削除時
     */
    public function on_post_delete($post_id) {
        $post = get_post($post_id);
        if ($post) {
            $this->regenerate_sitemap_for_post_type($post->post_type);
        }
    }
    
    /**
     * 投稿ステータス変更時
     */
    public function on_post_status_change($new_status, $old_status, $post) {
        if ($new_status !== $old_status) {
            $this->regenerate_sitemap_for_post_type($post->post_type);
        }
    }
    
    /**
     * 特定の投稿タイプのサイトマップを再生成
     */
    private function regenerate_sitemap_for_post_type($post_type) {
        // 対象の投稿タイプのみ処理
        $supported_types = ['page', 'post', 'news', 'achievement', 'staffs', 'reading_glasses_news', 'models'];
        
        if (!in_array($post_type, $supported_types)) {
            return;
        }
        
        // バックグラウンドで外部スクリプト実行
        $script_path = dirname(dirname(dirname(__DIR__))) . '/auto-sitemap-generator.php';
        $command = '/usr/bin/php7.4 ' . escapeshellarg($script_path) . ' generate > /dev/null 2>&1 &';
        exec($command);
        
        $this->log_action('Triggered sitemap regeneration for post type: ' . $post_type);
    }
    
    /**
     * 全サイトマップを再生成
     */
    private function regenerate_all_sitemaps() {
        $script_path = dirname(dirname(dirname(__DIR__))) . '/auto-sitemap-generator.php';
        $command = '/usr/bin/php7.4 ' . escapeshellarg($script_path) . ' generate';
        exec($command, $output, $return_code);
        
        $this->log_action('Manual regeneration triggered from admin panel');
        
        return $return_code === 0;
    }
    
    /**
     * ログ出力
     */
    private function log_action($message) {
        $log_file = dirname(dirname(dirname(__DIR__))) . '/sitemaps/sitemap.log';
        $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }
}

// プラグインを初期化
new AutoSitemapPlugin();
?>
