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

// AJAX handler for studio search
function ajax_studio_search() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'studio_search_nonce')) {
        wp_die('Security check failed');
    }

    $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $per_page = 6;

    // Use the same function from studio-search.php
    $api_url = 'https://678photo.com/api/get_all_studio_shop.php';

    $response = wp_remote_get($api_url, [
        'timeout' => 15, 
        'sslverify' => false 
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'Invalid JSON response']);
        return;
    }
    
    if (!isset($data['shops']) || !is_array($data['shops'])) {
        wp_send_json_error(['message' => 'No shops found in API response']);
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
                    <img src="<?php echo !empty($shop['image_urls']) ? esc_url($shop['image_urls'][0]) : get_template_directory_uri() . '/assets/images/cardpic-sample.jpg'; ?>" alt="スタジオ写真">
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
        <a href="#" class="pagination-btn pagination-btn--prev" data-page="<?php echo max(1, $page - 1); ?>" <?php echo $page == 1 ? 'data-disabled="true"' : ''; ?>>
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <div class="pagination-numbers">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="#" class="<?php echo $i == $page ? 'active' : ''; ?>" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <a href="#" class="pagination-btn pagination-btn--next" data-page="<?php echo min($total_pages, $page + 1); ?>" <?php echo $page == $total_pages ? 'data-disabled="true"' : ''; ?>>
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
add_action('wp_ajax_studio_search', 'ajax_studio_search');
add_action('wp_ajax_nopriv_studio_search', 'ajax_studio_search');

// Enqueue styles and scripts
function theme_678studio_styles() {
    // Use filemtime for cache busting in development
    $version = WP_DEBUG ? filemtime(get_stylesheet_directory() . '/style.css') : '1.0.0';
    
    wp_enqueue_style('678studio-style', get_stylesheet_uri(), [], $version);
    
    // Enqueue header script for mobile menu (only on frontend)
    if (!is_admin()) {
        $header_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/header.js') : '1.0.0';
        wp_enqueue_script('678studio-header', 
            get_template_directory_uri() . '/assets/js/header.js', 
            [], $header_version, true);
    }
    
    // Enqueue gallery script on gallery pages
    if (is_page_template('page-gallery.php')) {
        $js_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/gallery.js') : '1.0.0';
        wp_enqueue_script('678studio-gallery', 
            get_template_directory_uri() . '/assets/js/gallery.js', 
            [], $js_version, true);
    }
    
    // Enqueue GSAP and media slider on front page
    if (is_front_page() || is_home()) {
        // GSAP Core
        wp_enqueue_script('gsap', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', 
            [], '3.12.2', true);
        
        // GSAP Draggable Plugin
        wp_enqueue_script('gsap-draggable', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/Draggable.min.js', 
            ['gsap'], '3.12.2', true);
        
        // Media Slider Script
        $slider_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/modules/media-slider.js') : '1.0.0';
        wp_enqueue_script('media-slider', 
            get_template_directory_uri() . '/assets/js/modules/media-slider.js', 
            ['gsap', 'gsap-draggable'], $slider_version, true);
    }
    
    // Enqueue GSAP and FAQ accordion on About page
    if (is_page('about') || is_page_template('page-about.php')) {
        // GSAP Core
        wp_enqueue_script('gsap', 
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', 
            [], '3.12.2', true);
        
        // FAQ Accordion Script
        $faq_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/components/faq-accordion.js') : '1.0.0';
        wp_enqueue_script('faq-accordion', 
            get_template_directory_uri() . '/assets/js/components/faq-accordion.js', 
            ['gsap'], $faq_version, true);
    }
    
    // Enqueue GSAP and gallery slider on store detail pages
    if (is_page_template('page-store-detail.php') || is_page('store-detail-test')) {
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

// Enqueue debug scripts
function theme_678studio_debug_scripts() {
    if (WP_DEBUG || (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG)) {
        wp_enqueue_script('wp-debug-logger', 
            get_template_directory_uri() . '/assets/js/debug-logger.js', 
            ['jquery'], '1.0.0', true);
        
        wp_localize_script('wp-debug-logger', 'wpDebugAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_debug_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'theme_678studio_debug_scripts');

// Theme support
add_theme_support('title-tag');
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

// Track WordPress errors
add_action('wp_die_handler', function($message, $title, $args) {
    wp_log_error('WordPress die called', [
        'message' => $message,
        'title' => $title,
        'args' => $args
    ]);
});

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

// === Add Gallery Admin Menu ===
add_action('admin_menu', function() {
    add_menu_page(
        'FTP Gallery Upload',
        'Gallery Upload',
        'manage_options',
        'ftp-gallery-upload',
        'ftp_gallery_upload_page',
        'dashicons-upload',
        20
    );
});

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
                <input type="text" name="new_category" placeholder="<?php echo esc_attr($translations['placeholder_category']); ?>" style="width:300px;">
            </p>
            <p>
                <label><?php echo esc_html($translations['select_images']); ?></label><br>
                <input type="file" name="gallery_images[]" multiple accept="image/*" required>
            </p>
            <p>
                <input type="submit" name="ftp_gallery_submit" class="button button-primary" value="<?php echo esc_attr($translations['upload_button']); ?>">
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


function enqueue_reservation_script() {
    // Check if the current page slug is 'studio-reservation'
    if (is_page('studio-reservation')) {
        // 店舗選択用のスクリプト
        wp_enqueue_script(
            'reservation-script',
            get_template_directory_uri() . '/assets/js/inquery.js',
            array(), // Add dependencies if needed
            WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/inquery.js') : '1.0.0',
            true // Load in footer
        );
        
        // フォーム確認画面用のスクリプト
        wp_enqueue_script(
            'reservation-form-script',
            get_template_directory_uri() . '/assets/js/reservation-form.js',
            array(), // Add dependencies if needed
            WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/reservation-form.js') : '1.0.0',
            true // Load in footer
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_reservation_script');
function enqueue_inquery_script() {
    // Check if the current page slug is 'studio-inquery'
    if (is_page('studio-inquery')) {
        // 店舗選択用のスクリプト
        wp_enqueue_script(
            'inquery-script',
            get_template_directory_uri() . '/assets/js/inquery.js',
            array(), // Add dependencies if needed
            WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/inquery.js') : '1.0.0',
            true // Load in footer
        );
        
        // フォーム確認画面用のスクリプト
        wp_enqueue_script(
            'inquiry-form-script',
            get_template_directory_uri() . '/assets/js/inquiry-form.js',
            array(), // Add dependencies if needed
            WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/inquiry-form.js') : '1.0.0',
            true // Load in footer
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_inquery_script');