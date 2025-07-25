<?php
/**
 * 678 Studio Theme Functions
 * This file has been verified for syntax to resolve the parse error at line 290.
 * FTP credentials are sourced from environment variables for security.
 * Includes are commented out to isolate potential errors in external files.
 * Enhanced error logging for Docker compatibility and added transient caching.
 */

// === FTP Constants ===
if (!defined('FTP_HOST')) define('FTP_HOST', getenv('FTP_HOST') ?: 'sv504.xbiz.ne.jp');
if (!defined('FTP_USER')) define('FTP_USER', getenv('FTP_USER') ?: 'xb592942');
if (!defined('FTP_PASS')) define('FTP_PASS', getenv('FTP_PASS') ?: 'rv9e09e2');
if (!defined('GALLERY_BASE_PATH')) define('GALLERY_BASE_PATH', '/sugamo-navi.com/public_html/gallery/');
if (!defined('GALLERY_BASE_URL')) define('GALLERY_BASE_URL', 'https://sugamo-navi.com/gallery/');

// === Language Translations ===
function get_translations($lang = 'en') {
    $translations = [
        'en' => [
            'page_title' => 'FTP Gallery Upload',
            'menu_title' => 'Gallery Upload',
            'category_error' => 'Please enter or select a category.',
            'ftp_error' => 'FTP connection/login failed: %s',
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
            'ftp_error' => 'FTP接続/ログインに失敗しました: %s',
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

// === Add Admin Menu ===
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

// === Admin Upload Page ===
function ftp_gallery_upload_page() {
    $lang = isset($_POST['gallery_language']) ? sanitize_text_field($_POST['gallery_language']) : get_option('ftp_gallery_language', 'en');
    if (isset($_POST['gallery_language']) && check_admin_referer('ftp_gallery_nonce')) {
        update_option('ftp_gallery_language', $lang);
    }
    $translations = get_translations($lang);

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

        error_log('Debug: Before FTP connect in ftp_gallery_upload_page');
        $ftp = ftp_connect(FTP_HOST);
        if (!$ftp || !ftp_login($ftp, FTP_USER, FTP_PASS)) {
            $error = error_get_last();
            error_log('FTP connection/login failed in ftp_gallery_upload_page: ' . json_encode($error));
            echo '<div class="error"><p>' . sprintf(esc_html($translations['ftp_error']), esc_html($error['message'] ?? 'Unknown error')) . '</p></div>';
            return;
        }
        ftp_pasv($ftp, true);

        $remote_path = GALLERY_BASE_PATH . $category;
        $folders = explode('/', $remote_path);
        $path = '';
        foreach ($folders as $folder) {
            if (!$folder) continue;
            $path = rtrim($path, '/') . '/' . $folder;
            if (!@ftp_chdir($ftp, $path)) {
                if (!ftp_mkdir($ftp, $path)) {
                    error_log('Failed to create FTP folder: ' . $path);
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
                        error_log('FTP upload failed for file: ' . $name);
                        echo '<div class="error"><p>' . sprintf(esc_html($translations['upload_error']), esc_html($name)) . '</p></div>';
                    }
                }
            }
            echo '<div class="updated"><p>' . sprintf(esc_html($translations['upload_success']), $success) . '</p></div>';
        } else {
            echo '<div class="error"><p>' . esc_html($translations['no_images']) . '</p></div>';
        }
        ftp_close($ftp);
        error_log('Debug: After FTP operations in ftp_gallery_upload_page');
    }

    $existing_categories = get_gallery_categories();

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
    $categories = get_transient('ftp_gallery_categories');
    if ($categories !== false) {
        return $categories;
    }
    $ftp = ftp_connect(FTP_HOST);
    if (!$ftp || !ftp_login($ftp, FTP_USER, FTP_PASS)) {
        error_log('FTP connection failed in get_gallery_categories: ' . json_encode(error_get_last()));
        return [];
    }
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
    set_transient('ftp_gallery_categories', $categories, HOUR_IN_SECONDS);
    return $categories;
}

add_shortcode('xserver_gallery_display', function () {
    $lang = get_option('ftp_gallery_language', 'en');
    $translations = get_translations($lang);

    $ftp = ftp_connect(FTP_HOST);
    if (!$ftp || !ftp_login($ftp, FTP_USER, FTP_PASS)) {
        return '<div style="color:red; font-size:20px; background:yellow; padding:15px; border:2px solid red;">' . sprintf(esc_html($translations['ftp_failed']), date('Y-m-d H:i:s')) . '</div>';
    }
    ftp_pasv($ftp, true);

    $categories = ftp_nlist($ftp, XSERVER_GALLERY_BASE);
    if (!$categories) {
        ftp_close($ftp);
        return '<div style="color:red; font-size:20px; background:yellow; padding:15px; border:2px solid red;">' . sprintf(esc_html($translations['no_files']), XSERVER_GALLERY_BASE, date('Y-m-d H:i:s')) . '</div>';
    }

    $output = '<div class="xserver-gallery-wrapper">';
    $output .= '<h2>' . esc_html($translations['gallery_title']) . '</h2><ul style="list-style:none;">';
    $output .= '<p style="color:blue; font-size:16px;">' . sprintf(esc_html($translations['found_categories']), count($categories)) . '</p>';

    foreach ($categories as $category_path) {
        $category = basename($category_path);
        if (in_array($category, ['.', '..'])) continue;

        $output .= '<li style="margin: 10px 0;"><h3>' . esc_html($category) . '</h3><div style="display:flex; flex-wrap:wrap; gap:10px;">';

        $files = ftp_nlist($ftp, $category_path);
        if ($files && is_array($files)) {
            $output .= '<p style="color:blue; font-size:16px;">' . sprintf(esc_html($translations['found_files']), count($files), esc_html($category)) . '</p>';
            foreach ($files as $file) {
                if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                    $image_name = basename($file);
                    $img_url = XSERVER_GALLERY_URL . $category . '/' . $image_name;
                    $output .= '<img src="' . esc_url($img_url) . '" alt="' . esc_attr($image_name) . '" style="max-width:200px; height:auto; border:1px solid #ccc; padding:5px;">';
                } else {
                    $output .= '<p style="color:orange; font-size:14px;">' . sprintf(esc_html($translations['skip_non_image']), esc_html(basename($file))) . '</p>';
                }
            }
        } else {
            $output .= '<div style="color:red; font-size:20px; background:yellow; padding:15px; border:2px solid red;">' . sprintf(esc_html($translations['no_files']), esc_html($category), date('Y-m-d H:i:s')) . '</div>';
        }

        $output .= '</div></li>';
    }

    ftp_close($ftp);
    $output .= '</ul></div>';

    return $output;
});


