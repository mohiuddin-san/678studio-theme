<?php
// === FTP Constants ===
if (!defined('FTP_HOST')) define('FTP_HOST', 'sv504.xbiz.ne.jp');
if (!defined('FTP_USER')) define('FTP_USER', 'xb592942');
if (!defined('FTP_PASS')) define('FTP_PASS', 'rv9e09e2');
if (!defined('XSERVER_GALLERY_BASE')) define('XSERVER_GALLERY_BASE', '/sugamo-navi.com/public_html/gallery/');
if (!defined('XSERVER_GALLERY_URL')) define('XSERVER_GALLERY_URL', 'https://sugamo-navi.com/gallery/');

// === Language Translations ===
function get_translations($lang = 'en') {
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
    return $translations[$lang] ?? $translations['en']; // Default to English if language not found
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
    // Get or save selected language
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

        $ftp = ftp_connect(FTP_HOST);
        if (!$ftp || !ftp_login($ftp, FTP_USER, FTP_PASS)) {
            echo '<div class="error"><p>' . esc_html($translations['ftp_error']) . '</p></div>';
            return;
        }
        ftp_pasv($ftp, true);

        $remote_path = XSERVER_GALLERY_BASE . $category;
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

    // Fetch existing categories from Xserver
    $ftp = ftp_connect(FTP_HOST);
    $existing_categories = [];
    if ($ftp && ftp_login($ftp, FTP_USER, FTP_PASS)) {
        ftp_pasv($ftp, true);
        $items = ftp_nlist($ftp, XSERVER_GALLERY_BASE);
        foreach ($items as $item) {
            $name = basename($item);
            if ($name !== '.' && $name !== '..') {
                $existing_categories[] = $name;
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

// === Helper: Fetch Category List from Xserver FTP ===
function get_xserver_gallery_categories() {
    $ftp = ftp_connect(FTP_HOST);
    if (!$ftp || !ftp_login($ftp, FTP_USER, FTP_PASS)) return [];
    ftp_pasv($ftp, true);
    $items = ftp_nlist($ftp, XSERVER_GALLERY_BASE);
    ftp_close($ftp);

    $categories = [];
    foreach ($items as $item) {
        $name = basename($item);
        if ($name !== '.' && $name !== '..') {
            $categories[] = $name;
        }
    }
    return $categories;
}

// === Helper: Guess images inside a category (image1.jpg - image10.jpg) ===
function guess_images_in_category($category) {
    $base = XSERVER_GALLERY_URL . $category . '/';
    $images = [];
    for ($i = 1; $i <= 10; $i++) {
        $name = "image{$i}.jpg";
        $images[] = $base . $name;
    }
    return $images;
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