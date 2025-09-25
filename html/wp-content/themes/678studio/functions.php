<?php
/**
 * 678 Studio Theme Functions
 */

// Initialize WordPress Debug Logger
require_once get_template_directory() . '/lib/debug-logger.php';

// Load custom post types
require_once get_template_directory() . '/inc/post-types/media-achievements.php';

// Load ACF configurations
require_once get_template_directory() . '/inc/acf/media-achievements.php';
require_once get_template_directory() . '/inc/acf/studio-shops-fields.php';

// Load Custom Post Types
require_once get_template_directory() . '/inc/post-types/studio-shops.php';

// Load Studio Shops Compatibility Layer
require_once get_template_directory() . '/inc/studio-shops-compat.php';

// Load Admin Enhancements
require_once get_template_directory() . '/inc/admin-enhancements.php';

// Load Gallery Metabox (WordPress Standard Media)
require_once get_template_directory() . '/inc/gallery-metabox.php';

// ACF JSON save/load functionality
add_filter('acf/settings/save_json', function($path) {
    return get_stylesheet_directory() . '/acf-json';
});

add_filter('acf/settings/load_json', function($paths) {
    unset($paths[0]);
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
});

// Load WP-CLI commands for Studio Shop testing
if (file_exists(get_template_directory() . '/inc/wp-cli-studio-shop-test.php')) {
    require_once get_template_directory() . '/inc/wp-cli-studio-shop-test.php';
}

/**
 * 環境判定関数 - ローカルかサーバーかを判定
 * @return bool true if local environment, false if server
 */
function is_local_environment() {
    // HTTP_HOSTで判定
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
        if ($host === 'localhost:8080' || $host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
            return true;
        }
    }
    
    // DOCUMENT_ROOTでDocker環境を判定
    if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], '/var/www/html') === 0) {
        return true;
    }
    
    // WP_HOME定数で判定
    if (defined('WP_HOME') && strpos(WP_HOME, 'localhost') !== false) {
        return true;
    }
    
    return false;
}

/**
 * ローカル環境用: Studio Shop Manager APIからデータ取得
 */
function get_studio_data_from_local_api() {
    try {
        // データベースから直接取得
        global $wpdb;
        
        // 文字エンコーディングを確保
        $wpdb->query("SET NAMES utf8mb4");
        
        // ショップ一覧を取得（main_imageフィールドを追加）
        $shops = $wpdb->get_results("
            SELECT id, name, address, phone, nearest_station, business_hours, holidays, map_url, created_at, company_email, main_image  
            FROM studio_shops
        ", ARRAY_A);
        
        // Note: main_imageフィールドを含む完全なショップデータを取得
        
        if ($wpdb->last_error) {
            return ['shops' => [], 'error' => 'Database error: ' . $wpdb->last_error];
        }
        
        // 各ショップの画像データを取得
        foreach ($shops as &$shop) {
            $shop_id = $shop['id'];
            
            // メインギャラリー画像
            $main_images = $wpdb->get_results($wpdb->prepare("
                SELECT id, image_url FROM studio_shop_images WHERE shop_id = %d
            ", $shop_id), ARRAY_A);
            
            $image_urls = [];
            $image_data = [];
            foreach ($main_images as $img) {
                $image_urls[] = $img['image_url'];
                $image_data[] = [
                    'id' => $img['id'],
                    'url' => $img['image_url']
                ];
            }
            $shop['image_urls'] = $image_urls;
            $shop['main_gallery_images'] = $image_data;
            
            // カテゴリー機能は廃止されました（シンプルギャラリーシステム）
            $shop['category_images'] = [];
        }
        
        $data = [
            'success' => true,
            'message' => 'Shops retrieved successfully',
            'shops' => $shops
        ];
        
        return $data;
        
    } catch (Exception $e) {
        return ['shops' => [], 'error' => 'Local API error: ' . $e->getMessage()];
    }
}

/**
 * サーバー環境用: 既存APIからデータ取得
 */
function get_studio_data_from_server_api() {
    $api_url = 'https://678photo.com/api/get_all_studio_shop.php';
    
    $response = wp_remote_get($api_url, [
        'timeout' => 8, // タイムアウト短縮
        'sslverify' => false 
    ]);

    if (is_wp_error($response)) {
        return ['shops' => [], 'error' => $response->get_error_message()];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['shops']) || !is_array($data['shops'])) {
        return ['shops' => [], 'error' => 'Invalid API response'];
    }
    
    return $data;
}

// キャッシュ機能付きスタジオデータ取得
function get_cached_studio_data() {
    $cache_key = 'studio_shops_data';
    $cache_duration = 300; // 5分キャッシュ

    // キャッシュから取得を試行
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
    }

    // 環境に応じたAPI呼び出し
    if (is_local_environment()) {
        $data = get_studio_data_from_local_api();
    } else {
        $data = get_studio_data_from_server_api();
    }
    
    // キャッシュに保存（エラーの場合でも短時間キャッシュ）
    if (isset($data['error'])) {
        set_transient($cache_key, $data, 60); // エラー時は1分キャッシュ
    } else {
        set_transient($cache_key, $data, $cache_duration);
    }
    
    return $data;
}

/**
 * スタジオデータキャッシュをクリア
 */
function clear_studio_data_cache() {
    $cache_key = 'studio_shops_data';
    delete_transient($cache_key);
    
    // 個別ショップキャッシュもクリア
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_studio_shop_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_studio_shop_%'");
}

/**
 * Studio Shop Manager の更新時にキャッシュをクリアするフック
 */
function clear_cache_on_shop_update() {
    clear_studio_data_cache();
}

// Studio Shop Manager のアクションフックに接続
add_action('studio_shop_updated', 'clear_cache_on_shop_update');
add_action('studio_shop_created', 'clear_cache_on_shop_update');
add_action('studio_shop_deleted', 'clear_cache_on_shop_update');
add_action('studio_category_updated', 'clear_cache_on_shop_update');
add_action('studio_category_deleted', 'clear_cache_on_shop_update');

/**
 * 管理画面用：手動でキャッシュクリア
 */
function manual_clear_studio_cache() {
    if (current_user_can('manage_options') && isset($_GET['clear_studio_cache'])) {
        clear_studio_data_cache();
        wp_redirect(add_query_arg('cache_cleared', '1', remove_query_arg('clear_studio_cache')));
        exit;
    }
    
    if (isset($_GET['cache_cleared']) && $_GET['cache_cleared'] == '1') {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>スタジオデータキャッシュがクリアされました。</p></div>';
        });
    }
}
add_action('admin_init', 'manual_clear_studio_cache');

/**
 * Studio Detail ページ用: 個別ショップデータ取得
 * @param int $shop_id ショップID
 * @return array ショップデータとエラー情報
 */
function get_studio_shop_by_id($shop_id) {
    // 個別ショップキャッシュをチェック
    $cache_key = 'studio_shop_' . $shop_id;
    $cached_shop = get_transient($cache_key);
    
    if ($cached_shop !== false) {
        return ['shop' => $cached_shop, 'error' => null];
    }
    
    // 統一キャッシュシステムから全ショップデータを取得
    $all_shops_data = get_cached_studio_data();
    
    if (isset($all_shops_data['error'])) {
        return ['shop' => null, 'error' => $all_shops_data['error']];
    }
    
    if (!isset($all_shops_data['shops']) || !is_array($all_shops_data['shops'])) {
        return ['shop' => null, 'error' => 'No shops data available'];
    }
    
    // 指定IDのショップを検索
    foreach ($all_shops_data['shops'] as $shop) {
        if (isset($shop['id']) && intval($shop['id']) === intval($shop_id)) {
            // 個別キャッシュに保存 (5分)
            set_transient($cache_key, $shop, 300);
            return ['shop' => $shop, 'error' => null];
        }
    }
    
    return ['shop' => null, 'error' => 'Shop not found'];
}

