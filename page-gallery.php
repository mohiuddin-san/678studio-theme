<?php
get_header(); ?>

<div class="gallery-container">
    <?php
    if (!defined('FTP_HOST')) define('FTP_HOST', 'sv504.xbiz.ne.jp');
    if (!defined('FTP_USER')) define('FTP_USER', 'xb592942');
    if (!defined('FTP_PASS')) define('FTP_PASS', 'rv9e09e2');
    if (!defined('XSERVER_GALLERY_BASE')) define('XSERVER_GALLERY_BASE', '/sugamo-navi.com/public_html/gallery/');
    if (!defined('XSERVER_GALLERY_URL')) define('XSERVER_GALLERY_URL', 'https://sugamo-navi.com/gallery/');

    $ftp = ftp_connect(FTP_HOST);
    $images_by_category = [];
    if ($ftp && ftp_login($ftp, FTP_USER, FTP_PASS)) {
        ftp_pasv($ftp, true);

        $categories = ftp_nlist($ftp, XSERVER_GALLERY_BASE);
        if ($categories) {
            foreach ($categories as $category_path) {
                $category = basename($category_path);
                if (in_array($category, ['.', '..'])) continue;

                $files = ftp_nlist($ftp, $category_path);
                if ($files && is_array($files)) {
                    $images = [];
                    foreach ($files as $file) {
                        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                            $image_name = basename($file);
                            $img_url = XSERVER_GALLERY_URL . $category . '/' . $image_name;
                            $images[] = ['url' => $img_url, 'name' => $image_name];
                        }
                    }
                    if (!empty($images)) {
                        $images_by_category[$category] = $images;
                    }
                }
            }
        }
        ftp_close($ftp);
    } else {
        echo '<div style="color:red; font-size:20px; background:yellow; padding:15px; border:2px solid red;">FTP connection failed. Check internet or FTP details. Time: ' . date('Y-m-d H:i:s') . '</div>';
    }
    ?>

    <?php if (empty($images_by_category)): ?>
        <div style="color:red; font-size:20px; background:yellow; padding:15px; border:2px solid red;">No categories or images found. Time: ' . date('Y-m-d H:i:s') . '</div>';
    <?php else: ?>
        <div class="gallery-controls">
            <button class="category-btn active" data-category="all">All</button>
            <?php foreach (array_keys($images_by_category) as $category): ?>
                <button class="category-btn" data-category="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></button>
            <?php endforeach; ?>
        </div>

        <div class="gallery-images">
            <?php foreach ($images_by_category as $category => $images): ?>
                <div class="image-category" data-category="<?php echo esc_attr($category); ?>">
                    <h3><?php echo esc_html($category); ?></h3>
                    <div class="image-grid">
                        <?php foreach ($images as $image): ?>
                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['name']); ?>" style="max-width:200px; height:auto; border:1px solid #ccc; padding:5px;">
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>

<style>
    .gallery-controls {
        margin: 20px 0;
    }
    .category-btn {
        padding: 10px 20px;
        margin-right: 10px;
        cursor: pointer;
        background: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .category-btn.active {
        background: #0073aa;
        color: white;
    }
    .image-category {
        display: none;
    }
    .image-category[data-category="all"] {
        display: block;
    }
    .gallery-images .image-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.category-btn');
    const categories = document.querySelectorAll('.image-category');

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            // Hide all categories
            categories.forEach(cat => cat.style.display = 'none');

            // Show the selected category or all
            const selectedCategory = this.getAttribute('data-category');
            if (selectedCategory === 'all') {
                categories.forEach(cat => cat.style.display = 'block');
            } else {
                document.querySelector(`.image-category[data-category="${selectedCategory}"]`).style.display = 'block';
            }
        });
    });

    // Trigger "All" button click on load
    document.querySelector('.category-btn[data-category="all"]').click();
});
</script>