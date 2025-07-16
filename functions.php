<?php
// === FTP Constants ===
if (!defined('FTP_HOST')) define('FTP_HOST', 'sv504.xbiz.ne.jp');
if (!defined('FTP_USER')) define('FTP_USER', 'xb592942');
if (!defined('FTP_PASS')) define('FTP_PASS', 'rv9e09e2');
if (!defined('XSERVER_GALLERY_BASE')) define('XSERVER_GALLERY_BASE', '/sugamo-navi.com/public_html/gallery/');
if (!defined('XSERVER_GALLERY_URL')) define('XSERVER_GALLERY_URL', 'https://sugamo-navi.com/gallery/');

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
    $message = '';
    if (isset($_POST['ftp_gallery_submit']) && check_admin_referer('ftp_gallery_nonce')) {
        $category = sanitize_text_field($_POST['gallery_category']);
        $new_category = sanitize_text_field($_POST['new_category']);
        $category = !empty($new_category) ? $new_category : $category;
        if (empty($category)) {
            echo '<div class="error"><p>দয়া করে category নাম লিখুন অথবা একটি সিলেক্ট করুন।</p></div>';
            return;
        }

        $category = preg_replace('/[^A-Za-z0-9-_]/', '', str_replace(' ', '-', strtolower($category)));

        $ftp = ftp_connect(FTP_HOST);
        if (!$ftp || !ftp_login($ftp, FTP_USER, FTP_PASS)) {
            echo '<div class="error"><p>FTP connection/login failed.</p></div>';
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
                    echo '<div class="error"><p>Folder create failed: ' . esc_html($path) . '</p></div>';
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
                        echo '<div class="error"><p>Upload failed: ' . esc_html($name) . '</p></div>';
                    }
                }
            }
            echo '<div class="updated"><p>' . $success . ' টি ছবি সফলভাবে আপলোড হয়েছে!</p></div>';
        } else {
            echo '<div class="error"><p>কোনো ছবি নির্বাচন করা হয় নি।</p></div>';
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
        <h1>FTP Gallery Upload</h1>
        <?php echo $message; ?>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('ftp_gallery_nonce'); ?>
            <p>
                <label>Category সিলেক্ট করুন (আগে তৈরি):</label><br>
                <select name="gallery_category" style="width:300px;">
                    <option value="">নতুন বা পুরোনো সিলেক্ট করুন</option>
                    <?php foreach ($existing_categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label>নতুন Category নাম লিখুন (অপশনাল):</label><br>
                <input type="text" name="new_category" placeholder="উদাহরণ: summer-2025" style="width:300px;">
            </p>
            <p>
                <label>ছবি নির্বাচন করুন:</label><br>
                <input type="file" name="gallery_images[]" multiple accept="image/*" required>
            </p>
            <p>
                <input type="submit" name="ftp_gallery_submit" class="button button-primary" value="Upload Images">
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
    $ftp = ftp_connect(FTP_HOST);
    if (!$ftp || !ftp_login($ftp, FTP_USER, FTP_PASS)) {
        return '<div style="color:red; font-size:20px; background:yellow; padding:15px; border:2px solid red;">FTP connection failed. Check internet or FTP details in functions.php. Time: ' . date('Y-m-d H:i:s') . '</div>';
    }
    ftp_pasv($ftp, true);

    $categories = ftp_nlist($ftp, XSERVER_GALLERY_BASE);
    if (!$categories) {
        ftp_close($ftp);
        return '<div style="color:red; font-size:20px; background:yellow; padding:15px; border:2px solid red;">No categories found in ' . XSERVER_GALLERY_BASE . '. Time: ' . date('Y-m-d H:i:s') . '</div>';
    }

    $output = '<div class="xserver-gallery-wrapper">';
    $output .= '<h2>Gallery Categories</h2><ul style="list-style:none;">';
    $output .= '<p style="color:blue; font-size:16px;">Found ' . count($categories) . ' categories</p>';

    foreach ($categories as $category_path) {
        $category = basename($category_path);
        if (in_array($category, ['.', '..'])) continue;

        $output .= '<li style="margin: 10px 0;"><h3>' . esc_html($category) . '</h3><div style="display:flex; flex-wrap:wrap; gap:10px;">';

        $files = ftp_nlist($ftp, $category_path);
        if ($files && is_array($files)) {
            $output .= '<p style="color:blue; font-size:16px;">Found ' . count($files) . ' files in ' . esc_html($category) . '</p>';
            foreach ($files as $file) {
                if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                    $image_name = basename($file);
                    $img_url = XSERVER_GALLERY_URL . $category . '/' . $image_name;
                    $output .= '<img src="' . esc_url($img_url) . '" alt="' . esc_attr($image_name) . '" style="max-width:200px; height:auto; border:1px solid #ccc; padding:5px;">';
                } else {
                    $output .= '<p style="color:orange; font-size:14px;">Skipping non-image: ' . esc_html(basename($file)) . '</p>';
                }
            }
        } else {
            $output .= '<div style="color:red; font-size:20px; background:yellow; padding:15px; border:2px solid red;">No files found in ' . esc_html($category) . '. Time: ' . date('Y-m-d H:i:s') . '</div>';
        }

        $output .= '</div></li>';
    }

    ftp_close($ftp);
    $output .= '</ul></div>';

    return $output;
});