// Ajax用のスクリプトをエンキュー（ギャラリーページでのみ）
function enqueue_gallery_scripts() {
    if (is_page_template('page-photo-gallery.php')) {
        wp_localize_script('jquery', 'galleryAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gallery_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_gallery_scripts');

// Ajax アクション: ギャラリー用スタジオデータ取得
function ajax_get_gallery_studios() {
    // nonce チェック
    if (!wp_verify_nonce($_POST['nonce'], 'gallery_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }

    $studio_data = get_cached_studio_data();
    
    if (isset($studio_data['error'])) {
        wp_send_json_error(['message' => $studio_data['error']]);
    } else {
        wp_send_json_success($studio_data);
    }
}
add_action('wp_ajax_get_gallery_studios', 'ajax_get_gallery_studios');
add_action('wp_ajax_nopriv_get_gallery_studios', 'ajax_get_gallery_studios');

// AJAX handler for studio search
function ajax_studio_search() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'studio_search_nonce')) {
        wp_die('Security check failed');
    }

    $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $per_page = 6;

    // キャッシュされたデータを使用
    $data = get_cached_studio_data();
    
    if (isset($data['error'])) {
        wp_send_json_error(['message' => $data['error']]);
        return;
    }

    $filtered_shops = $data['shops'];
    if (!empty($search_query)) {
        $filtered_shops = array_filter($data['shops'], function($shop) use ($search_query) {
            return stripos($shop['name'] ?? '', $search_query) !== false || 
                   stripos($shop['nearest_station'] ?? '', $search_query) !== false ||
                   stripos($shop['address'] ?? '', $search_query) !== false;
        });
    }

    $total_shops = count($filtered_shops);
    $total_pages = max(1, ceil($total_shops / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    $shops = array_slice($filtered_shops, $offset, $per_page);

    // Generate HTML for cards
    ob_start();
    if (empty($shops)): ?>
<p>検索結果が見つかりませんでした。</p>
<?php else:
        foreach ($shops as $shop): ?>
<div class="studio-card">
  <div class="studio-card__image">
    <?php
    // メイン画像の表示優先順位: main_image -> image_urls[0] -> デフォルト画像
    $image_src = '';
    if (!empty($shop['main_image'])) {
        // Base64データかURLかを判定
        if (strpos($shop['main_image'], 'data:image') === 0) {
            $image_src = $shop['main_image']; // Base64データはそのまま使用
        } else {
            $image_src = esc_url($shop['main_image']); // URLの場合はエスケープ
        }
    } elseif (!empty($shop['image_urls']) && !empty($shop['image_urls'][0])) {
        // ギャラリー画像をフォールバック
        if (strpos($shop['image_urls'][0], 'data:image') === 0) {
            $image_src = $shop['image_urls'][0];
        } else {
            $image_src = esc_url($shop['image_urls'][0]);
        }
    } else {
        // デフォルト画像
        $image_src = get_template_directory_uri() . '/assets/images/cardpic-sample.jpg';
    }
    ?>
    <img src="<?php echo $image_src; ?>" alt="スタジオ写真">
    <div class="studio-card__location"><?php echo esc_html($shop['nearest_station'] ?? 'N/A'); ?></div>
  </div>
  <div class="studio-card__content">
    <h3 class="studio-card__name"><?php echo esc_html($shop['name'] ?? 'Unknown'); ?></h3>
    <div class="studio-card__details">
      <p class="studio-card__address"><?php echo esc_html($shop['address'] ?? 'N/A'); ?></p>
      <div class="studio-card__hours">
        <div class="studio-card__hour-item">営業時間：<?php echo esc_html($shop['business_hours'] ?? 'N/A'); ?></div>
        <div class="studio-card__hour-item">定休日：<?php echo esc_html($shop['holidays'] ?? 'N/A'); ?></div>
      </div>
    </div>
    <?php get_template_part('template-parts/components/camera-button', null, [
                        'text' => '詳しく見る',
                        'bg_color' => 'detail-card',
                        'icon' => 'none',
                        'class' => 'studio-card__contact-btn',
                        'url' => home_url('/studio-detail/?shop_id=' . $shop['id'])
                    ]); ?>
  </div>
</div>
<?php endforeach;
    endif;
    $cards_html = ob_get_clean();

    // Generate HTML for pagination
    ob_start();
    if ($total_pages > 1): ?>
<a href="#" class="pagination-btn pagination-btn--prev" data-page="<?php echo max(1, $page - 1); ?>"
  <?php echo $page == 1 ? 'data-disabled="true"' : ''; ?>>
  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </svg>
</a>
<div class="pagination-numbers">
  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
  <a href="#" class="<?php echo $i == $page ? 'active' : ''; ?>" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
  <?php endfor; ?>
</div>
<a href="#" class="pagination-btn pagination-btn--next" data-page="<?php echo min($total_pages, $page + 1); ?>"
  <?php echo $page == $total_pages ? 'data-disabled="true"' : ''; ?>>
  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </svg>
</a>
<?php endif;
    $pagination_html = ob_get_clean();

    wp_send_json_success([
        'cards_html' => $cards_html,
        'pagination_html' => $pagination_html,
        'total_shops' => $total_shops,
        'current_page' => $page,
        'total_pages' => $total_pages
    ]);
}

// Register AJAX endpoints
// Register AJAX actions for gallery studio data
add_action('wp_ajax_get_gallery_studios', 'ajax_get_gallery_studios');
add_action('wp_ajax_nopriv_get_gallery_studios', 'ajax_get_gallery_studios');

add_action('wp_ajax_studio_search', 'ajax_studio_search');
add_action('wp_ajax_nopriv_studio_search', 'ajax_studio_search');

/**
 * Secret Studio Recruitment Management
 */
function studio_secret_admin_menu() {
    add_options_page(
        'シークレット募集設定',
        'シークレット募集設定',
        'manage_options',
        'studio-secret-settings',
        'studio_secret_settings_page'
    );
}
add_action('admin_menu', 'studio_secret_admin_menu');

// 申し込み管理メニューの追加
function studio_applications_admin_menu() {
    add_management_page(
        '申し込み管理',
        '申し込み管理',
        'manage_options',
        'studio-applications',
        'studio_applications_page'
    );
}
add_action('admin_menu', 'studio_applications_admin_menu');

function studio_applications_page() {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }

    // 申し込みデータを取得（プラグインからのデータ）
    $submissions = get_option('siaes_submissions', []);

    // フィルタ処理
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $filtered_submissions = [];

    foreach ($submissions as $submission) {
        $source_type = isset($submission['source_type']) ? $submission['source_type'] : 'regular';

        if ($filter === 'all' ||
            ($filter === 'secret' && $source_type === 'secret') ||
            ($filter === 'regular' && $source_type === 'regular')) {
            $filtered_submissions[] = $submission;
        }
    }

    ?>
    <div class="wrap">
        <h1>申し込み管理</h1>

        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="filter" onchange="location.href='<?php echo admin_url('tools.php?page=studio-applications'); ?>&filter=' + this.value;">
                    <option value="all" <?php selected($filter, 'all'); ?>>すべて表示</option>
                    <option value="regular" <?php selected($filter, 'regular'); ?>>通常ページ</option>
                    <option value="secret" <?php selected($filter, 'secret'); ?>>シークレットページ</option>
                </select>
            </div>
        </div>

        <?php if (!empty($filtered_submissions)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>申し込み日時</th>
                        <th>お名前</th>
                        <th>メールアドレス</th>
                        <th>種別</th>
                        <th>ページ</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($filtered_submissions) as $index => $submission):
                        $source_type = isset($submission['source_type']) ? $submission['source_type'] : 'regular';
                        $page_slug = isset($submission['page_slug']) ? $submission['page_slug'] : 'unknown';
                    ?>
                        <tr>
                            <td><?php echo esc_html($submission['timestamp'] ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($submission['contact_name'] ?? $submission['name'] ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($submission['email_address'] ?? $submission['email'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($source_type === 'secret'): ?>
                                    <span class="secret-badge">🔐 シークレット</span>
                                <?php else: ?>
                                    <span class="regular-badge">通常</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($page_slug); ?></td>
                            <td>
                                <button type="button" class="button" onclick="showSubmissionDetails(<?php echo $index; ?>)">詳細表示</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>申し込みデータがありません。</p>
        <?php endif; ?>

        <!-- 詳細表示モーダル -->
        <div id="submission-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
            <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; max-width:600px; max-height:80%; overflow-y:auto; border-radius:5px;">
                <h3>申し込み詳細</h3>
                <div id="submission-details"></div>
                <button type="button" class="button" onclick="document.getElementById('submission-modal').style.display='none'">閉じる</button>
            </div>
        </div>

        <script>
        const submissions = <?php echo json_encode(array_values($filtered_submissions)); ?>;

        function showSubmissionDetails(index) {
            const submission = submissions[index];
            let details = '<table class="form-table">';

            for (const [key, value] of Object.entries(submission)) {
                if (key !== 'timestamp' && value && value !== '') {
                    const label = getFieldLabel(key);
                    details += `<tr><th>${label}</th><td>${escapeHtml(value)}</td></tr>`;
                }
            }

            details += '</table>';
            document.getElementById('submission-details').innerHTML = details;
            document.getElementById('submission-modal').style.display = 'block';
        }

        function getFieldLabel(key) {
            const labels = {
                'contact_name': 'お名前',
                'contact_kana': 'フリガナ',
                'company_name': '法人名',
                'phone_number': '電話番号',
                'email_address': 'メールアドレス',
                'website_url': 'WEBサイト',
                'inquiry_details': 'お問い合わせ内容',
                'source_type': '申し込み種別',
                'page_slug': 'ページ'
            };
            return labels[key] || key;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        </script>

        <style>
        .secret-badge {
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .regular-badge {
            background: #95a5a6;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        </style>
    </div>
    <?php
}

function studio_secret_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }

    // 設定保存処理
    if ($_POST && isset($_POST['studio_secret_nonce']) && wp_verify_nonce($_POST['studio_secret_nonce'], 'studio_secret_settings')) {
        if (isset($_POST['studio_secret_password']) && !empty($_POST['studio_secret_password'])) {
            update_option('studio_secret_password', sanitize_text_field($_POST['studio_secret_password']));
            echo '<div class="notice notice-success"><p>パスワードを更新しました。</p></div>';
        }

        if (isset($_POST['clear_access_log'])) {
            delete_option('studio_secret_access_log');
            echo '<div class="notice notice-success"><p>アクセスログをクリアしました。</p></div>';
        }
    }

    $current_password = get_option('studio_secret_password', 'recruit2024special');
    $access_log = get_option('studio_secret_access_log', []);
    ?>
    <div class="wrap">
        <h1>シークレット募集ページ設定</h1>

        <form method="post" action="">
            <?php wp_nonce_field('studio_secret_settings', 'studio_secret_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">アクセスパスワード</th>
                    <td>
                        <input type="text" name="studio_secret_password" value="<?php echo esc_attr($current_password); ?>" class="regular-text">
                        <p class="description">シークレットページにアクセスするためのパスワードです。</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('設定を保存'); ?>
        </form>

        <h2>アクセス情報</h2>
        <p><strong>シークレットページURL:</strong> <code><?php echo home_url('/studio-recruitment-secret/'); ?></code></p>

        <h3>アクセスログ</h3>
        <?php if (!empty($access_log)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>日時</th>
                        <th>IPアドレス</th>
                        <th>ステータス</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 最新10件のログを表示
                    $recent_logs = array_slice(array_reverse($access_log), 0, 10);
                    foreach ($recent_logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log['timestamp']); ?></td>
                            <td><?php echo esc_html($log['ip_address']); ?></td>
                            <td>
                                <span class="<?php echo $log['status'] === 'success' ? 'success' : 'failed'; ?>">
                                    <?php echo $log['status'] === 'success' ? '✓ 成功' : '✗ 失敗'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <form method="post" action="" style="margin-top: 20px;">
                <?php wp_nonce_field('studio_secret_settings', 'studio_secret_nonce'); ?>
                <input type="hidden" name="clear_access_log" value="1">
                <?php submit_button('アクセスログをクリア', 'delete', '', false, array('onclick' => 'return confirm("アクセスログをクリアしてもよろしいですか？");')); ?>
            </form>
        <?php else: ?>
            <p>アクセスログがありません。</p>
        <?php endif; ?>

        <style>
        .success { color: #46b450; font-weight: bold; }
        .failed { color: #dc3232; font-weight: bold; }
        </style>
    </div>
    <?php
}

// Enqueue styles and scripts
function theme_678studio_styles() {
    // Enqueue Google Fonts - Noto Sans JP with multiple weights including 500
    wp_enqueue_style('google-fonts',
        'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&family=Noto+Serif+JP:wght@300;400;500;600;700&display=swap',
        [], null);

    // Use filemtime for cache busting in development
    $css_file = get_stylesheet_directory() . '/style.css';
    $version = WP_DEBUG && file_exists($css_file) ? filemtime($css_file) : '1.0.0';

    wp_enqueue_style('678studio-style', get_stylesheet_uri(), ['google-fonts'], $version);
    
    // Enqueue debug scripts first (only on frontend)
    if (!is_admin()) {
        if (WP_DEBUG || (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG)) {
            wp_enqueue_script('wp-debug-logger',
                get_template_directory_uri() . '/assets/js/debug-logger.js',
                ['jquery'], '1.0.0', true);

            wp_localize_script('wp-debug-logger', 'wpDebugAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_debug_nonce')
            ]);
        }

        $header_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/header.js') : '1.0.0';
        wp_enqueue_script('678studio-header',
            get_template_directory_uri() . '/assets/js/header.js',
            [], $header_version, true);

        // Viewport Controller (global)
        $viewport_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/viewport-controller.js') : '1.0.0';
        wp_enqueue_script('678studio-viewport-controller',
            get_template_directory_uri() . '/assets/js/viewport-controller.js',
            [], $viewport_version, true);

        // Navigation Scripts - PC and Mobile separated (global)
        $desktop_nav_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/navigation-desktop.js') : '1.0.0';
        $desktop_deps = (WP_DEBUG || (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG)) ? ['wp-debug-logger'] : [];
        wp_enqueue_script('678studio-navigation-desktop',
            get_template_directory_uri() . '/assets/js/navigation-desktop.js',
            $desktop_deps, $desktop_nav_version, true);

        $mobile_nav_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/navigation-mobile.js') : '1.0.0';
        $mobile_deps = (WP_DEBUG || (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG)) ? ['wp-debug-logger'] : [];
        wp_enqueue_script('678studio-navigation-mobile',
            get_template_directory_uri() . '/assets/js/navigation-mobile.js',
            $mobile_deps, $mobile_nav_version, true);

        // Page Transitions Script (global)
        $transitions_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/modules/page-transitions.js') : '1.0.0';
        wp_enqueue_script('page-transitions', 
            get_template_directory_uri() . '/assets/js/modules/page-transitions.js', 
            [], $transitions_version, true);
    }
    
    // Enqueue gallery script on gallery pages
    if (is_page_template('page-photo-gallery.php')) {
        // ギャラリーページでは軽量化のためGSAPとScrollTriggerを無効化
        // GSAPを読み込まない = スクロールアニメーションも無効化される
        
        $js_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/gallery.js') : '1.0.0';
        wp_enqueue_script('678studio-gallery', 
            get_template_directory_uri() . '/assets/js/gallery.js', 
            [], $js_version, true);
    }

    // Enqueue Splide.js for studio detail pages
    if (is_page_template('page-studio-detail.php')) {
        // Splide CSS
        wp_enqueue_style(
            'splide-css',
            'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css',
            array(),
            '4.1.4'
        );

        // Splide JS
        wp_enqueue_script(
            'splide-js',
            'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js',
            array(),
            '4.1.4',
            true
        );

        // Custom studio slider initialization
        wp_enqueue_script(
            'studio-slider-js',
            get_template_directory_uri() . '/assets/js/studio-slider.js',
            array('splide-js'),
            $js_version,
            true
        );
    }

    // Enqueue GSAP and media slider on front page
    if (is_front_page() || is_home()) {
        // GSAP Core
        wp_enqueue_script('gsap', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', 
            [], '3.12.2', true);
        
        // GSAP ScrollTrigger Plugin
        wp_enqueue_script('gsap-scrolltrigger', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js', 
            ['gsap'], '3.12.2', true);
        
        // GSAP Draggable Plugin
        wp_enqueue_script('gsap-draggable', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/Draggable.min.js', 
            ['gsap'], '3.12.2', true);
        
        // Media Slider Script
        $slider_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/modules/media-slider.js') : '1.0.0';
        wp_enqueue_script('media-slider', 
            get_template_directory_uri() . '/assets/js/modules/media-slider.js', 
            ['gsap', 'gsap-draggable'], $slider_version, true);
        
        // Scroll Animations Script
        $scroll_animations_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/modules/scroll-animations.js') : '1.0.0';
        wp_enqueue_script('scroll-animations', 
            get_template_directory_uri() . '/assets/js/modules/scroll-animations.js', 
            ['gsap', 'gsap-scrolltrigger'], $scroll_animations_version, true);
    }
    
    // Enqueue GSAP and FAQ accordion on About page
    if (is_page('about') || is_page_template('page-about.php')) {
        // GSAP Core
        wp_enqueue_script('gsap', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', 
            [], '3.12.2', true);
        
        // GSAP ScrollTrigger Plugin
        wp_enqueue_script('gsap-scrolltrigger', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js', 
            ['gsap'], '3.12.2', true);
        
        // FAQ Accordion Script
        $faq_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/components/faq-accordion.js') : '1.0.0';
        wp_enqueue_script('faq-accordion', 
            get_template_directory_uri() . '/assets/js/components/faq-accordion.js', 
            ['gsap'], $faq_version, true);
        
        // Scroll Animations Script
        $scroll_animations_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/modules/scroll-animations.js') : '1.0.0';
        wp_enqueue_script('scroll-animations', 
            get_template_directory_uri() . '/assets/js/modules/scroll-animations.js', 
            ['gsap', 'gsap-scrolltrigger'], $scroll_animations_version, true);
    }
    
    // Enqueue GSAP and gallery slider on studio detail pages
    if (is_page_template('page-studio-detail.php') || is_page('studio-detail')) {
        // GSAP Core
        wp_enqueue_script('gsap', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', 
            [], '3.12.2', true);
        
        // Gallery Slider Script
        $gallery_slider_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/gallery-slider.js') : '1.0.0';
        wp_enqueue_script('gallery-slider', 
            get_template_directory_uri() . '/assets/js/gallery-slider.js', 
            ['gsap'], $gallery_slider_version, true);
    }
}
add_action('wp_enqueue_scripts', 'theme_678studio_styles');

// Enqueue additional scripts
function theme_678studio_additional_scripts() {
    // Always localize gallery AJAX data for gallery pages
    if (is_page_template('page-photo-gallery.php') || is_page('photo-gallery')) {
        wp_localize_script('jquery', 'galleryAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gallery_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'theme_678studio_additional_scripts');

// Theme support
// add_theme_support('title-tag'); // SEOマネージャーで管理するためコメントアウト
add_theme_support('post-thumbnails');
add_theme_support('menus');

// Development: Disable caching for faster development
if (WP_DEBUG) {
    // Disable WordPress caching
    if (!defined('WP_CACHE')) {
        define('WP_CACHE', false);
    }
    
    // Add no-cache headers
    add_action('wp_head', function() {
        echo '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">' . "\n";
        echo '<meta http-equiv="Pragma" content="no-cache">' . "\n";
        echo '<meta http-equiv="Expires" content="0">' . "\n";
    });
    
    // Disable query string cache busting for WordPress core
    add_filter('script_loader_src', 'remove_script_version', 15, 1);
    add_filter('style_loader_src', 'remove_script_version', 15, 1);
}

// Helper function to remove WordPress core version strings
function remove_script_version($src) {
    // Only remove version from WordPress core files, not our theme files
    if (strpos($src, '678studio-style') === false) {
        $parts = explode('?ver', $src);
        return $parts[0];
    }
    return $src;
}

// Register navigation menu
function theme_678studio_menus() {
    register_nav_menus(array(
        'header' => 'Header Menu'
    ));
}
add_action('init', 'theme_678studio_menus');

// AJAX handler for JavaScript debug logs
add_action('wp_ajax_wp_debug_log_js', 'handle_js_debug_logs');
add_action('wp_ajax_nopriv_wp_debug_log_js', 'handle_js_debug_logs');

function handle_js_debug_logs() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'wp_debug_nonce')) {
        wp_die('Security check failed');
    }
    
    $logs = json_decode(stripslashes($_POST['logs']), true);
    
    if (!is_array($logs)) {
        wp_send_json_error('Invalid log data');
        return;
    }
    
    // Create log directory if it doesn't exist
    $log_dir = WP_CONTENT_DIR . '/debug-logs/';
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
    
    $log_file = $log_dir . 'js-debug-' . date('Y-m-d') . '.log';
    
    foreach ($logs as $log) {
        if (is_array($log)) {
            $formatted_log = json_encode($log, JSON_UNESCAPED_UNICODE) . "\n";
            file_put_contents($log_file, $formatted_log, FILE_APPEND | LOCK_EX);
        }
    }
    
    wp_send_json_success('Logs saved successfully');
}

// WordPress debug integration hooks
add_action('wp_loaded', function() {
    wp_log_info('WordPress fully loaded', [
        'theme' => get_template(),
        'active_plugins' => get_option('active_plugins'),
        'user_count' => count_users()['total_users']
    ]);
});

add_action('template_redirect', function() {
    global $template;
    if (isset($template)) {
        WordPressDebugLogger::getInstance()->trackTemplate($template, 'main');
    }
});

// Track WordPress errors (removed problematic filter)
// Note: wp_die_handler filter removed to prevent fatal error

// Track slow queries
add_filter('log_query_custom_data', function($query_data, $query) {
    if (isset($query_data['query_time']) && $query_data['query_time'] > 0.1) {
        wp_log_warn('Slow database query detected', [
            'query' => $query,
            'execution_time' => $query_data['query_time']
        ]);
    }
    return $query_data;
}, 10, 2);

// Track user actions
add_action('wp_login', function($user_login, $user) {
    wp_log_info('User login', [
        'user_login' => $user_login,
        'user_id' => $user->ID,
        'user_roles' => $user->roles
    ]);
}, 10, 2);

add_action('wp_logout', function($user_id) {
    wp_log_info('User logout', [
        'user_id' => $user_id
    ]);
});

// ================================
// GALLERY FUNCTIONALITY
// ================================

// === Language Translations ===
function get_gallery_translations($lang = 'en') {
    $translations = [
        'en' => [
            'page_title' => 'FTP Gallery Upload',
            'menu_title' => 'Gallery Upload',
            'category_error' => 'Please enter or select a category.',
            'ftp_error' => 'FTP connection/login failed.',
            'folder_error' => 'Failed to create folder: %s',
            'upload_success' => '%d images uploaded successfully!',
            'upload_error' => 'Upload failed: %s',
            'no_images' => 'No images selected.',
            'select_category' => 'Select Category (Existing):',
            'new_category' => 'Enter New Category (Optional):',
            'select_images' => 'Select Images:',
            'upload_button' => 'Upload Images',
            'placeholder_category' => 'Example: summer-2025',
            'gallery_title' => 'Gallery Categories',
            'found_categories' => 'Found %d categories',
            'found_files' => 'Found %d files in %s',
            'skip_non_image' => 'Skipping non-image: %s',
            'no_files' => 'No files found in %s. Time: %s',
            'ftp_failed' => 'FTP connection failed. Check internet or FTP details in functions.php. Time: %s',
            'select_language' => 'Select Language:'
        ],
        'ja' => [
            'page_title' => 'FTPギャラリーアップロード',
            'menu_title' => 'ギャラリーアップロード',
            'category_error' => 'カテゴリーを入力または選択してください。',
            'ftp_error' => 'FTP接続/ログインに失敗しました。',
            'folder_error' => 'フォルダの作成に失敗しました: %s',
            'upload_success' => '%d枚の画像が正常にアップロードされました！',
            'upload_error' => 'アップロードに失敗しました: %s',
            'no_images' => '画像が選択されていません。',
            'select_category' => 'カテゴリーを選択（既存）:',
            'new_category' => '新しいカテゴリーを入力（任意）:',
            'select_images' => '画像を選択:',
            'upload_button' => '画像をアップロード',
            'placeholder_category' => '例: summer-2025',
            'gallery_title' => 'ギャラリーカテゴリー',
            'found_categories' => '%d個のカテゴリーが見つかりました',
            'found_files' => '%sに%d個のファイルが見つかりました',
            'skip_non_image' => '画像でないファイルをスキップ: %s',
            'no_files' => '%sにファイルが見つかりませんでした。時間: %s',
            'ftp_failed' => 'FTP接続に失敗しました。functions.phpのインターネットまたはFTP詳細を確認してください。時間: %s',
            'select_language' => '言語を選択:'
        ]
    ];
    return $translations[$lang] ?? $translations['en'];
}

// === Gallery FTP Configuration ===
// Define these constants in wp-config.php for production
if (!defined('GALLERY_FTP_HOST')) {
    define('GALLERY_FTP_HOST', 'example.com');
}
if (!defined('GALLERY_FTP_USER')) {
    define('GALLERY_FTP_USER', 'username');
}
if (!defined('GALLERY_FTP_PASS')) {
    define('GALLERY_FTP_PASS', 'password');
}
if (!defined('GALLERY_BASE_PATH')) {
    define('GALLERY_BASE_PATH', '/public_html/gallery/');
}
if (!defined('GALLERY_BASE_URL')) {
    define('GALLERY_BASE_URL', 'https://example.com/gallery/');
}

// === Gallery Admin Menu removed ===
// Gallery Upload menu has been removed - functionality moved to Studio Shops Manager plugin

// === Gallery Admin Upload Page ===
function ftp_gallery_upload_page() {
    // Get or save selected language
    $lang = isset($_POST['gallery_language']) ? sanitize_text_field($_POST['gallery_language']) : get_option('ftp_gallery_language', 'en');
    if (isset($_POST['gallery_language']) && check_admin_referer('ftp_gallery_nonce')) {
        update_option('ftp_gallery_language', $lang);
    }
    $translations = get_gallery_translations($lang);

    $message = '';
    if (isset($_POST['ftp_gallery_submit']) && check_admin_referer('ftp_gallery_nonce')) {
        $category = sanitize_text_field($_POST['gallery_category']);
        $new_category = sanitize_text_field($_POST['new_category']);
        $category = !empty($new_category) ? $new_category : $category;
        
        if (empty($category)) {
            echo '<div class="error"><p>' . esc_html($translations['category_error']) . '</p></div>';
            return;
        }

        $category = preg_replace('/[^A-Za-z0-9-_]/', '', str_replace(' ', '-', strtolower($category)));

        $ftp = ftp_connect(GALLERY_FTP_HOST);
        if (!$ftp || !ftp_login($ftp, GALLERY_FTP_USER, GALLERY_FTP_PASS)) {
            echo '<div class="error"><p>' . esc_html($translations['ftp_error']) . '</p></div>';
            return;
        }
        ftp_pasv($ftp, true);

        $remote_path = GALLERY_BASE_PATH . $category;
        $folders = explode('/', $remote_path);
        $path = '';
        foreach ($folders as $folder) {
            if (!$folder) continue;
            $path .= '/' . $folder;
            if (!@ftp_chdir($ftp, $path)) {
                if (!ftp_mkdir($ftp, $path)) {
                    echo '<div class="error"><p>' . sprintf(esc_html($translations['folder_error']), esc_html($path)) . '</p></div>';
                    ftp_close($ftp);
                    return;
                }
            }
        }

        if (!empty($_FILES['gallery_images']['name'][0])) {
            $success = 0;
            foreach ($_FILES['gallery_images']['name'] as $key => $name) {
                if ($_FILES['gallery_images']['error'][$key] === 0) {
                    $tmp_name = $_FILES['gallery_images']['tmp_name'][$key];
                    $remote_file = $remote_path . '/' . basename($name);
                    if (ftp_put($ftp, $remote_file, $tmp_name, FTP_BINARY)) {
                        $success++;
                    } else {
                        echo '<div class="error"><p>' . sprintf(esc_html($translations['upload_error']), esc_html($name)) . '</p></div>';
                    }
                }
            }
            echo '<div class="updated"><p>' . sprintf(esc_html($translations['upload_success']), $success) . '</p></div>';
        } else {
            echo '<div class="error"><p>' . esc_html($translations['no_images']) . '</p></div>';
        }
        ftp_close($ftp);
    }

    // Fetch existing categories from FTP
    $ftp = ftp_connect(GALLERY_FTP_HOST);
    $existing_categories = [];
    if ($ftp && ftp_login($ftp, GALLERY_FTP_USER, GALLERY_FTP_PASS)) {
        ftp_pasv($ftp, true);
        $items = ftp_nlist($ftp, GALLERY_BASE_PATH);
        if ($items) {
            foreach ($items as $item) {
                $name = basename($item);
                if ($name !== '.' && $name !== '..') {
                    $existing_categories[] = $name;
                }
            }
        }
        ftp_close($ftp);
    }

    ?>
<div class="wrap">
  <h1><?php echo esc_html($translations['page_title']); ?></h1>
  <?php echo $message; ?>
  <form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('ftp_gallery_nonce'); ?>
    <p>
      <label><?php echo esc_html($translations['select_language']); ?></label><br>
      <select name="gallery_language" style="width:300px;" onchange="this.form.submit()">
        <option value="en" <?php selected($lang, 'en'); ?>>English</option>
        <option value="ja" <?php selected($lang, 'ja'); ?>>日本語 (Japanese)</option>
      </select>
    </p>
    <p>
      <label><?php echo esc_html($translations['select_category']); ?></label><br>
      <select name="gallery_category" style="width:300px;">
        <option value=""><?php echo esc_html($translations['select_category']); ?></option>
        <?php foreach ($existing_categories as $cat): ?>
        <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label><?php echo esc_html($translations['new_category']); ?></label><br>
      <input type="text" name="new_category"
        placeholder="<?php echo esc_attr($translations['placeholder_category']); ?>" style="width:300px;">
    </p>
    <p>
      <label><?php echo esc_html($translations['select_images']); ?></label><br>
      <input type="file" name="gallery_images[]" multiple accept="image/*" required>
    </p>
    <p>
      <input type="submit" name="ftp_gallery_submit" class="button button-primary"
        value="<?php echo esc_attr($translations['upload_button']); ?>">
    </p>
  </form>
</div>
<?php
}

// === Helper: Fetch Category List from FTP ===
function get_gallery_categories() {
    $ftp = ftp_connect(GALLERY_FTP_HOST);
    if (!$ftp || !ftp_login($ftp, GALLERY_FTP_USER, GALLERY_FTP_PASS)) return [];
    ftp_pasv($ftp, true);
    $items = ftp_nlist($ftp, GALLERY_BASE_PATH);
    ftp_close($ftp);

    $categories = [];
    if ($items) {
        foreach ($items as $item) {
            $name = basename($item);
            if ($name !== '.' && $name !== '..') {
                $categories[] = $name;
            }
        }
    }
    return $categories;
}

// === Gallery Shortcode ===
add_shortcode('ftp_gallery', function($atts) {
    $atts = shortcode_atts(['category' => '', 'limit' => ''], $atts);
    
    $ftp = ftp_connect(GALLERY_FTP_HOST);
    if (!$ftp || !ftp_login($ftp, GALLERY_FTP_USER, GALLERY_FTP_PASS)) {
        return '<p style="color:red;">FTP connection failed</p>';
    }
    ftp_pasv($ftp, true);
    
    $categories = ftp_nlist($ftp, GALLERY_BASE_PATH);
    $images_by_category = [];
    
    if ($categories) {
        foreach ($categories as $category_path) {
            $category = basename($category_path);
            if (in_array($category, ['.', '..'])) continue;
            
            if ($atts['category'] && $atts['category'] !== $category) continue;
            
            $files = ftp_nlist($ftp, $category_path);
            if ($files && is_array($files)) {
                $images = [];
                foreach ($files as $file) {
                    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                        $image_name = basename($file);
                        $img_url = GALLERY_BASE_URL . $category . '/' . $image_name;
                        $images[] = ['url' => $img_url, 'name' => $image_name];
                    }
                }
                if (!empty($images)) {
                    if ($atts['limit']) {
                        $images = array_slice($images, 0, intval($atts['limit']));
                    }
                    $images_by_category[$category] = $images;
                }
            }
        }
    }
    ftp_close($ftp);
    
    ob_start();
    ?>
<div class="gallery-shortcode">
  <?php foreach ($images_by_category as $category => $images): ?>
  <div class="gallery-category">
    <h3><?php echo esc_html($category); ?></h3>
    <div class="gallery-grid">
      <?php foreach ($images as $image): ?>
      <div class="gallery-item">
        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['name']); ?>">
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php
    return ob_get_clean();
});

// Initialize debug logger
add_action('init', function() {
    wp_log_info('WordPress debug logger initialized');
    wp_log_debug('FAQ Debug Test', ['message' => 'Debug system is working', 'timestamp' => current_time('mysql')]);
});

// ================================
// SEO & META MANAGEMENT SYSTEM
// ================================

/**
 * 678スタジオ SEO管理システム
 * 全ページのSEO情報を統一管理
 */
class StudioSEOManager {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // WordPressの標準titleタグ機能を無効化
        remove_theme_support('title-tag');
        add_filter('wp_title', '__return_false', 10, 3);
        add_filter('pre_get_document_title', '__return_false');
        add_action('wp_head', [$this, 'outputSEOTags'], 1);
    }
    
    /**
     * ページタイプに応じたSEO情報を取得
     */
    public function getSEOData() {
        $seo_data = [
            'title' => '',
            'description' => '',
            'keywords' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'canonical' => '',
            'noindex' => false
        ];
        
        // 基本的なサイト情報
        $site_name = 'ロクナナハチ(678)';
        $site_description = '60代、70代、80代の方々のための記念撮影サービス。還暦、喜寿、米寿のお祝い、遺影撮影、家族写真など人生の大切な瞬間を美しく残します。';
        $base_keywords = '記念撮影,還暦祝い,喜寿祝い,米寿祝い,遺影撮影,60代,70代,80代,家族写真,写真館,シニア撮影';
        $default_og_image = get_template_directory_uri() . '/assets/images/og-image.jpg';
        
        if (is_front_page() || is_home()) {
            // トップページ
            $seo_data['title'] = $site_name . ' | シニア世代のための記念撮影・写真館サービス';
            $seo_data['description'] = $site_description;
            $seo_data['keywords'] = $base_keywords;
            $seo_data['og_type'] = 'website';
            
        } elseif (is_page('about')) {
            // Aboutページ
            $seo_data['title'] = 'ロクナナハチについて | シニア向け撮影サービス・プラン | ' . $site_name;
            $seo_data['description'] = 'ロクナナハチ(678)のサービス内容をご紹介。還暦・喜寿・米寿のお祝い撮影、遺影撮影、家族写真撮影など、シニア世代に寄り添った撮影プランをご用意しています。';
            $seo_data['keywords'] = $base_keywords . ',撮影プラン,サービス内容,料金,シニア向け';
            
        } elseif (is_page('gallery')) {
            // ギャラリーページ
            $seo_data['title'] = 'ギャラリー | シニア撮影作品・事例紹介 | ' . $site_name;
            $seo_data['description'] = 'ロクナナハチ(678)の撮影作品・事例をご紹介。還暦、喜寿、米寿のお祝い撮影、遺影撮影、家族写真など様々なシーンでの撮影作品をご覧いただけます。';
            $seo_data['keywords'] = $base_keywords . ',作品,事例,ギャラリー,撮影実績';
            
        } elseif (is_page('stores')) {
            // 店舗一覧ページ
            $seo_data['title'] = '写真館検索・店舗一覧 | 全国の提携写真館 | ' . $site_name;
            $seo_data['description'] = 'ロクナナハチ(678)提携写真館の店舗一覧ページです。全国の提携写真館でシニア向け記念撮影サービスをご利用いただけます。お近くの店舗を検索してください。';
            $seo_data['keywords'] = $base_keywords . ',店舗検索,写真館一覧,全国対応';
            
        } elseif (is_page('studio-reservation')) {
            // 予約ページ
            $seo_data['title'] = 'ご予約 | シニア記念撮影のご予約・お申込み | ' . $site_name;
            $seo_data['description'] = 'ロクナナハチ(678)の撮影予約ページです。還暦、喜寿、米寿のお祝い撮影、遺影撮影、家族写真などシニア向け記念撮影のご予約を承っております。';
            $seo_data['keywords'] = $base_keywords . ',予約,申込み,撮影予約';
            
        } elseif (is_page('studio-inquiry')) {
            // 問合せページ
            $seo_data['title'] = 'お問合せ | ご質問・ご相談 | ' . $site_name;
            $seo_data['description'] = 'ロクナナハチ(678)へのお問合せページです。シニア向け撮影に関するご質問・ご相談はこちらからお気軽にお問合せください。';
            $seo_data['keywords'] = $base_keywords . ',問合せ,質問,相談';
            
        } elseif (is_page('privacy')) {
            // プライバシーポリシー
            $seo_data['title'] = 'プライバシーポリシー | ' . $site_name;
            $seo_data['description'] = 'ロクナナハチ(678)のプライバシーポリシーページです。個人情報の取扱いについて詳しく説明しています。';
            $seo_data['keywords'] = 'プライバシーポリシー,個人情報保護,利用規約';
            $seo_data['noindex'] = true; // 検索結果に表示しない
            
        } elseif (is_page_template('page-studio-detail.php')) {
            // 店舗詳細ページ（動的コンテンツ）
            $shop_data = $this->getStoreDetailSEO();
            if ($shop_data) {
                $seo_data['title'] = $shop_data['shop']['name'] . ' | 店舗詳細・アクセス | ' . $site_name;
                $seo_data['description'] = $shop_data['shop']['name'] . 'の店舗情報。住所：' . $shop_data['shop']['address'] . '、最寄り駅：' . $shop_data['shop']['nearest_station'] . '。678撮影サービスをご利用いただけます。';
                $seo_data['keywords'] = $base_keywords . ',' . $shop_data['shop']['name'] . ',店舗詳細,アクセス,' . $shop_data['shop']['nearest_station'];
                
                // 店舗固有のOG画像
                if (!empty($shop_data['shop']['image_urls'][0])) {
                    $seo_data['og_image'] = $shop_data['shop']['image_urls'][0];
                }
                
                $seo_data['og_title'] = $shop_data['shop']['name'] . ' | ' . $site_name;
                $seo_data['og_description'] = $shop_data['shop']['name'] . 'でロクナナハチ(678)のシニア向け記念撮影サービスをご利用いただけます。' . $shop_data['shop']['address'] . 'にてお待ちしております。';
            }
            
        } else {
            // その他のページ（デフォルト）
            $post_title = get_the_title();
            $seo_data['title'] = $post_title . ' | ' . $site_name;
            $seo_data['description'] = $site_description;
            $seo_data['keywords'] = $base_keywords;
        }
        
        // OG情報の設定
        $seo_data['og_title'] = $seo_data['og_title'] ?: $seo_data['title'];
        $seo_data['og_description'] = $seo_data['og_description'] ?: $seo_data['description'];
        $seo_data['og_image'] = $seo_data['og_image'] ?: $default_og_image;
        
        // カノニカルURL
        $seo_data['canonical'] = get_permalink();
        
        return apply_filters('studio_seo_data', $seo_data);
    }
    
    /**
     * 店舗詳細ページのSEOデータを取得
     */
    private function getStoreDetailSEO() {
        $shop_id = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : 0;
        if (!$shop_id) {
            return null;
        }
        
        // キャッシュから店舗データを取得
        $cache_key = 'studio_shop_' . $shop_id;
        $cached_shop = get_transient($cache_key);
        
        if ($cached_shop !== false) {
            return ['shop' => $cached_shop];
        }
        
        // キャッシュがない場合は全店舗データから検索
        $all_shops_data = get_cached_studio_data();
        if (isset($all_shops_data['shops']) && is_array($all_shops_data['shops'])) {
            foreach ($all_shops_data['shops'] as $shop) {
                if (isset($shop['id']) && intval($shop['id']) === $shop_id) {
                    // 個別キャッシュに保存
                    set_transient($cache_key, $shop, 300); // 5分キャッシュ
                    return ['shop' => $shop];
                }
            }
        }
        
        return null;
    }
    
    /**
     * SEOタグを出力
     */
    public function outputSEOTags() {
        $seo_data = $this->getSEOData();
        
        // タイトルタグ
        if ($seo_data['title']) {
            echo '<title>' . esc_html($seo_data['title']) . '</title>' . "\n";
        }
        
        // メタディスクリプション
        if ($seo_data['description']) {
            echo '<meta name="description" content="' . esc_attr($seo_data['description']) . '">' . "\n";
        }
        
        // メタキーワード
        if ($seo_data['keywords']) {
            echo '<meta name="keywords" content="' . esc_attr($seo_data['keywords']) . '">' . "\n";
        }
        
        // カノニカルURL
        if ($seo_data['canonical']) {
            echo '<link rel="canonical" href="' . esc_url($seo_data['canonical']) . '">' . "\n";
        }
        
        // noindex設定
        if ($seo_data['noindex']) {
            echo '<meta name="robots" content="noindex,nofollow">' . "\n";
        }
        
        // OGPタグ
        echo '<meta property="og:title" content="' . esc_attr($seo_data['og_title']) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($seo_data['og_description']) . '">' . "\n";
        echo '<meta property="og:type" content="' . esc_attr($seo_data['og_type']) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($seo_data['canonical']) . '">' . "\n";
        echo '<meta property="og:site_name" content="ロクナナハチ(678)">' . "\n";
        echo '<meta property="og:locale" content="ja_JP">' . "\n";
        
        if ($seo_data['og_image']) {
            echo '<meta property="og:image" content="' . esc_url($seo_data['og_image']) . '">' . "\n";
            echo '<meta property="og:image:width" content="1200">' . "\n";
            echo '<meta property="og:image:height" content="630">' . "\n";
        }
        
        // Twitter Cardタグ
        echo '<meta name="twitter:card" content="' . esc_attr($seo_data['twitter_card']) . '">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($seo_data['og_title']) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($seo_data['og_description']) . '">' . "\n";
        
        if ($seo_data['og_image']) {
            echo '<meta name="twitter:image" content="' . esc_url($seo_data['og_image']) . '">' . "\n";
        }
        
        // 構造化データ（JSON-LD）を出力
        $this->outputStructuredData();
    }
    
    /**
     * 構造化データ（JSON-LD）を出力
     */
    private function outputStructuredData() {
        $structured_data = [];
        
        if (is_front_page() || is_home()) {
            // トップページ - 企業/サービス情報
            $structured_data[] = [
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                'name' => 'ロクナナハチ(678)',
                'description' => '60代、70代、80代の方々のための記念撮影サービス。還暦、喜寿、米寿のお祝い、遺影撮影、家族写真など人生の大切な瞬間を美しく残します。',
                'url' => home_url(),
                'telephone' => '',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'JP'
                ],
                'serviceType' => ['シニア記念撮影', '還暦祝い撮影', '喜寿祝い撮影', '米寿祝い撮影', '遺影撮影', '家族写真'],
                'areaServed' => '全国',
                'priceRange' => '$$',
                'audience' => [
                    '@type' => 'Audience',
                    'audienceType' => 'シニア世代',
                    'suggestedMinAge' => 60
                ]
            ];
            
            // パンくずリスト
            $structured_data[] = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'TOP',
                        'item' => home_url()
                    ]
                ]
            ];
            
        } elseif (is_page('about')) {
            // Aboutページ - サービス情報
            $structured_data[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => 'ロクナナハチ(678)',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'ロクナナハチ(678)'
                ],
                'serviceType' => 'シニア向け記念撮影サービス',
                'description' => 'ロクナナハチ(678)のサービス内容をご紹介。還暦・喜寿・米寿のお祝い撮影、遺影撮影、家族写真撮影など、シニア世代に寄り添った撮影プランをご用意しています。',
                'audience' => [
                    '@type' => 'Audience',
                    'audienceType' => 'シニア世代',
                    'suggestedMinAge' => 60
                ]
            ];
            
        } elseif (is_page('gallery')) {
            // ギャラリーページ - 作品集
            $structured_data[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ImageGallery',
                'name' => 'ロクナナハチ(678) ギャラリー',
                'description' => 'ロクナナハチ(678)の撮影作品・事例をご紹介。還暦、喜寿、米寿のお祝い撮影、遺影撮影、家族写真など様々なシーンでの撮影作品をご覧いただけます。'
            ];
            
        } elseif (is_page('stores')) {
            // 店舗一覧ページ - 店舗検索
            $structured_data[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'ロクナナハチ(678) 提携写真館一覧',
                'description' => '全国の提携写真館でロクナナハチ(678)のシニア向け記念撮影サービスをご利用いただけます。'
            ];
            
        } elseif (is_page_template('page-studio-detail.php')) {
            // 店舗詳細ページ - 個別店舗情報
            $shop_data = $this->getStoreDetailSEO();
            if ($shop_data && isset($shop_data['shop'])) {
                $shop = $shop_data['shop'];
                $structured_data[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'LocalBusiness',
                    'name' => $shop['name'],
                    'description' => $shop['name'] . 'でロクナナハチ(678)のシニア向け記念撮影サービスをご利用いただけます。',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => $shop['address'] ?? '',
                        'addressCountry' => 'JP'
                    ],
                    'telephone' => $shop['phone'] ?? '',
                    'url' => get_permalink(),
                    'serviceType' => ['シニア記念撮影', '還暦祝い撮影', '遺影撮影'],
                    'openingHours' => $shop['business_hours'] ?? '',
                    'image' => !empty($shop['image_urls'][0]) ? $shop['image_urls'][0] : ''
                ];
            }
        }
        
        // パンくずリスト（共通）
        if (!is_front_page() && !is_home()) {
            $breadcrumb_items = [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'TOP',
                    'item' => home_url()
                ]
            ];
            
            // 現在のページ
            $breadcrumb_items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => get_the_title(),
                'item' => get_permalink()
            ];
            
            $structured_data[] = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumb_items
            ];
        }
        
        // JSON-LD出力
        if (!empty($structured_data)) {
            foreach ($structured_data as $data) {
                echo '<script type="application/ld+json">' . "\n";
                echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
                echo '</script>' . "\n";
            }
        }
    }
}