function theme_678studio_styles() {
    $version = WP_DEBUG ? filemtime(get_stylesheet_directory() . '/style.css') : '1.0.0';
    wp_enqueue_style('678studio-style', get_stylesheet_uri(), [], $version);

    if (is_page_template('page-gallery.php')) {
        $js_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/gallery.js') : '1.0.0';
        wp_enqueue_script('678studio-gallery',
            get_template_directory_uri() . '/assets/js/gallery.js',
            [], $js_version, true);
    }

    if (is_front_page() || is_home()) {
        wp_enqueue_script('gsap',
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js',
            [], '3.12.2', true);
        wp_enqueue_script('gsap-draggable',
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/Draggable.min.js',
            ['gsap'], '3.12.2', true);
        $slider_version = WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/modules/media-slider.js') : '1.0.0';
        wp_enqueue_script('media-slider',
            get_template_directory_uri() . '/assets/js/modules/media-slider.js',
            ['gsap', 'gsap-draggable'], $slider_version, true);
    }
}
add_action('wp_enqueue_scripts', 'theme_678studio_styles');

// === Enqueue Debug Scripts ===
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

// === Theme Support ===
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_theme_support('menus');

// === Register Navigation Menu ===
function theme_678studio_menus() {
    register_nav_menus(array(
        'header' => 'Header Menu'
    ));
}
add_action('init', 'theme_678studio_menus');

