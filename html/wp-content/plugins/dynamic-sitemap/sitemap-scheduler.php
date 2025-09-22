<?php
/**
 * 678photo.com Sitemap Scheduler
 * Automatic sitemap generation every 6 hours
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

class SitemapScheduler678 {

    private $hook_name = 'generate_678photo_sitemap';

    public function __construct() {
        // WordPress cron hooks
        add_action($this->hook_name, [$this, 'generate_sitemap_files']);
        add_action('wp', [$this, 'schedule_sitemap_generation']);

        // Admin menu for manual generation
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Plugin activation/deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate_scheduler']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate_scheduler']);
    }

    /**
     * Schedule sitemap generation if not already scheduled
     */
    public function schedule_sitemap_generation() {
        if (!wp_next_scheduled($this->hook_name)) {
            // Schedule to run every 6 hours starting now
            wp_schedule_event(time(), 'every_6_hours', $this->hook_name);
        }
    }

    /**
     * Generate sitemap files using the standalone generator
     */
    public function generate_sitemap_files() {
        try {
            $generator_file = ABSPATH . 'auto-sitemap-generator-678photo.php';

            if (file_exists($generator_file)) {
                // Include and run the generator
                require_once $generator_file;
                $generator = new AutoSitemapGenerator678();
                $generator->generate_all_sitemaps();

                // Log successful generation
                error_log('678photo.com sitemap generated successfully at ' . current_time('mysql'));
            } else {
                error_log('678photo.com sitemap generator file not found');
            }
        } catch (Exception $e) {
            error_log('678photo.com sitemap generation error: ' . $e->getMessage());
        }
    }

    /**
     * Add admin menu for manual sitemap generation
     */
    public function add_admin_menu() {
        add_options_page(
            '678photo Sitemaps',
            '678photo Sitemaps',
            'manage_options',
            '678photo-sitemaps',
            [$this, 'admin_page']
        );
    }

    /**
     * Admin page for sitemap management
     */
    public function admin_page() {
        // Handle manual generation
        if (isset($_POST['generate_now']) && wp_verify_nonce($_POST['_wpnonce'], 'generate_sitemap')) {
            $this->generate_sitemap_files();
            echo '<div class="notice notice-success"><p>サイトマップを生成しました！</p></div>';
        }

        $next_scheduled = wp_next_scheduled($this->hook_name);
        ?>
        <div class="wrap">
            <h1>678photo.com Sitemap Generator</h1>

            <div class="card">
                <h2>自動生成スケジュール</h2>
                <?php if ($next_scheduled): ?>
                    <p>次回実行予定: <strong><?php echo date('Y-m-d H:i:s', $next_scheduled); ?></strong></p>
                <?php else: ?>
                    <p style="color: red;">スケジュールが設定されていません</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>手動生成</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('generate_sitemap'); ?>
                    <input type="submit" name="generate_now" class="button-primary" value="今すぐ生成" />
                </form>
            </div>

            <div class="card">
                <h2>生成されるサイトマップ</h2>
                <ul>
                    <li><a href="<?php echo home_url('/sitemap.xml'); ?>" target="_blank">sitemap.xml</a> (メインサイトマップ)</li>
                    <li><a href="<?php echo home_url('/sitemap-pages.xml'); ?>" target="_blank">sitemap-pages.xml</a> (固定ページ)</li>
                    <li><a href="<?php echo home_url('/sitemap-posts.xml'); ?>" target="_blank">sitemap-posts.xml</a> (投稿)</li>
                    <li><a href="<?php echo home_url('/sitemap-stores.xml'); ?>" target="_blank">sitemap-stores.xml</a> (店舗)</li>
                    <li><a href="<?php echo home_url('/sitemap-studio-shops.xml'); ?>" target="_blank">sitemap-studio-shops.xml</a> (スタジオショップ)</li>
                    <li><a href="<?php echo home_url('/sitemap-seo-articles.xml'); ?>" target="_blank">sitemap-seo-articles.xml</a> (SEO記事)</li>
                    <li><a href="<?php echo home_url('/sitemap-images.xml'); ?>" target="_blank">sitemap-images.xml</a> (画像)</li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Plugin activation
     */
    public function activate_scheduler() {
        // Add custom cron interval
        add_filter('cron_schedules', [$this, 'add_cron_intervals']);

        // Schedule first run
        if (!wp_next_scheduled($this->hook_name)) {
            wp_schedule_event(time(), 'every_6_hours', $this->hook_name);
        }

        // Generate initial sitemap
        $this->generate_sitemap_files();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate_scheduler() {
        wp_clear_scheduled_hook($this->hook_name);
    }

    /**
     * Add custom cron intervals
     */
    public function add_cron_intervals($schedules) {
        $schedules['every_6_hours'] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display'  => 'Every 6 Hours'
        );
        return $schedules;
    }
}

// Add cron interval filter globally
add_filter('cron_schedules', function($schedules) {
    $schedules['every_6_hours'] = array(
        'interval' => 6 * HOUR_IN_SECONDS,
        'display'  => 'Every 6 Hours'
    );
    return $schedules;
});

// Initialize scheduler
new SitemapScheduler678();