// SEOマネージャーを初期化
add_action('init', function() {
    StudioSEOManager::getInstance();
});

// ================================
// SITEMAP GENERATION
// ================================

/**
 * カスタムサイトマップ生成システム
 */
class StudioSitemapGenerator {
    
    public function __construct() {
        add_action('init', [$this, 'addRewriteRules']);
        add_action('template_redirect', [$this, 'handleSitemapRequest']);
        add_filter('query_vars', [$this, 'addQueryVars']);
    }
    
    public function addRewriteRules() {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?sitemap=1', 'top');
        add_rewrite_rule('^sitemap_index\.xml$', 'index.php?sitemap_index=1', 'top');
    }
    
    public function addQueryVars($vars) {
        $vars[] = 'sitemap';
        $vars[] = 'sitemap_index';
        return $vars;
    }
    
    public function handleSitemapRequest() {
        if (get_query_var('sitemap')) {
            $this->outputSitemap();
            exit;
        }
        
        if (get_query_var('sitemap_index')) {
            $this->outputSitemapIndex();
            exit;
        }
    }
    
    private function outputSitemapIndex() {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

  $sitemap_url = home_url('/sitemap.xml');
  $lastmod = date('Y-m-d\TH:i:s+00:00');

  echo '<sitemap>' . "\n";
    echo '<loc>' . esc_url($sitemap_url) . '</loc>' . "\n";
    echo '<lastmod>' . $lastmod . '</lastmod>' . "\n";
    echo '</sitemap>' . "\n";

  echo '</sitemapindex>' . "\n";
}

private function outputSitemap() {
header('Content-Type: application/xml; charset=utf-8');
echo '
<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

  // 主要ページ
  $pages = [
  ['url' => home_url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
  ['url' => home_url('/about'), 'priority' => '0.9', 'changefreq' => 'weekly'],
  ['url' => home_url('/photo-gallery'), 'priority' => '0.8', 'changefreq' => 'weekly'],
  ['url' => home_url('/stores'), 'priority' => '0.9', 'changefreq' => 'daily'],
  ['url' => home_url('/studio-reservation'), 'priority' => '0.7', 'changefreq' => 'monthly'],
  ['url' => home_url('/studio-inquiry'), 'priority' => '0.6', 'changefreq' => 'monthly']
  ];

  foreach ($pages as $page) {
  $this->outputUrlEntry($page['url'], $page['priority'], $page['changefreq']);
  }

  // 店舗詳細ページ（動的）
  $shops_data = get_cached_studio_data();
  if (isset($shops_data['shops']) && is_array($shops_data['shops'])) {
  foreach ($shops_data['shops'] as $shop) {
  if (isset($shop['id'])) {
  $shop_url = home_url('/studio-detail/?shop_id=' . $shop['id']);
  $this->outputUrlEntry($shop_url, '0.7', 'weekly');
  }
  }
  }

  echo '</urlset>' . "\n";
}

private function outputUrlEntry($url, $priority, $changefreq, $lastmod = null) {
if (!$lastmod) {
$lastmod = date('Y-m-d\TH:i:s+00:00');
}

echo '<url>' . "\n";
  echo '<loc>' . esc_url($url) . '</loc>' . "\n";
  echo '<lastmod>' . $lastmod . '</lastmod>' . "\n";
  echo '<changefreq>' . $changefreq . '</changefreq>' . "\n";
  echo '<priority>' . $priority . '</priority>' . "\n";
  echo '</url>' . "\n";
}
}

// サイトマップジェネレーターを初期化
new StudioSitemapGenerator();

// パーマリンク更新時にリライトルールをフラッシュ
add_action('wp_loaded', function() {
if (!get_option('studio_sitemap_flushed')) {
flush_rewrite_rules();
update_option('studio_sitemap_flushed', true);
}
});

// Load WP-CLI commands if WP-CLI is available
if (defined('WP_CLI') && WP_CLI) {
    $wp_cli_commands_file = get_template_directory() . '/inc/wp-cli-studio-commands.php';
    if (file_exists($wp_cli_commands_file)) {
        require_once $wp_cli_commands_file;
    }
}


function enqueue_reservation_script() {
    // デバッグ用：ページ情報をログ出力
    $page_id = get_the_ID();
    $page_slug = get_post_field('post_name', $page_id);
    $template = get_page_template_slug();
    $current_url = $_SERVER['REQUEST_URI'] ?? '';

    wp_log_debug('enqueue_reservation_script called', [
        'page_id' => $page_id,
        'page_slug' => $page_slug,
        'template' => $template,
        'current_url' => $current_url,
        'is_page_studio_reservation' => is_page('studio-reservation'),
        'is_page_207' => is_page(207),
        'slug_match' => ($page_slug === 'studio-reservation'),
        'url_contains' => (strpos($current_url, 'studio-reservation') !== false)
    ]);

    // より確実な条件判定：複数の条件をチェック
    $is_reservation_page = is_page('studio-reservation') ||
                          is_page(207) ||
                          $page_slug === 'studio-reservation' ||
                          is_page_template('page-studio-reservation.php') ||
                          (strpos($current_url, 'studio-reservation') !== false);

    if ($is_reservation_page) {
        wp_log_info('Reservation page detected - loading scripts', [
            'page_id' => $page_id,
            'page_slug' => $page_slug,
            'template' => $template
        ]);

        // 店舗選択用のスクリプト
        wp_enqueue_script(
            'reservation-script',
            get_template_directory_uri() . '/assets/js/reservation.js',
            array(), // Add dependencies if needed
            WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/reservation.js') : '1.0.0',
            true // Load in footer
        );

        // フォーム確認画面用のスクリプト (依存関係なしで先に読み込み)
        wp_enqueue_script(
            'reservation-form-script',
            get_template_directory_uri() . '/assets/js/reservation-form.js',
            array(), // 依存関係を削除
            WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/reservation-form.js') : '1.0.0',
            true // Load in footer
        );

        wp_log_debug('Scripts enqueued successfully', [
            'reservation_script' => 'loaded',
            'reservation_form_script' => 'loaded'
        ]);

        // AJAX settings are now handled by override_plugin_form_handler_on_reservation function
    } else {
        wp_log_debug('Not a reservation page - scripts not loaded', [
            'page_id' => $page_id,
            'page_slug' => $page_slug,
            'url' => $current_url
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_reservation_script', 10);

// Enqueue security helpers for all form pages
function enqueue_security_helpers() {
    if (is_page('studio-reservation') || is_page('studio-inquiry') || is_page('studio-recruitment') || is_page('corporate-inquiry')) {
        wp_enqueue_script(
            'security-helpers',
            get_template_directory_uri() . '/assets/js/security-helpers.js',
            array('jquery'),
            WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/security-helpers.js') : '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_security_helpers');

// Additional security headers for theme
function theme_security_headers() {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
add_action('send_headers', 'theme_security_headers');
add_action('wp_head', 'theme_security_headers', 1);

// Override plugin's form-handler.js to prevent conflicts on specific pages
function override_plugin_form_handler_conflicts() {
    if (is_page('studio-reservation') || is_page('studio-inquiry') || is_page('studio-recruitment') || is_page('corporate-inquiry')) {
        // For these pages: completely override with empty script (use theme's custom form handlers)
        wp_deregister_script('siaes-form-handler');
        wp_register_script('siaes-form-handler', get_template_directory_uri() . '/assets/js/empty-form-handler.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('siaes-form-handler');

        // Re-add the AJAX configuration that the plugin would normally add
        wp_localize_script('siaes-form-handler', 'siaes_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('siaes_form_nonce'),
            'page_id' => get_the_ID(),
            'api_url' => 'https://678photo.com/api/get_all_studio_shop.php',
            'is_user_logged_in' => is_user_logged_in() ? 1 : 0
        ));
    }
}
add_action('wp_enqueue_scripts', 'override_plugin_form_handler_conflicts', 999);
function enqueue_inquiry_script() {
// Check if the current page slug is 'studio-inquiry'
if (is_page('studio-inquiry')) {
// 店舗選択用のスクリプト
wp_enqueue_script(
'inquiry-script',
get_template_directory_uri() . '/assets/js/inquiry.js',
array(), // Add dependencies if needed
WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/inquiry.js') : '1.0.0',
true // Load in footer
);

// フォーム処理用のスクリプト（日本語メッセージ対応）
wp_enqueue_script(
'inquiry-form-script',
get_template_directory_uri() . '/assets/js/inquiry-form.js',
array(), // Add dependencies if needed
WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/inquiry-form.js') : '1.0.0',
true // Load in footer
);
}
}
add_action('wp_enqueue_scripts', 'enqueue_inquiry_script');

function enqueue_recruitment_script() {
// Check if the current page slug is 'studio-recruitment' or if using secret template
if (is_page('studio-recruitment') || is_page_template('page-studio-recruitment-secret.php')) {
// フォーム処理用のスクリプト（日本語メッセージ対応）
wp_enqueue_script(
'recruitment-form-script',
get_template_directory_uri() . '/assets/js/recruitment-form.js',
array(), // Add dependencies if needed
WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/recruitment-form.js') : '1.0.0',
true // Load in footer
);
}
}
add_action('wp_enqueue_scripts', 'enqueue_recruitment_script');

// Corporate Inquiry form script
function enqueue_corporate_inquiry_script() {
// Check if the current page slug is 'corporate-inquiry'
if (is_page('corporate-inquiry')) {
// フォーム処理用のスクリプト（日本語メッセージ対応）
wp_enqueue_script(
'corporate-inquiry-form-script',
get_template_directory_uri() . '/assets/js/corporate-inquiry-form.js',
array(), // Add dependencies if needed
WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/corporate-inquiry-form.js') : '1.0.0',
true // Load in footer
);
}
}
add_action('wp_enqueue_scripts', 'enqueue_corporate_inquiry_script');

// SEO Articles Custom Post Type
require_once get_template_directory() . '/inc/post-types/seo-articles.php';

/**
 * Fix Gutenberg block editor dependencies
 */
function fix_gutenberg_dependencies() {
    if ( ! is_admin() ) {
        return;
    }
    
    global $pagenow, $typenow;
    
    // すべての投稿タイプの編集画面でGutenbergの依存関係を修正
    if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
        // 必要なGutenbergのスクリプトを正しい順序で読み込む
        wp_enqueue_script( 'wp-polyfill' );
        wp_enqueue_script( 'wp-element' );
        wp_enqueue_script( 'wp-i18n' );
        wp_enqueue_script( 'wp-hooks' );
        wp_enqueue_script( 'wp-api-fetch' );
        wp_enqueue_script( 'wp-data' );
        wp_enqueue_script( 'wp-compose' );
        wp_enqueue_script( 'wp-components' );
        wp_enqueue_script( 'wp-blocks' );
        wp_enqueue_script( 'wp-block-library' );
        wp_enqueue_script( 'wp-editor' );
        wp_enqueue_script( 'wp-edit-post' );
        wp_enqueue_script( 'wp-format-library' );
        wp_enqueue_script( 'wp-viewport' );
        
        // Gutenbergのスタイルも読み込む
        wp_enqueue_style( 'wp-block-library' );
        wp_enqueue_style( 'wp-block-library-theme' );
        wp_enqueue_style( 'wp-edit-blocks' );
        wp_enqueue_style( 'wp-editor' );
        wp_enqueue_style( 'wp-edit-post' );
        wp_enqueue_style( 'wp-format-library' );
        wp_enqueue_style( 'wp-components' );
    }
}
add_action( 'admin_enqueue_scripts', 'fix_gutenberg_dependencies', 5 );

/**
 * Gutenbergエディタでカテゴリーとタグを確実に表示
 */
function ensure_taxonomy_support_in_gutenberg() {
    // SEO記事投稿タイプにタクソノミーサポートを明示的に追加
    add_post_type_support( 'seo_articles', 'editor' );
    add_post_type_support( 'seo_articles', 'custom-fields' );
    
    // カテゴリーとタグがGutenbergサイドバーに表示されるようにする
    if ( function_exists( 'register_meta' ) ) {
        // カテゴリーメタデータの登録
        register_meta( 'post', 'category', array(
            'show_in_rest' => true,
            'single' => false,
            'type' => 'array',
            'auth_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ));
        
        // タグメタデータの登録
        register_meta( 'post', 'post_tag', array(
            'show_in_rest' => true,
            'single' => false,
            'type' => 'array',
            'auth_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ));
    }
}
add_action( 'init', 'ensure_taxonomy_support_in_gutenberg' );

/**
 * GutenbergのREST APIでタクソノミーを有効化
 */
function enable_taxonomy_rest_support() {
    global $wp_taxonomies;
    
    // カテゴリーとタグをREST APIで利用可能にする
    if ( isset( $wp_taxonomies['category'] ) ) {
        $wp_taxonomies['category']->show_in_rest = true;
        $wp_taxonomies['category']->rest_base = 'categories';
        $wp_taxonomies['category']->rest_controller_class = 'WP_REST_Terms_Controller';
    }
    
    if ( isset( $wp_taxonomies['post_tag'] ) ) {
        $wp_taxonomies['post_tag']->show_in_rest = true;
        $wp_taxonomies['post_tag']->rest_base = 'tags';
        $wp_taxonomies['post_tag']->rest_controller_class = 'WP_REST_Terms_Controller';
    }
}
add_action( 'init', 'enable_taxonomy_rest_support', 30 );

/**
 * Gutenbergエディタのサイドバーでタクソノミーパネルを強制表示
 */
function force_show_taxonomy_panels_in_gutenberg() {
    ?>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Gutenbergエディタが読み込まれた後に実行
        if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
            setTimeout(function() {
                try {
                    // タクソノミーパネルを開く
                    wp.data.dispatch('core/edit-post').openGeneralSidebar('edit-post/document');
                    
                    // カテゴリーパネルを有効にする
                    wp.data.dispatch('core/edit-post').enablePluginDocumentSettingPanel('category-panel-0');
                    wp.data.dispatch('core/edit-post').enablePluginDocumentSettingPanel('post_tag-panel-0');
                } catch(e) {
                    console.log('Taxonomy panels setup: ', e);
                }
            }, 1000);
        }
    });
    </script>
    <?php
}
add_action( 'admin_footer', 'force_show_taxonomy_panels_in_gutenberg' );

// パーマリンクをフラッシュ（管理画面メニュー表示のため）
function flush_rewrite_rules_for_seo_articles() {
    // メニュー表示を強制するため再度フラッシュ
    delete_option('flush_rewrite_rules_seo_articles');
    flush_rewrite_rules();
    update_option('flush_rewrite_rules_seo_articles', 'flushed');
}
add_action('init', 'flush_rewrite_rules_for_seo_articles');

/**
 * Add Favicon and Apple Touch Icon
 */
function add_custom_favicon() {
    echo '<link rel="shortcut icon" href="' . get_template_directory_uri() . '/favicon.ico" type="image/x-icon">';
    echo '<link rel="icon" href="' . get_template_directory_uri() . '/favicon-16x16.png" sizes="16x16" type="image/png">';
    echo '<link rel="icon" href="' . get_template_directory_uri() . '/favicon-32x32.png" sizes="32x32" type="image/png">';
    echo '<link rel="apple-touch-icon" href="' . get_template_directory_uri() . '/apple-touch-icon.png" sizes="180x180">';
    echo '<link rel="manifest" href="' . get_template_directory_uri() . '/site.webmanifest">';
}
add_action('wp_head', 'add_custom_favicon');

// ============================================
// AUTOMATIC ENGLISH SLUG GENERATION
// Google Translate APIを使用した自動英語スラッグ生成
// ============================================

/**
 * Google Translate APIを使用してテキストを翻訳
 */
function translate_text_to_english($text) {
    // Google Translate API キー（wp-config.phpに定義）
    $api_key = defined('GOOGLE_TRANSLATE_API_KEY') ? GOOGLE_TRANSLATE_API_KEY : '';
    
    if (empty($api_key)) {
        error_log('Google Translate API key is not defined in wp-config.php');
        return false;
    }
    
    // GET メソッドでURLパラメータとして送信（より確実な方法）
    $url = 'https://translation.googleapis.com/language/translate/v2?' . http_build_query(array(
        'key' => $api_key,
        'q' => $text,
        'source' => 'ja',
        'target' => 'en',
        'format' => 'text'
    ));
    
    $response = wp_remote_get($url, array(
        'timeout' => 15,
        'headers' => array(
            'Accept' => 'application/json',
        )
    ));
    
    if (is_wp_error($response)) {
        error_log('Google Translate API Error: ' . $response->get_error_message());
        return false;
    }
    
    $http_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    // デバッグ用ログ
    error_log("Google Translate API Response: HTTP {$http_code}");
    error_log("Google Translate API Body: " . $body);
    
    if ($http_code !== 200) {
        error_log("Google Translate API HTTP Error: {$http_code}");
        return false;
    }
    
    $result = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Google Translate API: JSON decode error - ' . json_last_error_msg());
        return false;
    }
    
    if (isset($result['data']['translations'][0]['translatedText'])) {
        return $result['data']['translations'][0]['translatedText'];
    }
    
    if (isset($result['error'])) {
        error_log('Google Translate API Error: ' . $result['error']['message']);
        return false;
    }
    
    error_log('Google Translate API: Unexpected response format - ' . print_r($result, true));
    return false;
}

/**
 * 翻訳されたテキストをスラッグに変換
 */
function create_slug_from_english($english_text) {
    // 小文字に変換
    $slug = strtolower($english_text);
    
    // 特殊文字を削除またはハイフンに変換
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    
    // スペースをハイフンに変換
    $slug = preg_replace('/\s+/', '-', trim($slug));
    
    // 連続するハイフンを単一のハイフンに
    $slug = preg_replace('/-+/', '-', $slug);
    
    // 前後のハイフンを削除
    $slug = trim($slug, '-');
    
    // 長すぎる場合は短縮（WordPressの推奨は200文字以下）
    if (strlen($slug) > 100) {
        $words = explode('-', $slug);
        $short_slug = '';
        foreach ($words as $word) {
            if (strlen($short_slug . '-' . $word) > 100) {
                break;
            }
            $short_slug = empty($short_slug) ? $word : $short_slug . '-' . $word;
        }
        $slug = $short_slug;
    }
    
    return $slug;
}

/**
 * SEO記事投稿時の自動スラッグ生成
 */
function auto_generate_english_slug($post_id, $post, $update) {
    // 自動保存、リビジョン、ゴミ箱は除外
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || $post->post_status === 'trash') {
        return;
    }
    
    // seo_articles投稿タイプのみ対象
    if ($post->post_type !== 'seo_articles') {
        return;
    }
    
    // 既にカスタムスラッグが設定されている場合はスキップ
    if (!empty($post->post_name) && $post->post_name !== sanitize_title($post->post_title)) {
        return;
    }
    
    // タイトルが空の場合はスキップ
    if (empty($post->post_title)) {
        return;
    }
    
    // 無限ループ防止
    remove_action('save_post', 'auto_generate_english_slug', 10, 3);
    
    // 英語翻訳を取得
    $english_title = translate_text_to_english($post->post_title);
    
    if ($english_title) {
        $english_slug = create_slug_from_english($english_title);
        
        if (!empty($english_slug)) {
            // スラッグの重複チェックと調整
            $unique_slug = wp_unique_post_slug($english_slug, $post_id, $post->post_status, $post->post_type, $post->post_parent);
            
            // 投稿を更新
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => $unique_slug
            ));
            
            // ログに記録
            error_log("Auto-generated English slug for post {$post_id}: '{$post->post_title}' -> '{$unique_slug}'");
        }
    }
    
    // フックを再度追加
    add_action('save_post', 'auto_generate_english_slug', 10, 3);
}
add_action('save_post', 'auto_generate_english_slug', 10, 3);

/**
 * 管理画面に英語スラッグ生成ボタンを追加
 */
function add_english_slug_meta_box() {
    add_meta_box(
        'english-slug-generator',
        '英語スラッグ生成',
        'english_slug_meta_box_callback',
        'seo_articles',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_english_slug_meta_box');

/**
 * 英語スラッグ生成メタボックスのコールバック
 */
function english_slug_meta_box_callback($post) {
    ?>
    <div id="english-slug-generator">
        <p>タイトルから英語スラッグを自動生成します。</p>
        <button type="button" id="generate-english-slug" class="button button-secondary">英語スラッグを生成</button>
        <div id="slug-preview" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa; display: none;">
            <strong>生成されたスラッグ:</strong>
            <div id="slug-text" style="font-family: monospace; margin-top: 5px;"></div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // タイトル取得の汎用関数
        function getCurrentPostTitle() {
            // 複数の方法でタイトルを取得を試行
            const selectors = [
                '#title',                              // クラシックエディタ
                '.editor-post-title__input',          // Gutenberg (古いバージョン)
                '[data-type="core/post-title"] textarea', // Gutenberg (新しいバージョン)
                '.wp-block-post-title',                // ブロックエディタ
                'h1[data-type="core/post-title"]',     // タイトルブロック
                '[placeholder*="タイトル"]',            // 日本語プレースホルダー
                '[placeholder*="Add title"]'           // 英語プレースホルダー
            ];
            
            // セレクタを順番に試す
            for (const selector of selectors) {
                const element = $(selector);
                if (element.length > 0) {
                    const value = element.val() || element.text() || element.attr('value');
                    if (value && value.trim()) {
                        console.log('Title found with selector:', selector, 'Value:', value);
                        return value.trim();
                    }
                }
            }
            
            // WordPress Data APIを使用（Gutenberg）
            if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
                try {
                    const postTitle = wp.data.select('core/editor').getEditedPostAttribute('title');
                    if (postTitle && postTitle.trim()) {
                        console.log('Title found via WP Data API:', postTitle);
                        return postTitle.trim();
                    }
                } catch (e) {
                    console.log('WordPress Data API not available:', e);
                }
            }
            
            // 最後の手段：ページタイトル要素から取得
            const pageTitle = $('h1').first().text();
            if (pageTitle && pageTitle.trim() && !pageTitle.includes('新規投稿') && !pageTitle.includes('Add New')) {
                console.log('Title found from page h1:', pageTitle);
                return pageTitle.trim();
            }
            
            return null;
        }

        $('#generate-english-slug').on('click', function() {
            const button = $(this);
            const title = getCurrentPostTitle();
            
            if (!title) {
                alert('タイトルを入力してください。\n\nタイトル入力後に再度お試しください。');
                
                // デバッグ情報をコンソールに出力
                console.log('Debug: Available elements:');
                console.log('- #title:', $('#title').val());
                console.log('- .editor-post-title__input:', $('.editor-post-title__input').val());
                console.log('- [data-type="core/post-title"] textarea:', $('[data-type="core/post-title"] textarea').val());
                console.log('- All h1 elements:', $('h1').map(function() { return $(this).text(); }).get());
                
                return;
            }
            
            button.prop('disabled', true).text('生成中...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'generate_english_slug_preview',
                    title: title,
                    nonce: '<?php echo wp_create_nonce('generate_english_slug'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#slug-text').text(response.data.slug);
                        $('#slug-preview').show();
                        $('#post_name').val(response.data.slug);
                        $('.edit-slug').show();
                        $('#editable-post-name').text(response.data.slug);
                    } else {
                        alert('スラッグの生成に失敗しました: ' + (response.data || '不明なエラー'));
                    }
                },
                error: function() {
                    alert('通信エラーが発生しました。');
                },
                complete: function() {
                    button.prop('disabled', false).text('英語スラッグを生成');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * AJAX: 英語スラッグプレビュー生成
 */
function ajax_generate_english_slug_preview() {
    check_ajax_referer('generate_english_slug', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_die('権限がありません。');
    }
    
    $title = sanitize_text_field($_POST['title']);
    
    if (empty($title)) {
        wp_send_json_error('タイトルが空です。');
    }
    
    // API キーの確認
    $api_key = defined('GOOGLE_TRANSLATE_API_KEY') ? GOOGLE_TRANSLATE_API_KEY : '';
    if (empty($api_key)) {
        wp_send_json_error('Google Translate APIキーが設定されていません。wp-config.phpを確認してください。');
    }
    
    // テスト用：APIキーの最初の4文字と最後の4文字を表示
    $masked_key = substr($api_key, 0, 4) . '...' . substr($api_key, -4);
    error_log("Using API Key: {$masked_key}");
    
    $english_title = translate_text_to_english($title);
    
    if (!$english_title) {
        wp_send_json_error('翻訳に失敗しました。WordPressのエラーログを確認してください。');
    }
    
    $slug = create_slug_from_english($english_title);
    
    if (empty($slug)) {
        wp_send_json_error('スラッグの生成に失敗しました。');
    }
    
    wp_send_json_success(array(
        'slug' => $slug,
        'english_title' => $english_title,
        'api_key_test' => $masked_key
    ));
}
add_action('wp_ajax_generate_english_slug_preview', 'ajax_generate_english_slug_preview');

/**
 * デバッグ用：Google Translate API テスト関数
 */
function test_google_translate_api() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="wrap"><h1>Google Translate API テスト</h1>';
    
    $api_key = defined('GOOGLE_TRANSLATE_API_KEY') ? GOOGLE_TRANSLATE_API_KEY : '';
    if (empty($api_key)) {
        echo '<div class="notice notice-error"><p>APIキーが設定されていません。</p></div>';
        echo '</div>';
        return;
    }
    
    echo '<p>APIキー: ' . substr($api_key, 0, 4) . '...' . substr($api_key, -4) . '</p>';
    
    $test_text = 'テスト';
    echo '<p>テスト文字列: ' . $test_text . '</p>';
    
    $result = translate_text_to_english($test_text);
    
    if ($result) {
        echo '<div class="notice notice-success"><p>翻訳成功: ' . $result . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>翻訳失敗。エラーログを確認してください。</p></div>';
    }
    
    echo '</div>';
}

// 管理画面でのテストページ追加
function add_translate_test_page() {
    if (current_user_can('manage_options') && isset($_GET['test_translate'])) {
        add_action('admin_init', function() {
            test_google_translate_api();
            exit;
        });
    }
}
add_action('init', 'add_translate_test_page');

// Temporary: Flush rewrite rules once to apply new studio-detail permalink structure
function flush_studio_permalinks_once() {
    if (get_option('studio_permalinks_flushed') != '1') {
        flush_rewrite_rules();
        update_option('studio_permalinks_flushed', '1');
    }
}
add_action('init', 'flush_studio_permalinks_once');


// Custom permalink and preview for studio_shops
function custom_studio_shop_permalink($permalink, $post) {
    if ($post->post_type == 'studio_shops') {
        $shop_id = get_post_meta($post->ID, 'shop_id', true);
        if (empty($shop_id)) {
            $shop_id = $post->ID; // Fallback to post ID
        }
        return home_url('/studio-detail/?shop_id=' . $shop_id);
    }
    return $permalink;
}
add_filter('post_link', 'custom_studio_shop_permalink', 10, 2);
add_filter('preview_post_link', 'custom_studio_shop_permalink', 10, 2);


// Custom admin permalink display for studio_shops
function custom_studio_shop_get_permalink($permalink, $post, $leavename) {
    if ($post->post_type == 'studio_shops') {
        $shop_id = get_post_meta($post->ID, 'shop_id', true);
        if (empty($shop_id)) {
            $shop_id = $post->ID; // Fallback to post ID
        }
        return home_url('/studio-detail/?shop_id=' . $shop_id);
    }
    return $permalink;
}
add_filter('get_permalink', 'custom_studio_shop_get_permalink', 10, 3);


// Override sample permalink in admin for studio_shops
function custom_studio_shop_sample_permalink($permalink, $post_id, $title, $name, $post) {
    if ($post && $post->post_type == 'studio_shops') {
        $shop_id = get_post_meta($post_id, 'shop_id', true);
        if (empty($shop_id)) {
            $shop_id = $post_id; // Fallback to post ID
        }
        $custom_permalink = home_url('/studio-detail/?shop_id=' . $shop_id);
        return array($custom_permalink, '');
    }
    return $permalink;
}
add_filter('get_sample_permalink', 'custom_studio_shop_sample_permalink', 10, 5);


// Admin script to modify permalink display for studio_shops
function studio_shop_admin_script() {
    global $post;
    if ($post && $post->post_type == 'studio_shops') {
        $shop_id = get_post_meta($post->ID, 'shop_id', true);
        if (empty($shop_id)) {
            $shop_id = $post->ID;
        }
        $correct_url = home_url('/studio-detail/?shop_id=' . $shop_id);
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var correctUrl = '<?php echo $correct_url; ?>';
            $('#sample-permalink').attr('href', correctUrl).text(correctUrl);
        });
        </script>
        <?php
    }
}
add_action('admin_footer-post.php', 'studio_shop_admin_script');
add_action('admin_footer-post-new.php', 'studio_shop_admin_script');

// Cookie Consent Script
function enqueue_cookie_consent_script() {
    wp_enqueue_script(
        'cookie-consent',
        get_template_directory_uri() . '/assets/js/cookie-consent.js',
        [],
        '1.0.0',
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'enqueue_cookie_consent_script');

// Header Height Manager Script
function enqueue_header_height_manager() {
    wp_enqueue_script(
        'header-height-manager',
        get_template_directory_uri() . '/assets/js/header-height-manager.js',
        [],
        '1.0.0',
        false // Load in head for early execution
    );
}
add_action('wp_enqueue_scripts', 'enqueue_header_height_manager', 5);

// AJAX handler for form submissions - DISABLED to allow plugin handler to work
// function siaes_submit_form_handler() {
//     // Log form submission attempt
//     siaes_debug_log('=== Theme AJAX handler triggered ===');
//     siaes_debug_log('POST data: ' . print_r($_POST, true));
//
//     // Verify nonce (temporarily more lenient for debugging)
//     if (!empty($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'siaes_form_nonce')) {
//         siaes_debug_log('Nonce verification failed');
//         wp_die(json_encode(['success' => false, 'message' => 'セキュリティチェックに失敗しました。']));
//     }
//
//     // Log form submission attempt
//     siaes_debug_log('Form submission received:', $_POST);
//
//     try {
//         // Get form data
//         $page_id = sanitize_text_field($_POST['page_id'] ?? '');
//         $form_data = [];
//
//         // Extract all form fields
//         foreach ($_POST as $key => $value) {
//             if (!in_array($key, ['action', 'nonce', 'page_id'])) {
//                 $form_data[$key] = sanitize_textarea_field($value);
//             }
//         }
//
//         siaes_debug_log('Processed form data:', $form_data);
//
//         // Here you would normally process the form data
//         // (save to database, send email, etc.)
//
//         // For now, just return success
//         wp_die(json_encode([
//             'success' => true,
//             'message' => 'フォームが正常に送信されました。ありがとうございます。'
//         ]));
//
//     } catch (Exception $e) {
//         siaes_debug_log('Form submission error:', $e->getMessage());
//         wp_die(json_encode([
//             'success' => false,
//             'message' => '送信に失敗しました。しばらく時間をおいて再度お試しください。'
//         ]));
//     }
// }
// // Remove plugin's form handler and add ours - DISABLED
// // remove_action('wp_ajax_siaes_submit_form', 'siaes_handle_form_submission');
// // remove_action('wp_ajax_nopriv_siaes_submit_form', 'siaes_handle_form_submission');
// // add_action('wp_ajax_siaes_submit_form', 'siaes_submit_form_handler', 20);
// // add_action('wp_ajax_nopriv_siaes_submit_form', 'siaes_submit_form_handler', 20);