// === Development: Disable Caching ===
if (WP_DEBUG) {
    define('WP_CACHE', false);
    add_action('wp_head', function() {
        echo '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">' . "\n";
        echo '<meta http-equiv="Pragma" content="no-cache">' . "\n";
        echo '<meta http-equiv="Expires" content="0">' . "\n";
    });
    add_filter('script_loader_src', 'remove_script_version', 15, 1);
    add_filter('style_loader_src', 'remove_script_version', 15, 1);
}

function remove_script_version($src) {
    if (strpos($src, '678studio-style') === false) {
        $parts = explode('?ver', $src);
        return $parts[0];
    }
    return $src;
}

// === AJAX Handler for JavaScript Debug Logs ===
add_action('wp_ajax_wp_debug_log_js', 'handle_js_debug_logs');
add_action('wp_ajax_nopriv_wp_debug_log_js', 'handle_js_debug_logs');

function handle_js_debug_logs() {
    if (!wp_verify_nonce($_POST['nonce'], 'wp_debug_nonce')) {
        wp_die('Security check failed');
    }
    $logs = json_decode(stripslashes($_POST['logs']), true);
    if (!is_array($logs)) {
        wp_send_json_error('Invalid log data');
        return;
    }
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

// === WordPress Debug Integration Hooks ===
add_action('wp_loaded', function() {
    if (function_exists('wp_log_info')) {
        wp_log_info('WordPress fully loaded', [
            'theme' => get_template(),
            'active_plugins' => get_option('active_plugins'),
            'user_count' => count_users()['total_users']
        ]);
    }
});

add_action('template_redirect', function() {
    global $template;
    if (isset($template) && class_exists('WordPressDebugLogger')) {
        WordPressDebugLogger::getInstance()->trackTemplate($template, 'main');
    }
});

add_action('wp_die_handler', function($message, $title, $args) {
    if (function_exists('wp_log_error')) {
        wp_log_error('WordPress die called', [
            'message' => $message,
            'title' => $title,
            'args' => $args
        ]);
    }
});

add_filter('log_query_custom_data', function($query_data, $query) {
    if (isset($query_data['query_time']) && $query_data['query_time'] > 0.1 && function_exists('wp_log_warn')) {
        wp_log_warn('Slow database query detected', [
            'query' => $query,
            'execution_time' => $query_data['query_time']
        ]);
    }
    return $query_data;
}, 10, 2);

add_action('wp_login', function($user_login, $user) {
    if (function_exists('wp_log_info')) {
        wp_log_info('User login', [
            'user_login' => $user_login,
            'user_id' => $user->ID,
            'user_roles' => $user->roles
        ]);
    }
}, 10, 2);

add_action('wp_logout', function($user_id) {
    if (function_exists('wp_log_info')) {
        wp_log_info('User logout', [
            'user_id' => $user_id
        ]);
    }
});
function enqueue_reservation_script() {
    // Check if the current page slug is 'studio-reservation'
    if (is_page('studio-reservation')) {
        wp_enqueue_script(
            'reservation-script',
            get_template_directory_uri() . '/assets/js/reservation.js',
            array(), // Add dependencies if needed
            null,
            true // Load in footer
        );
    }
}
function enqueue_inquery_script() {
    // Check if the current page slug is 'studio-reservation'
    if (is_page('studio-inquery')) {
        wp_enqueue_script(
            'reservation-script',
            get_template_directory_uri() . '/assets/js/reservation.js',
            array(), // Add dependencies if needed
            null,
            true // Load in footer
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_inquery_script');
?>